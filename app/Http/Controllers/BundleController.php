<?php

namespace App\Http\Controllers;

use App\Models\Bundle;
use App\Models\Application;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class BundleController extends Controller
{
    public function store(Request $request)
    {
        $isUuid = Str::isUuid($request->application_id);
        $column = $isUuid ? 'uuid' : 'id';

        $request->validate([
            'file'           => 'required|file',
            'application_id' => "required|exists:applications,{$column}",
            'name'           => 'nullable|string|max:255'
        ]);

        $file = $request->file('file');
        $path = Storage::disk('public')->putFile('bundles', $file);

        $applicationId = $isUuid 
            ? Application::where('uuid', $request->application_id)->value('id') 
            : $request->application_id;
            
        $bundleName = $request->name ?? $request->file('file')->getClientOriginalName();

        $bundle = Bundle::create([
            'file_path'      => $path,
            'name'           => $bundleName,
            'slug'           => Str::slug($bundleName),
            'size'           => $request->file('file')->getSize(),
            'user_id'        => $request->user()->id,
            'application_id' => $applicationId
        ]);

        return response()->json($bundle);
    }
}
