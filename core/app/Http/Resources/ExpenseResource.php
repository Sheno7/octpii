<?php

namespace App\Http\Resources;

use App\Http\Controllers\vendors\v1\VeMediaController;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ExpenseResource extends JsonResource {
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array {
        $media = (new VeMediaController())->getMediaByModelId('expense', $this->id);
        $attachment_url = '';
        if (count($media) > 0) {
            $attachment_url = url('uploads/media/expense/' . $media[0]['file']);
        }

        return [
            'id' => $this->id,
            'category_id' => $this->category_id,
            'category' => new ExpenseCategoryResource($this->category),
            'branch_name' => $this->branch?->name,
            'date' => $this->date,
            'amount' => $this->amount,
            'media' => $media,
            'attachment_url' => $attachment_url,
            'payment_method_id' => $this->payment_method_id,
            'notes' => $this->notes,
            'sector_id' => $this->sector_id,
            'sector_title' => app()->getLocale() === 'ar' ? $this->sector?->title_ar : $this->sector?->title_en,
        ];
    }
}
