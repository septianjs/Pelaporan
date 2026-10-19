<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Template;
use Illuminate\Http\JsonResponse;

class TemplateController extends Controller
{
    /**
     * Return only built-in PDF themes. Legacy uploaded PowerPoint templates are
     * intentionally excluded from the PDF-only workflow.
     */
    public function index(): JsonResponse
    {
        $templates = Template::where('active', true)
            ->where('type', 'builtin')
            ->orderBy('id')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $templates,
        ]);
    }
}
