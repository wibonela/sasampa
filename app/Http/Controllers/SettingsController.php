<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class SettingsController extends Controller
{
    public function index(): View
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

        return view('settings.index', compact('settings'));
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'store_name' => 'required|string|max:255',
            'store_logo' => 'nullable|image|max:2048',
            'store_address' => 'nullable|string|max:500',
            'store_phone' => 'nullable|string|max:50',
            'store_email' => 'nullable|email|max:255',
            'currency_symbol' => 'nullable|string|max:10',
            'default_tax_rate' => 'nullable|numeric|min:0|max:100',
            'low_stock_threshold' => 'nullable|integer|min:0',
            'receipt_header' => 'nullable|string|max:500',
            'receipt_footer' => 'nullable|string|max:500',
        ]);

        // Apply defaults for nullable fields
        $validated['currency_symbol'] = $validated['currency_symbol'] ?? 'TZS';
        $validated['default_tax_rate'] = $validated['default_tax_rate'] ?? 0;
        $validated['low_stock_threshold'] = $validated['low_stock_threshold'] ?? 10;

        // Handle logo upload
        if ($request->hasFile('store_logo')) {
            // Delete old logo if exists
            $oldLogo = Setting::get('store_logo');
            if ($oldLogo) {
                Storage::disk('public')->delete($oldLogo);
            }
            $logoPath = $request->file('store_logo')->store('logos', 'public');
            Setting::set('store_logo', $logoPath, 'string');
        }

        // Remove store_logo from validated array (handled separately)
        unset($validated['store_logo']);

        foreach ($validated as $key => $value) {
            $type = match ($key) {
                'default_tax_rate' => 'float',
                'low_stock_threshold' => 'integer',
                default => 'string',
            };
            Setting::set($key, $value ?? '', $type);
        }

        return redirect()->route('settings.index')
            ->with('success', 'Settings updated successfully.');
    }

    public function removeLogo(): RedirectResponse
    {
        $logo = Setting::get('store_logo');
        if ($logo) {
            Storage::disk('public')->delete($logo);
            Setting::set('store_logo', '', 'string');
        }

        return redirect()->route('settings.index')
            ->with('success', 'Logo removed successfully.');
    }
}
