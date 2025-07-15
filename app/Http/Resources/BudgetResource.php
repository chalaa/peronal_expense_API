<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\TransactionResource;
class BudgetResource extends JsonResource
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
            "amount" => $this->amount,
            "color" => $this->color,
            "month" => $this->month,
            "year" => $this->year,
            "category" => $this->category,
            "user" => $this->user,
            'expense' => $this->category->transactions()
            ->where('user_id', $this->user_id)
            ->where('type', 'expense')
            ->whereYear('date', $this->year)
            ->whereMonth('date', $this->month)
            ->sum('amount'),    
        ];
    }
}
