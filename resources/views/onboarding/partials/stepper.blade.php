<div class="progress-stepper">
    @for($i = 1; $i <= 4; $i++)
        <div class="step-item">
            <div class="step-circle {{ $i < $currentStep ? 'completed' : ($i == $currentStep ? 'active' : 'pending') }}">
                @if($i < $currentStep)
                    <i class="bi bi-check"></i>
                @else
                    {{ $i }}
                @endif
            </div>
        </div>
        @if($i < 4)
            <div class="step-connector {{ $i < $currentStep ? 'completed' : '' }}"></div>
        @endif
    @endfor
</div>
