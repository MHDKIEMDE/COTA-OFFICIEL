<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\BookmakerBlogResource;
use App\Models\BookmakerBlog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BookmakerBlogController extends Controller
{
    // GET /api/bookmakers/{bookmaker_id}/blog
    public function show(Request $request, int $bookmakerId): JsonResponse
    {
        $blog = BookmakerBlog::with('bookmaker')
            ->active()
            ->where('bookmaker_id', $bookmakerId)
            ->first();

        if (!$blog) {
            return response()->json(['success' => false, 'message' => 'Aucun blog disponible pour ce bookmaker.'], 404);
        }

        return response()->json([
            'success' => true,
            'data'    => new BookmakerBlogResource($blog),
        ]);
    }

    // GET /api/bookmakers/blogs  (tous les blogs actifs)
    public function index(): JsonResponse
    {
        $blogs = BookmakerBlog::with('bookmaker')
            ->active()
            ->whereHas('bookmaker', fn($q) => $q->where('is_active', true))
            ->get();

        return response()->json([
            'success' => true,
            'data'    => BookmakerBlogResource::collection($blogs),
        ]);
    }
}
