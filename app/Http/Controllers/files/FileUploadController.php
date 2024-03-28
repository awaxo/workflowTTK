<?php

namespace App\Http\Controllers\files;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class FileUploadController extends Controller
{
    public function upload(Request $request)
    {
        $file = $request->file('file');
        if ($file) {
            $path = $file->store('uploads', 'public'); // Adjust the path and disk as necessary
            
            // Optionally, store the file path in session to retrieve it later
            $uploads = session()->get('file_uploads', []);
            $uploads[$request->input('type')] = $path; // Use a 'type' parameter to differentiate between different uploads
            session(['file_uploads' => $uploads]);

            return response()->json(['path' => $path], 201); // Return the path for immediate use or confirmation
        }

        return response()->json(['error' => 'No file uploaded'], 400);
    }
}
