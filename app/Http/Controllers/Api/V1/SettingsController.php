<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SettingsController extends Controller
{
    /**
     * Get all store settings
     */
    public function index(): JsonResponse
    {
        $company = auth()->user()->company;
        $settings = [];

        foreach (Setting::getDefaults() as $key => $default) {
            $value = Setting::get($key);

            // If no setting saved, use company data as fallback
            if ($value === null || $value === '') {
                $value = match($key) {
                    'store_name' => $company->name ?? $default,
                    'store_address' => $company->address ?? $default,
                    'store_phone' => $company->phone ?? $default,
                    'store_email' => $company->email ?? $default,
                    default => $default,
                };
            }

            $settings[$key] = $value;
        }

        // Add logo URL if exists
        if (!empty($settings['store_logo'])) {
            $settings['store_logo_url'] = Storage::disk('public')->url($settings['store_logo']);
        } else if ($company->logo) {
            $settings['store_logo_url'] = Storage::disk('public')->url($company->logo);
        } else {
            $settings['store_logo_url'] = null;
        }

        return response()->json([
            'data' => $settings,
        ]);
    }

    /**
     * Update store settings
     */
    public function update(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'store_name' => 'sometimes|string|max:255',
            'store_address' => 'nullable|string|max:500',
            'store_phone' => 'nullable|string|max:50',
            'store_email' => 'nullable|email|max:255',
            'currency_symbol' => 'nullable|string|max:10',
            'default_tax_rate' => 'nullable|numeric|min:0|max:100',
            'low_stock_threshold' => 'nullable|integer|min:0',
            'receipt_header' => 'nullable|string|max:500',
            'receipt_footer' => 'nullable|string|max:500',
        ]);

        foreach ($validated as $key => $value) {
            $type = match ($key) {
                'default_tax_rate' => 'float',
                'low_stock_threshold' => 'integer',
                default => 'string',
            };
            Setting::set($key, $value ?? '', $type);
        }

        return response()->json([
            'message' => 'Settings updated successfully.',
            'data' => $this->getSettingsArray(),
        ]);
    }

    /**
     * Upload store logo
     */
    public function uploadLogo(Request $request): JsonResponse
    {
        $request->validate([
            'logo' => 'required|image|max:2048', // Max 2MB
        ]);

        // Delete old logo if exists
        $oldLogo = Setting::get('store_logo');
        if ($oldLogo) {
            Storage::disk('public')->delete($oldLogo);
        }

        $logoPath = $request->file('logo')->store('logos', 'public');
        Setting::set('store_logo', $logoPath, 'string');

        return response()->json([
            'message' => 'Logo uploaded successfully.',
            'data' => [
                'logo_path' => $logoPath,
                'logo_url' => Storage::disk('public')->url($logoPath),
            ],
        ]);
    }

    /**
     * Remove store logo
     */
    public function removeLogo(): JsonResponse
    {
        $logo = Setting::get('store_logo');
        if ($logo) {
            Storage::disk('public')->delete($logo);
            Setting::set('store_logo', '', 'string');
        }

        return response()->json([
            'message' => 'Logo removed successfully.',
        ]);
    }

    /**
     * Get settings as array
     */
    private function getSettingsArray(): array
    {
        $company = auth()->user()->company;
        $settings = [];

        foreach (Setting::getDefaults() as $key => $default) {
            $value = Setting::get($key);

            if ($value === null || $value === '') {
                $value = match($key) {
                    'store_name' => $company->name ?? $default,
                    'store_address' => $company->address ?? $default,
                    'store_phone' => $company->phone ?? $default,
                    'store_email' => $company->email ?? $default,
                    default => $default,
                };
            }

            $settings[$key] = $value;
        }

        // Add logo URL
        if (!empty($settings['store_logo'])) {
            $settings['store_logo_url'] = Storage::disk('public')->url($settings['store_logo']);
        } else if ($company->logo) {
            $settings['store_logo_url'] = Storage::disk('public')->url($company->logo);
        } else {
            $settings['store_logo_url'] = null;
        }

        return $settings;
    }
}
