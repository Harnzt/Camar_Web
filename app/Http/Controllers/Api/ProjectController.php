<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProjectResource;
use App\Models\Project;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'category' => ['nullable', 'string', 'max:100'],
            'search' => ['nullable', 'string', 'max:100'],
        ]);

        $projects = Project::query()
            ->approved()
            ->when(
                $validated['category'] ?? null,
                fn ($query, $category) => $query->where('category', $category),
            )
            ->when(
                $validated['search'] ?? null,
                fn ($query, $search) => $query->where(function ($nested) use ($search) {
                    $nested
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('location', 'like', "%{$search}%")
                        ->orWhere('company_name', 'like', "%{$search}%");
                }),
            )
            ->latest()
            ->orderByDesc('id')
            ->get();

        return response()->json(
            ProjectResource::collection($projects)->resolve(),
        );
    }

    public function show(Project $project): JsonResponse
    {
        abort_unless($project->verification_status === 'approved', 404);

        return response()->json(
            ProjectResource::make($project)->resolve(),
        );
    }
}
