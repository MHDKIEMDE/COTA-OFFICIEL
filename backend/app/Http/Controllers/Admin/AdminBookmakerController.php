<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Bookmaker;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AdminBookmakerController extends Controller
{
    public function index(): JsonResponse
    {
        $bookmakers = Bookmaker::ordered()->get();

        return response()->json(['success' => true, 'data' => $bookmakers]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name'          => 'required|string|max:100',
            'slug'          => 'nullable|string|max:100|unique:bookmakers,slug',
            'primary_color' => 'nullable|string|max:20',
            'affiliate_link'=> 'required|url|max:500',
            'download_link' => 'nullable|url|max:500',
            'description'   => 'nullable|string|max:500',
            'is_active'     => 'boolean',
            'sort_order'    => 'integer|min:0',
        ]);

        $data['slug'] = $data['slug'] ?? Str::slug($data['name']);
        $data['primary_color'] = $data['primary_color'] ?? '#F9FF00';
        $data['is_active']  = $data['is_active'] ?? true;
        $data['sort_order'] = $data['sort_order'] ?? 0;

        $bookmaker = Bookmaker::create($data);

        return response()->json(['success' => true, 'data' => $bookmaker], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $bookmaker = Bookmaker::findOrFail($id);

        $data = $request->validate([
            'name'          => 'sometimes|string|max:100',
            'slug'          => 'sometimes|string|max:100|unique:bookmakers,slug,' . $id,
            'primary_color' => 'nullable|string|max:20',
            'affiliate_link'=> 'sometimes|url|max:500',
            'download_link' => 'nullable|url|max:500',
            'description'   => 'nullable|string|max:500',
            'is_active'     => 'boolean',
            'sort_order'    => 'integer|min:0',
        ]);

        $bookmaker->update($data);

        return response()->json(['success' => true, 'data' => $bookmaker->fresh()]);
    }

    public function destroy(int $id): JsonResponse
    {
        $bookmaker = Bookmaker::findOrFail($id);
        $bookmaker->delete();

        return response()->json(['success' => true, 'message' => 'Bookmaker supprimé.']);
    }
}
