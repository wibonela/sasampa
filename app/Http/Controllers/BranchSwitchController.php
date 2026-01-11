<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class BranchSwitchController extends Controller
{
    /**
     * Switch the current active branch
     */
    public function switch(Request $request, Branch $branch): RedirectResponse
    {
        $user = auth()->user();

        if (!$user->canAccessBranch($branch)) {
            abort(403, 'You do not have access to this branch.');
        }

        $user->setCurrentBranch($branch);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'branch' => $branch->only(['id', 'name', 'code']),
            ]);
        }

        return back()->with('success', "Switched to {$branch->name}");
    }
}
