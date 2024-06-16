<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class BookResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id'            => $this->id,
            'title'         => $this->title,
            'type'          => $this->type,
            'category'      => new CategoryResource($this->whenLoaded('category')),
            'manufacturer'  => new ManufacturerResource($this->whenLoaded('manufacturer')),
            'summary'       => $this->summary,
            'price'         => $this->price,
            'in_stock'      => $this->in_stock,
            'photo'         => new PhotoResource($this->whenLoaded('photo')),
            'created_at'    => $this->created_at->format('d.m.Y H:i:s'),
            'updated_at'    => $this->updated_at ? $this->updated_at->format('d.m.Y H:i:s') : null,
        ];
    }
}
