<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StorageController extends Controller
{
    /**
     * Display a file from storage/app/private if the user is authenticated.
     *
     * Behaviour:
     * - If the user is not authenticated, redirect to Laravel Nova login.
     * - If authenticated but the file doesn't exist, return 404.
     * - If authenticated and the file exists, return the file inline.
     */
    public function show(Request $request, $any)
    {
        // If the user is not authenticated, redirect them to Nova's login page
        if (!Auth::check()) {
            // Build the Nova login URL based on configured Nova path
            $novaPath = trim(config('nova.path', 'nova'), '/');
            $loginUrl = '/' . ($novaPath !== '' ? $novaPath . '/' : '') . 'login';

            return redirect()->guest($loginUrl);
        }

        // Build an absolute path within storage/app/private and guard against path traversal
        $basePath = rtrim(storage_path('app/private'), DIRECTORY_SEPARATOR);
        $requestedPath = $basePath . DIRECTORY_SEPARATOR . ltrim($any, DIRECTORY_SEPARATOR);

        $realBase = realpath($basePath);
        $realPath = $realBase !== false ? realpath($requestedPath) : false;

        // If file does not exist or is outside the allowed directory, abort with 404
        if ($realBase === false || $realPath === false || strpos($realPath, $realBase) !== 0 || !is_file($realPath)) {
            abort(404);
        }

        // Return the file inline; Laravel will attempt to infer the correct Content-Type
        return response()->file($realPath);
    }
}
