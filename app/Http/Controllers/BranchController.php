<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BranchController extends Controller
{
    public function index(): View
    {
        $branches = Branch::with(['users'])
            ->orderBy('is_main', 'desc')
            ->orderBy('name')
            ->get();

        return view('branches.index', compact('branches'));
    }

    public function create(): View
    {
        return view('branches.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:20|unique:branches,code,NULL,id,company_id,' . auth()->user()->company_id,
            'address' => 'nullable|string|max:500',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'is_main' => 'boolean',
        ]);

        $company = auth()->user()->company;

        // If this is the first branch or marked as main, handle main flag
        if ($request->boolean('is_main') || !$company->branches()->exists()) {
            // Remove main flag from other branches
            $company->branches()->update(['is_main' => false]);
            $validated['is_main'] = true;
        }

        $branch = Branch::create($validated);

        // Auto-assign the owner to this branch
        $branch->users()->attach(auth()->id(), ['is_default' => false]);

        return redirect()->route('branches.index')
            ->with('success', 'Branch created successfully.');
    }

    public function edit(Branch $branch): View
    {
        return view('branches.edit', compact('branch'));
    }

    public function update(Request $request, Branch $branch): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:20|unique:branches,code,' . $branch->id . ',id,company_id,' . auth()->user()->company_id,
            'address' => 'nullable|string|max:500',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'is_main' => 'boolean',
            'is_active' => 'boolean',
        ]);

        // Handle main branch flag
        if ($request->boolean('is_main') && !$branch->is_main) {
            auth()->user()->company->branches()->update(['is_main' => false]);
            $validated['is_main'] = true;
        }

        $branch->update($validated);

        return redirect()->route('branches.index')
            ->with('success', 'Branch updated successfully.');
    }

    public function destroy(Branch $branch): RedirectResponse
    {
        if ($branch->is_main) {
            return back()->with('error', 'Cannot delete the main branch. Please assign another branch as main first.');
        }

        // Check if branch has transactions
        if ($branch->transactions()->exists()) {
            return back()->with('error', 'Cannot delete branch with existing transactions. Please deactivate it instead.');
        }

        $branch->delete();

        return redirect()->route('branches.index')
            ->with('success', 'Branch deleted successfully.');
    }

    /**
     * Manage users assigned to a branch
     */
    public function users(Branch $branch): View
    {
        $branch->load('users');

        $availableUsers = auth()->user()->company->users()
            ->whereNotIn('id', $branch->users->pluck('id'))
            ->where('role', '!=', 'platform_admin')
            ->get();

        return view('branches.users', compact('branch', 'availableUsers'));
    }

    /**
     * Assign a user to a branch
     */
    public function assignUser(Request $request, Branch $branch): RedirectResponse
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        // Verify user belongs to same company
        $user = auth()->user()->company->users()->findOrFail($request->user_id);

        if ($branch->users()->where('user_id', $user->id)->exists()) {
            return back()->with('error', 'User is already assigned to this branch.');
        }

        $branch->users()->attach($user->id, [
            'is_default' => $request->boolean('is_default')
        ]);

        return back()->with('success', "{$user->name} has been assigned to {$branch->name}.");
    }

    /**
     * Remove a user from a branch
     */
    public function removeUser(Branch $branch, int $userId): RedirectResponse
    {
        $user = $branch->users()->findOrFail($userId);

        // Don't remove owner from their own branches if it's the only one
        if ($user->id === auth()->id() && auth()->user()->branches()->count() <= 1) {
            return back()->with('error', 'You cannot remove yourself from your only branch.');
        }

        $branch->users()->detach($userId);

        return back()->with('success', "{$user->name} has been removed from {$branch->name}.");
    }

    /**
     * Set user's default branch
     */
    public function setDefaultBranch(Request $request, Branch $branch, int $userId): RedirectResponse
    {
        $user = $branch->users()->findOrFail($userId);

        // Remove default from all other branches for this user
        $user->branches()->updateExistingPivot(
            $user->branches->pluck('id')->toArray(),
            ['is_default' => false]
        );

        // Set this branch as default
        $branch->users()->updateExistingPivot($userId, ['is_default' => true]);

        return back()->with('success', "{$branch->name} is now {$user->name}'s default branch.");
    }
}
