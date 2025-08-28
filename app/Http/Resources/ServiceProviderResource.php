<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ServiceProviderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
                'id' => $this->id,
                'name' => $this->name,
                'email' => $this->email,
                'category' => $this->category->name,
                'description' => $this->description,
                'tax_id' => $this->tax_id,
                'address' => new AddressResource($this->address),
                'license' => $this->license,
                'contact_phone' => $this->contact_phone,
                'image_url' => $this->image_url,
                'created_at' => $this->created_at,
                'updated_at' => $this->updated_at
        ];
    }
}
