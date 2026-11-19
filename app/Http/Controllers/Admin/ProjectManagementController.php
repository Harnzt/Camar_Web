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
    public function __construct(private readonly AdminAuditService $audit) {}

    public function create(): View
    {
        return view('main_page.admin-panel.projects.form', ['project' => new Project]);
    }

    /**
     * Fields that change what a buyer's certificate certifies. Editing one of
     * these on an approved project sends it back to the auditor.
     */
    private const INTEGRITY_FIELDS = ['methodology', 'standard', 'verified_year', 'co2_per_year'];

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validatedProject($request);
        $validated['seller_id'] = null;
        // Proyek baru tidak langsung tayang: auditor memeriksanya lebih dulu.
        $validated['verification_status'] = 'pending';
        $validated['submitted_at'] = now();
        $validated['image'] = $this->storeImage($request);

        $project = Project::create($validated);
        $this->audit->log('project.created', "Admin membuat proyek {$project->name} dan mengajukannya ke auditor.", $project, [], $project->only([
            'name', 'company_name', 'category', 'location', 'price_per_ton', 'stock_available', 'verification_status',
        ]));

        return redirect()->route('admin.projects.index')
            ->with('success', 'Proyek berhasil ditambahkan dan dikirim ke auditor untuk diverifikasi. Proyek akan tayang setelah disetujui.');
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

        $reviewReason = $this->reviewReason($project, $validated);
        if ($reviewReason !== null) {
            $oldValues['verification_status'] = $project->verification_status;
            $validated['verification_status'] = 'pending';
            $validated['submitted_at'] = now();
        }

        $project->update($validated);
        $this->audit->log(
            'project.updated',
            "Admin memperbarui proyek {$project->name}.".($reviewReason ? " Dikirim ulang ke auditor: {$reviewReason}." : ''),
            $project,
            $oldValues,
            $project->only(array_keys($validated))
        );

        return redirect()->route('admin.projects.index')->with(
            'success',
            $reviewReason
                ? "Proyek diperbarui dan dikirim ulang ke auditor ({$reviewReason}). Proyek tidak tayang sampai disetujui."
                : 'Proyek berhasil diperbarui.'
        );
    }

    /**
     * Why an update needs a new auditor decision, or null when it does not.
     * Lowering stock or editing price/description keeps the project live.
     */
    private function reviewReason(Project $project, array $validated): ?string
    {
        if (in_array($project->verification_status, ['rejected', 'revision_required'], true)) {
            return 'perbaikan setelah catatan auditor';
        }

        if ($project->verification_status !== 'approved') {
            return null;
        }

        if ((int) $validated['stock_available'] > (int) $project->stock_available) {
            return 'stok karbon ditambah';
        }

        foreach (self::INTEGRITY_FIELDS as $field) {
            if ((string) ($validated[$field] ?? '') !== (string) ($project->{$field} ?? '')) {
                return 'data metodologi/verifikasi berubah';
            }
        }

        return null;
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
