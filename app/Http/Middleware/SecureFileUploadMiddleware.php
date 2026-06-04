<?php

namespace App\Http\Middleware;

use App\Services\SecureFileUploadService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecureFileUploadMiddleware
{
    public function __construct(protected SecureFileUploadService $uploader) {}

    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->hasFile('*') && empty($request->allFiles())) {
            return $next($request);
        }

        $errors = [];
        foreach ($request->allFiles() as $file) {
            if (is_array($file)) {
                foreach ($file as $f) {
                    $result = $this->uploader->validate($f);
                    if (!$result['safe']) {
                        $errors[] = $result['reason'] ?? 'File tidak aman';
                    }
                }
            } else {
                $result = $this->uploader->validate($file);
                if (!$result['safe']) {
                    $errors[] = $result['reason'] ?? 'File tidak aman';
                }
            }
        }

        if (!empty($errors)) {
            return response()->json([
                'message' => 'Upload tidak valid.',
                'errors' => $errors,
            ], 422);
        }

        return $next($request);
    }
}
