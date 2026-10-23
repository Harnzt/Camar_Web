<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Services\AdminAuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProjectManagementController extends Controller
{
    public function __construct(private readonly AdminAuditService $audit)
    {
    }

    public function create(): View
    {
        return view('main_page.admin-panel.projects.form', ['project' => new Project()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validatedProject($request);
        $validated['seller_id'] = null;
        $validated['verification_status'] = 'approved';
        $validated['reviewed_by'] = $request->user()->id;
        $validated['reviewed_at'] = now();
        $validated['submitted_at'] = now();
        $validated['image'] = $this->storeImage($request);

        $project = Project::create($validated);
        $this->audit->log('project.created', "Admin membuat proyek {$project->name}.", $project, [], $project->only([
            'name', 'company_name', 'category', 'location', 'price_per_ton', 'stock_available',
        ]));

        return redirect()->route('admin.projects.index')->with('success', 'Proyek berhasil ditambahkan.');
    }

    public function edit(Project $project): View
    {
        return view('main_page.admin-panel.projects.form', compact('project'));
    }

    public function update(Request $request, Project $project): RedirectResponse
    {
        $validated = $this->validatedProject($request);
        $oldValues = $project->only(array_keys($validated));
        $image = $this->storeImage($request);
        if ($image !== null) {
            $validated['image'] = $image;
        } else {
            unset($validated['image']);
        }

        $project->update($validated);
        $this->audit->log('project.updated', "Admin memperbarui proyek {$project->name}.", $project, $oldValues, $project->only(array_keys($validated)));

        return redirect()->route('admin.projects.index')->with('success', 'Proyek berhasil diperbarui.');
    }

    public function destroy(Project $project): RedirectResponse
    {
        if ($project->orders()->exists() || $project->transactions()->exists()) {
            return redirect()->route('admin.projects.index')
                ->with('error', 'Proyek yang sudah memiliki transaksi tidak dapat dihapus.');
        }

        $name = $project->name;
        $oldValues = $project->only(['name', 'company_name', 'category', 'verification_status']);
        $project->delete();
        $this->audit->log('project.deleted', "Admin menghapus proyek {$name}.", $project, $oldValues);

        return redirect()->route('admin.projects.index')->with('success', 'Proyek berhasil dihapus.');
    }

    private function validatedProject(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'company_name' => ['required', 'string', 'max:255'],
            'category' => ['required', 'string', 'max:100'],
            'location' => ['required', 'string', 'max:255'],
            'standard' => ['nullable', 'string', 'max:255'],
            'duration_months' => ['required', 'integer', 'min:1'],
            'price_per_ton' => ['required', 'numeric', 'min:0'],
            'stock_available' => ['required', 'integer', 'min:0'],
            'area_ha' => ['nullable', 'integer', 'min:0'],
            'co2_per_year' => ['nullable', 'integer', 'min:0'],
            'families_impacted' => ['nullable', 'integer', 'min:0'],
            'verified_year' => ['nullable', 'integer', 'min:1900', 'max:'.now()->year],
            'description' => ['required', 'string', 'max:10000'],
            'methodology' => ['nullable', 'string', 'max:10000'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);
    }

    private function storeImage(Request $request): ?string
    {
        if (! $request->hasFile('image')) {
            return null;
        }

        $image = $request->file('image');
        $fileName = uniqid('project_', true).'.'.$image->extension();
        $image->move(public_path('images'), $fileName);

        return $fileName;
    }
}
