<?php

namespace App\Http\Controllers;

use App\Models\Bundle;
use App\Models\Application;
use App\Models\Channel;
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
            'channel'        => "nullable|exists:channels,name",
            'name'           => 'nullable|string|max:255'
        ]);

        $file = $request->file('file');
        $path = Storage::disk('public')->putFile('bundles', $file);

        $applicationId = $isUuid 
            ? Application::where('uuid', $request->application_id)->value('id') 
            : $request->application_id;


        $application = $request->user()->applications()->find($applicationId);

        if (!$application) {
            return response()->json([
                'message' => 'You are not authorized to upload bundles for this application'
            ], 403);
        }

        if ($application->bundles()->count() >= $application->bundle_limit) {
            return response()->json([
                'message' => 'You have reached the maximum number of bundles for this application'
            ], 403);
        }

        $bundleName = $request->name ?? $request->file('file')->getClientOriginalName();

        $channelId = Channel::where('name', $request->channel)->where('application_id', $applicationId)->value('id');

        $bundle = Bundle::create([
            'file_path'      => $path,
            'name'           => $bundleName,
            'slug'           => Str::slug($bundleName),
            'size'           => $request->file('file')->getSize(),
            'user_id'        => $request->user()->id,
            'channel_id'     => $channelId,
            'application_id' => $applicationId
        ]);

        return response()->json($bundle);
    }
}
