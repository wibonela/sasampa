<?php

namespace App\Services;

use App\Models\Company;
use App\Models\Transaction;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class EfdmsService
{
    protected const SANDBOX_URL = 'https://virtual.tra.go.tz/efdmsRctApi';
    protected const PRODUCTION_URL = 'https://efd.tra.go.tz/api';

    protected function getBaseUrl(Company $company): string
    {
        if ($this->isStubMode()) {
            return 'stub';
        }

        return $company->isEfdProduction() ? self::PRODUCTION_URL : self::SANDBOX_URL;
    }

    protected function isStubMode(): bool
    {
        return config('efdms.environment', 'stub') === 'stub';
    }

    protected function getHttpOptions(): array
    {
        $options = [
            'timeout' => 30,
            'verify' => true,
        ];

        $certPath = config('efdms.cert_path');
        $certPassword = config('efdms.cert_password');

        if ($certPath && file_exists($certPath)) {
            $options['cert'] = $certPassword ? [$certPath, $certPassword] : $certPath;
        }

        return $options;
    }

    public function registerDevice(Company $company): array
    {
        if ($this->isStubMode()) {
            $uin = 'STUB-UIN-' . strtoupper(Str::random(8));
            return [
                'success' => true,
                'uin' => $uin,
                'message' => 'Device registered in stub mode',
            ];
        }

        try {
            $xml = EfdmsXmlBuilder::buildRegistrationXml($company);
            $baseUrl = $this->getBaseUrl($company);

            $response = Http::withOptions($this->getHttpOptions())
                ->withBody($xml, 'application/xml')
                ->post("{$baseUrl}/api/vfdRegReq");

            if ($response->successful()) {
                $responseXml = simplexml_load_string($response->body());
                $ackCode = (string) ($responseXml->ACKCODE ?? '');
                $uin = (string) ($responseXml->UIN ?? '');

                if ($ackCode === '0' && $uin) {
                    return [
                        'success' => true,
                        'uin' => $uin,
                        'message' => 'Device registered successfully',
                    ];
                }

                return [
                    'success' => false,
                    'message' => 'Registration rejected: ' . ($responseXml->ACKMSG ?? 'Unknown error'),
                ];
            }

            return [
                'success' => false,
                'message' => 'TRA API error: HTTP ' . $response->status(),
            ];
        } catch (\Exception $e) {
            Log::error('EFDMS registration failed', ['error' => $e->getMessage(), 'company_id' => $company->id]);
            return [
                'success' => false,
                'message' => 'Connection failed: ' . $e->getMessage(),
            ];
        }
    }

    public function signReceipt(Transaction $transaction): array
    {
        $company = $transaction->user?->company;
        if (!$company || !$company->isEfdEnabled()) {
            return ['success' => false, 'message' => 'EFD not enabled for this company'];
        }

        if ($this->isStubMode()) {
            $receiptNumber = 'STUB-' . strtoupper(Str::random(10));
            $verificationCode = strtoupper(Str::random(16));
            $qrCode = "https://verify.tra.go.tz/{$receiptNumber}";

            return [
                'success' => true,
                'fiscal_receipt_number' => $receiptNumber,
                'fiscal_verification_code' => $verificationCode,
                'fiscal_qr_code' => $qrCode,
                'fiscal_receipt_time' => now()->toIso8601String(),
                'message' => 'Receipt signed in stub mode',
            ];
        }

        try {
            $xml = EfdmsXmlBuilder::buildReceiptXml($transaction);
            $baseUrl = $this->getBaseUrl($company);

            $response = Http::withOptions($this->getHttpOptions())
                ->withBody($xml, 'application/xml')
                ->post("{$baseUrl}/api/efdmsRctInfo");

            if ($response->successful()) {
                $responseXml = simplexml_load_string($response->body());
                $ackCode = (string) ($responseXml->ACKCODE ?? '');

                if ($ackCode === '0') {
                    return [
                        'success' => true,
                        'fiscal_receipt_number' => (string) ($responseXml->RCTNUM ?? ''),
                        'fiscal_verification_code' => (string) ($responseXml->VFDCD ?? ''),
                        'fiscal_qr_code' => (string) ($responseXml->QRCODE ?? ''),
                        'fiscal_receipt_time' => (string) ($responseXml->DATE ?? now()->toIso8601String()),
                        'message' => 'Receipt signed successfully',
                    ];
                }

                return [
                    'success' => false,
                    'message' => 'Receipt rejected: ' . ($responseXml->ACKMSG ?? 'Unknown error'),
                ];
            }

            return [
                'success' => false,
                'message' => 'TRA API error: HTTP ' . $response->status(),
            ];
        } catch (\Exception $e) {
            Log::error('EFDMS receipt signing failed', [
                'error' => $e->getMessage(),
                'transaction_id' => $transaction->id,
            ]);
            return [
                'success' => false,
                'message' => 'Connection failed: ' . $e->getMessage(),
            ];
        }
    }

    public function submitZReport(Company $company, string $date = null): array
    {
        $date = $date ?? now()->format('Y-m-d');

        $transactions = Transaction::where('company_id', $company->id)
            ->whereDate('created_at', $date)
            ->completed()
            ->get();

        $dailySummary = [
            'date' => $date,
            'time' => now()->format('H:i:s'),
            'total_amount' => $transactions->sum('total'),
            'gross' => $transactions->sum('subtotal'),
            'discounts' => $transactions->sum('discount_amount'),
            'corrections' => 0,
            'receipt_count' => $transactions->count(),
        ];

        if ($this->isStubMode()) {
            return [
                'success' => true,
                'message' => 'Z-Report submitted in stub mode',
                'summary' => $dailySummary,
            ];
        }

        try {
            $xml = EfdmsXmlBuilder::buildZReportXml($company, $dailySummary);
            $baseUrl = $this->getBaseUrl($company);

            $response = Http::withOptions($this->getHttpOptions())
                ->withBody($xml, 'application/xml')
                ->post("{$baseUrl}/api/efdmsZReport");

            if ($response->successful()) {
                $responseXml = simplexml_load_string($response->body());
                $ackCode = (string) ($responseXml->ACKCODE ?? '');

                if ($ackCode === '0') {
                    return [
                        'success' => true,
                        'message' => 'Z-Report submitted successfully',
                        'summary' => $dailySummary,
                    ];
                }

                return [
                    'success' => false,
                    'message' => 'Z-Report rejected: ' . ($responseXml->ACKMSG ?? 'Unknown error'),
                ];
            }

            return [
                'success' => false,
                'message' => 'TRA API error: HTTP ' . $response->status(),
            ];
        } catch (\Exception $e) {
            Log::error('EFDMS Z-Report failed', ['error' => $e->getMessage(), 'company_id' => $company->id]);
            return [
                'success' => false,
                'message' => 'Connection failed: ' . $e->getMessage(),
            ];
        }
    }

    public function retryFailedReceipts(Company $company = null): array
    {
        $query = Transaction::fiscalPending()
            ->with(['items', 'user.company']);

        if ($company) {
            $query->where('company_id', $company->id);
        }

        $transactions = $query->limit(50)->get();
        $results = ['success' => 0, 'failed' => 0, 'total' => $transactions->count()];

        foreach ($transactions as $transaction) {
            $result = $this->signReceipt($transaction);

            if ($result['success']) {
                $transaction->update([
                    'fiscal_receipt_number' => $result['fiscal_receipt_number'],
                    'fiscal_verification_code' => $result['fiscal_verification_code'],
                    'fiscal_qr_code' => $result['fiscal_qr_code'],
                    'fiscal_receipt_time' => $result['fiscal_receipt_time'],
                    'fiscal_submitted' => true,
                    'fiscal_submission_error' => null,
                ]);
                $results['success']++;
            } else {
                $transaction->update([
                    'fiscal_submission_error' => $result['message'],
                ]);
                $results['failed']++;
            }
        }

        return $results;
    }
}
