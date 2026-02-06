<?php

namespace App\Http\Controllers;

use App\Models\MobileWaitlist;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class MobileWaitlistController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => [
                'required',
                'string',
                'max:20',
                Rule::unique('mobile_waitlist', 'phone'),
            ],
            'email' => 'nullable|email|max:255',
            'business_name' => 'required|string|max:255',
            'business_type' => 'required|in:restaurant,retail,pharmacy,supermarket,salon,other',
            'platform' => 'required|in:ios,android,both',
        ], [
            'phone.unique' => 'This phone number is already on the waitlist.',
        ]);

        $validated['ip_address'] = $request->ip();
        $validated['status'] = 'pending';

        MobileWaitlist::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Hongera! You\'re on the list. We\'ll notify you when we launch.',
            'count' => MobileWaitlist::count(),
        ]);
    }

    public function count()
    {
        return response()->json([
            'count' => MobileWaitlist::count(),
        ]);
    }
}
