<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProjectResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => (string) $this->id,
            'title' => $this->name,
            'company_name' => $this->company_name,
            'location' => $this->location,
            'category' => $this->category,
            'standard' => $this->standard,
            'duration_months' => (int) $this->duration_months,
            'estimated_credits' => (int) ($this->co2_per_year ?? 0),
            'verification_status' => $this->verification_status,
            'price_per_ton' => (float) $this->price_per_ton,
            'stock_available' => (int) $this->stock_available,
            'area_ha' => $this->area_ha === null ? null : (int) $this->area_ha,
            'families_impacted' => $this->families_impacted === null
                ? null
                : (int) $this->families_impacted,
            'verified_year' => $this->verified_year === null
                ? null
                : (int) $this->verified_year,
            'description' => $this->description,
            'methodology' => $this->methodology,
            'image_url' => $this->imageUrl($request),
            'created_at' => $this->created_at?->toISOString(),
        ];
    }

    private function imageUrl(Request $request): string
    {
        $imagePath = $this->image
            && file_exists(public_path('images/'.$this->image))
                ? 'images/'.ltrim($this->image, '/')
                : 'images/placeholder-project.jpg';

        return $request->getSchemeAndHttpHost().'/'.$imagePath;
    }
}
