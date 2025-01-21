<?php

namespace App\Http\Resources;

use App\Http\Controllers\vendors\v1\VeMediaController;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ServiceCategoryResource extends JsonResource {
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array {
        $media = (new VeMediaController())->getMediaByModelId('service_category', $this->id);
        $icon_url = '';
        if (!empty($media->toArray())) {
            $icon_url = url('uploads/media/service_category/' . $media[0]['file']);
        }
        return [
            'id' => $this->id,
            'title' => app()->getLocale() === 'ar' ? $this->title_ar : $this->title_en,
            'title_ar' =>  $this->title_ar,
            'title_en' => $this->title_en,
            'description' => app()->getLocale() === 'ar' ? $this->description_ar : $this->description_en,
            'description_ar' =>  $this->description_ar,
            'description_en' => $this->description_en,
            'sector_id' => $this->sector_id,
            'sector' => app()->getLocale() === 'ar' ? $this->sector?->title_ar : $this->sector?->title_en,
            'icon' => $icon_url,
            'icon_url' => $icon_url,
            'created_at' => $this->created_at,
        ];
    }
}
