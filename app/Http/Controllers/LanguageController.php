<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;

class LanguageController extends Controller
{
    protected array $supportedLocales = ['en', 'sw'];

    public function switch(string $locale): RedirectResponse
    {
        if (in_array($locale, $this->supportedLocales)) {
            session(['locale' => $locale]);
        }

        return back();
    }
}
