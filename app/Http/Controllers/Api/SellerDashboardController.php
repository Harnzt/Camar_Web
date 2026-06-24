<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProjectResource;
use App\Models\Order;
use App\Models\Project;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SellerDashboardController extends Controller
{
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

        $project = Project::create($validated + [
            'seller_id' => $user->id,
            'company_name' => $validated['company_name'] ?? $user->company_name ?? 'Mitra CAMAR',
            'verification_status' => 'pending',
            'submitted_at' => now(),
        ]);

        return response()->json([
            'message' => 'Proyek berhasil diajukan dan menunggu verifikasi admin.',
            'project' => $this->projectData($request, $project),
        ], 201);
    }

    public function update(Request $request, Project $project): JsonResponse
    {
        abort_unless((int) $project->seller_id === (int) $request->user()->id, 404);

        $validated = $this->validateProject($request);
        $project->update($validated + [
            'verification_status' => 'pending',
            'reviewed_by' => null,
            'reviewed_at' => null,
            'rejection_reason' => null,
            'admin_notes' => null,
            'submitted_at' => now(),
        ]);

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
        ]);
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
