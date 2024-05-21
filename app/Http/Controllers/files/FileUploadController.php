<?php

namespace App\Http\Controllers\files;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class FileUploadController extends Controller
{
    public function upload(Request $request)
    {
        $file = $request->file('file');
        if ($file) {
            $extension = $file->getClientOriginalExtension();
            $filename = Str::random(15) . '.' . $extension;
            $path = $file->storeAs('uploads', $filename, 'public');            
            $serverFilename = basename($path);

            return response()->json(['fileName' => $serverFilename], 201);
        }

        return response()->json(['error' => 'No file uploaded'], 400);
    }
}
