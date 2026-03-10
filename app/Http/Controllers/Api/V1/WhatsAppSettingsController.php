<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WhatsAppSettingsController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user->isCompanyOwner() && !$user->hasPermission('manage_settings')) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'whatsapp_receipts_enabled' => Setting::get('whatsapp_receipts_enabled', false),
                'whatsapp_receipts_mode' => Setting::get('whatsapp_receipts_mode', 'prompted'),
                'whatsapp_receipts_sms_fallback' => Setting::get('whatsapp_receipts_sms_fallback', true),
                'whatsapp_receipts_marketing_footer' => Setting::get('whatsapp_receipts_marketing_footer', ''),
            ],
        ]);
    }

    public function update(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user->isCompanyOwner() && !$user->hasPermission('manage_settings')) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $validated = $request->validate([
            'whatsapp_receipts_enabled' => 'sometimes|boolean',
            'whatsapp_receipts_mode' => 'sometimes|in:automatic,prompted',
            'whatsapp_receipts_sms_fallback' => 'sometimes|boolean',
            'whatsapp_receipts_marketing_footer' => 'sometimes|string|max:500',
        ]);

        foreach ($validated as $key => $value) {
            $type = is_bool($value) ? 'boolean' : 'string';
            Setting::set($key, $value, $type);
        }

        return response()->json([
            'success' => true,
            'message' => 'WhatsApp receipt settings updated.',
            'data' => [
                'whatsapp_receipts_enabled' => Setting::get('whatsapp_receipts_enabled', false),
                'whatsapp_receipts_mode' => Setting::get('whatsapp_receipts_mode', 'prompted'),
                'whatsapp_receipts_sms_fallback' => Setting::get('whatsapp_receipts_sms_fallback', true),
                'whatsapp_receipts_marketing_footer' => Setting::get('whatsapp_receipts_marketing_footer', ''),
            ],
        ]);
    }
}
