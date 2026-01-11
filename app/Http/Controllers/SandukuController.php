<?php

namespace App\Http\Controllers;

use App\Models\Sanduku;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SandukuController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|in:feedback,bug',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'contact' => 'nullable|string|max:255',
            'page_url' => 'nullable|string|max:500',
            'user_agent' => 'nullable|string',
            'screenshot' => 'nullable|image|max:5120', // 5MB max
        ]);

        // Handle screenshot upload
        if ($request->hasFile('screenshot')) {
            $path = $request->file('screenshot')->store('sanduku', 'public');
            $validated['screenshot'] = $path;
        }

        $sanduku = Sanduku::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Asante! Tumepokea feedback yako.',
            'id' => $sanduku->id,
        ]);
    }
}
