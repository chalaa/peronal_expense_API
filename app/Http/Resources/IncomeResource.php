<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class IncomeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            "id"=> $this->id,
            "amount" => $this->amount,
            "date" => $this->date,
            "description" => $this->description,
            "category" => $this->category,
            "user" => $this->user
        ];

    }
}
