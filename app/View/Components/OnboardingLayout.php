<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;

class OnboardingLayout extends Component
{
    public int $currentStep;
    public string $title;

    public function __construct(int $currentStep = 1, string $title = 'Get Started')
    {
        $this->currentStep = $currentStep;
        $this->title = $title;
    }

    public function render(): View
    {
        return view('layouts.onboarding');
    }
}
