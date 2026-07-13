<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProjectResource;
use App\Models\DocumentVerification;
use App\Models\Order;
use App\Models\Project;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

class SellerDashboardController extends Controller
{
    private const PROJECT_DOCUMENT_FIELDS = [
        'methodology_document' => 'Dokumen Metodologi',
        'verification_certificate' => 'Sertifikat Verifikasi',
        'location_map' => 'Peta Lokasi',
        'mrv_report' => 'Laporan MRV',
    ];

    public function __invoke(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'summary' => $this->summary($user->id),
            'projects' => $this->sellerProjects($user->id)
                ->map(fn (Project $project) => $this->projectData($request, $project))
                ->values(),
            'recent_sales' => $this->sellerOrders($user->id)
                ->take(8)
                ->get()
                ->map(fn (Order $order) => $this->orderData($order))
                ->values(),
        ]);
    }

    public function projects(Request $request): JsonResponse
    {
        return response()->json([
            'projects' => $this->sellerProjects($request->user()->id)
                ->map(fn (Project $project) => $this->projectData($request, $project))
                ->values(),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $this->validateProject($request);
        $user = $request->user();
        $projectPayload = $this->projectPayload($validated);
        $projectPayload['company_name'] = $projectPayload['company_name']
            ?? $user->company_name
            ?? 'Mitra CAMAR';

        if ($image = $this->storeProjectImage($request)) {
            $projectPayload['image'] = $image;
        }

        $project = Project::create($projectPayload + [
            'seller_id' => $user->id,
            'verification_status' => 'pending',
            'submitted_at' => now(),
        ]);

        $this->storeProjectDocuments($request, $project);

        return response()->json([
            'message' => 'Proyek berhasil diajukan dan menunggu verifikasi admin.',
            'project' => $this->projectData($request, $project),
        ], 201);
    }

    public function update(Request $request, Project $project): JsonResponse
    {
        abort_unless((int) $project->seller_id === (int) $request->user()->id, 404);

        $validated = $this->validateProject($request);
        $projectPayload = $this->projectPayload($validated);

        if ($image = $this->storeProjectImage($request, $project)) {
            $projectPayload['image'] = $image;
        }

        $project->update($projectPayload + [
            'verification_status' => 'pending',
            'reviewed_by' => null,
            'reviewed_at' => null,
            'rejection_reason' => null,
            'admin_notes' => null,
            'submitted_at' => now(),
        ]);

        $this->storeProjectDocuments($request, $project->fresh());

        return response()->json([
            'message' => 'Data proyek diperbarui dan diajukan kembali untuk verifikasi.',
            'project' => $this->projectData($request, $project->fresh()),
        ]);
    }

    public function destroy(Request $request, Project $project): JsonResponse
    {
        abort_unless((int) $project->seller_id === (int) $request->user()->id, 404);

        $project->delete();

        return response()->json([
            'message' => 'Proyek berhasil dihapus.',
        ]);
    }

    public function transactions(Request $request): JsonResponse
    {
        $orders = $this->sellerOrders($request->user()->id)->get();

        return response()->json([
            'transactions' => $orders->map(fn (Order $order) => $this->orderData($order))->values(),
            'summary' => $this->summary($request->user()->id),
        ]);
    }

    private function validateProject(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'company_name' => ['nullable', 'string', 'max:255'],
            'category' => ['required', 'string', 'max:100'],
            'location' => ['required', 'string', 'max:255'],
            'standard' => ['nullable', 'string', 'max:100'],
            'duration_months' => ['nullable', 'integer', 'min:1'],
            'price_per_ton' => ['required', 'numeric', 'min:0'],
            'stock_available' => ['required', 'integer', 'min:0'],
            'area_ha' => ['nullable', 'integer', 'min:0'],
            'co2_per_year' => ['nullable', 'integer', 'min:0'],
            'families_impacted' => ['nullable', 'integer', 'min:0'],
            'verified_year' => ['nullable', 'integer', 'min:1900'],
            'description' => ['required', 'string'],
            'methodology' => ['nullable', 'string'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'methodology_document' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'],
            'verification_certificate' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'],
            'location_map' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'],
            'mrv_report' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'],
        ]);
    }

    private function projectPayload(array $validated): array
    {
        unset($validated['image']);

        foreach (array_keys(self::PROJECT_DOCUMENT_FIELDS) as $field) {
            unset($validated[$field]);
        }

        return $validated;
    }

    private function storeProjectImage(Request $request, ?Project $project = null): ?string
    {
        if (!$request->hasFile('image')) {
            return null;
        }

        if ($project?->image && file_exists(public_path('images/'.$project->image))) {
            unlink(public_path('images/'.$project->image));
        }

        $file = $request->file('image');
        $filename = 'project_'.$request->user()->id.'_'.now()->format('YmdHis').'_'.Str::random(8).'.'.$file->extension();
        $file->move(public_path('images'), $filename);

        return $filename;
    }

    private function storeProjectDocuments(Request $request, Project $project): void
    {
        foreach (self::PROJECT_DOCUMENT_FIELDS as $field => $label) {
            if (!$request->hasFile($field)) {
                continue;
            }

            $path = $this->storePrivateProjectDocument(
                $request->file($field),
                $project,
                $field
            );

            DocumentVerification::updateOrCreate(
                [
                    'user_id' => $project->seller_id,
                    'document_type' => "project_{$project->id}_{$field}",
                ],
                [
                    'document_path' => $path,
                    'status' => 'pending',
                    'reviewed_by' => null,
                    'reviewed_at' => null,
                    'rejection_reason' => null,
                    'notes' => "{$label} untuk proyek {$project->name}.",
                ]
            );
        }
    }

    private function storePrivateProjectDocument(UploadedFile $file, Project $project, string $field): string
    {
        $filename = $field.'_'.now()->format('YmdHis').'_'.Str::random(8).'.'.$file->getClientOriginalExtension();

        return $file->storeAs(
            "project-documents/{$project->seller_id}/{$project->id}",
            $filename,
            'private'
        );
    }

    private function sellerProjects(int $sellerId)
    {
        return Project::query()
            ->where('seller_id', $sellerId)
            ->withCount('orders')
            ->withSum(['orders as revenue_sum' => fn ($query) => $query->whereIn('status', ['paid', 'verified', 'completed'])], 'total_price')
            ->withSum(['orders as carbon_sum' => fn ($query) => $query->whereIn('status', ['paid', 'verified', 'completed'])], 'quantity')
            ->latest()
            ->get();
    }

    private function sellerOrders(int $sellerId)
    {
        return Order::query()
            ->with(['project', 'user'])
            ->whereHas('project', fn ($query) => $query->where('seller_id', $sellerId))
            ->latest();
    }

    private function summary(int $sellerId): array
    {
        $successfulOrders = Order::query()
            ->whereHas('project', fn ($query) => $query->where('seller_id', $sellerId))
            ->whereIn('status', ['paid', 'verified', 'completed']);

        return [
            'total_revenue' => (float) (clone $successfulOrders)->sum('total_price'),
            'total_carbon_sold' => (float) (clone $successfulOrders)->sum('quantity'),
            'active_projects' => Project::query()->where('seller_id', $sellerId)->where('stock_available', '>', 0)->count(),
            'total_stock' => (int) Project::query()->where('seller_id', $sellerId)->sum('stock_available'),
            'pending_count' => Order::query()
                ->whereHas('project', fn ($query) => $query->where('seller_id', $sellerId))
                ->where('status', 'pending')
                ->count(),
        ];
    }

    private function projectData(Request $request, Project $project): array
    {
        $data = ProjectResource::make($project)->resolve($request);
        $data['orders_count'] = (int) ($project->orders_count ?? 0);
        $data['revenue_sum'] = (float) ($project->revenue_sum ?? 0);
        $data['carbon_sum'] = (float) ($project->carbon_sum ?? 0);

        return $data;
    }

    private function orderData(Order $order): array
    {
        return [
            'id' => (string) $order->id,
            'code' => $order->order_number,
            'buyer_name' => $order->user?->name,
            'project_id' => (string) $order->project_id,
            'project_name' => $order->project?->name ?? 'Proyek',
            'category' => $order->project?->category ?? '-',
            'quantity_ton' => (float) $order->quantity,
            'total_price' => (float) $order->total_price,
            'payment_method' => $order->payment_method,
            'status' => $order->status,
            'created_at' => $order->created_at?->toISOString(),
        ];
    }
}
