<?php

namespace App\Http\Controllers;

use App\Mail\NewCompanyRegistration;
use App\Models\Company;
use App\Models\User;
use App\Services\AdminNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rules\Password;

class CompanyRegistrationController extends Controller
{
    public function __construct(
        private AdminNotificationService $notificationService
    ) {}

    public function showRegistrationForm()
    {
        return view('company.register');
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            // Company fields
            'company_name' => 'required|string|max:255',
            'company_email' => 'required|email|unique:companies,email',
            'company_phone' => 'nullable|string|max:20',
            'company_address' => 'nullable|string|max:500',
            // Owner fields
            'owner_name' => 'required|string|max:255',
            'owner_email' => 'required|email|unique:users,email',
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        $company = DB::transaction(function () use ($validated) {
            // Create company
            $company = Company::create([
                'name' => $validated['company_name'],
                'email' => $validated['company_email'],
                'phone' => $validated['company_phone'],
                'address' => $validated['company_address'],
                'status' => Company::STATUS_PENDING,
            ]);

            // Create owner user
            User::create([
                'name' => $validated['owner_name'],
                'email' => $validated['owner_email'],
                'password' => Hash::make($validated['password']),
                'company_id' => $company->id,
                'role' => User::ROLE_COMPANY_OWNER,
            ]);

            return $company;
        });

        // Create notification for platform admin
        $this->notificationService->notifyNewCompanyRegistration($company);

        // Send email to platform admin(s)
        $admins = User::where('role', User::ROLE_PLATFORM_ADMIN)->get();
        foreach ($admins as $admin) {
            Mail::to($admin->email)->queue(new NewCompanyRegistration($company));
        }

        return redirect()->route('login')
            ->with('success', 'Registration submitted! You will be notified once your business is approved.');
    }

    public function pending()
    {
        $user = auth()->user();

        // If no company, redirect to registration
        if (!$user->company_id) {
            return redirect()->route('company.register');
        }

        $company = $user->company;

        // If already approved, redirect to dashboard
        if ($company->isApproved()) {
            return redirect()->route('dashboard');
        }

        return view('company.pending', compact('company'));
    }
}
