<?php

namespace App\Http\Controllers\files;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class FileUploadController extends Controller
{
    public function upload(Request $request)
    {
        $file = $request->file('file');
        if ($file) {
            $path = $file->store('uploads', 'public');

            $url = Storage::url($path);
            $serverFilename = basename($path);

            return response()->json(['fileName' => $serverFilename], 201);
        }

        return response()->json(['error' => 'No file uploaded'], 400);
    }
}
