<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReferralItemResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'name'          => $this->resource['name'],
            'status'        => $this->resource['status'],
            'reward_days'   => $this->resource['reward_days'],
            'reward_applied'=> $this->resource['reward_applied'],
            'joined_at'     => $this->resource['joined_at'],
            'validated_at'  => $this->resource['validated_at'],
        ];
    }
}
