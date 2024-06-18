<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class FileController extends Controller
{
    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $file = $request->file('file');
        $filePath = $request->path;
        $disk = $request->disk;

        $path = $file->storeAs($filePath, $file->getClientOriginalName(), $disk);

        if (!$path) {
            return response()->json([
                "errors" => 'Storing file failed',
            ], 500);
        }

        return response()->json([
            'data' => $path,
        ]);
    }

    /**
     * @param Request $request
     * @param string $name
     *
     * @return JsonResponse
     */
    public function destroy(Request $request, string $name): JsonResponse
    {   
        $filePath = $request->query('path');
        $fileName = urldecode($name);
        $disk = $request->query('disk');

        $file = str_replace('/', "\\", Storage::disk($disk)->path("$filePath/$fileName"));

        if (file_exists($file)) {
            unlink($file);
        }

        return response()->json();
    }
}
