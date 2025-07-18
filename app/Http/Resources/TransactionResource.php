<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TransactionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            "id" => $this->id,
            "type" => $this->type,
            "amount" => $this->amount,
            "date" => $this->date,
            "description" => $this->description,  
            "category" => $this->category,
            "user_id" =>$this->user,
        ];
    }
}
