<?php

namespace App\Http\Controllers;

use App\Models\Bundle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class BundleController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'file' => 'required|file',
            'application_id' => 'required|exists:applications,id'
        ]);

        $file = $request->file('file');
        $path = Storage::disk('public')->putFile('bundles', $file);

        $bundle = Bundle::create([
            'file_path' => $path,
            'name' => $request->file('file')->getClientOriginalName(),
            'slug' => Str::slug($request->file('file')->getClientOriginalName()),
            'size' => $request->file('file')->getSize(),
            'user_id' => $request->user()->id,
            'application_id' => $request->application_id
        ]);

        return response()->json($bundle);
    }
}
