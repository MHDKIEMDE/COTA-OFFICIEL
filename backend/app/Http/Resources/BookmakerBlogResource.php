<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BookmakerBlogResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                => $this->id,
            'bookmaker_id'      => $this->bookmaker_id,
            'promo_code'        => $this->promo_code,
            'bonus_title'       => $this->bonus_title,
            'bonus_description' => $this->bonus_description,
            'steps'             => $this->steps ?? [],
            'cta_label'         => $this->cta_label,
            'bookmaker'         => $this->whenLoaded('bookmaker', fn() => [
                'id'            => $this->bookmaker->id,
                'name'          => $this->bookmaker->name,
                'slug'          => $this->bookmaker->slug,
                'primary_color' => $this->bookmaker->primary_color,
                'logo_url'      => $this->bookmaker->logo_url,
                'affiliate_link'=> $this->bookmaker->affiliate_link,
            ]),
        ];
    }
}
