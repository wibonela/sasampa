<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Sanduku;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminSandukuController extends Controller
{
    public function index(Request $request): View
    {
        $query = Sanduku::query()->orderByDesc('created_at');

        // Filter by type
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $feedbacks = $query->paginate(20);

        $stats = [
            'total' => Sanduku::count(),
            'new' => Sanduku::where('status', 'new')->count(),
            'reviewed' => Sanduku::where('status', 'reviewed')->count(),
            'resolved' => Sanduku::where('status', 'resolved')->count(),
            'bugs' => Sanduku::where('type', 'bug')->count(),
            'feedback' => Sanduku::where('type', 'feedback')->count(),
        ];

        return view('admin.sanduku.index', compact('feedbacks', 'stats'));
    }

    public function show(Sanduku $sanduku): View
    {
        return view('admin.sanduku.show', compact('sanduku'));
    }

    public function updateStatus(Request $request, Sanduku $sanduku)
    {
        $validated = $request->validate([
            'status' => 'required|in:new,reviewed,resolved',
        ]);

        $sanduku->update($validated);

        return back()->with('success', 'Status updated successfully.');
    }

    public function destroy(Sanduku $sanduku)
    {
        // Delete screenshot if exists
        if ($sanduku->screenshot) {
            \Storage::disk('public')->delete($sanduku->screenshot);
        }

        $sanduku->delete();

        return redirect()->route('admin.sanduku.index')
            ->with('success', 'Feedback deleted successfully.');
    }
}
