<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource {

    /**
     * The flag indicating whether the resource is a single item.
     *
     * @var bool
     */
    private $isSingle;

    /**
     * Create a new resource instance.
     *
     * @param  mixed  $resource
     * @param  bool  $isSingle
     * @return void
     */
    public function __construct($resource, $isSingle = false) {
        $this->isSingle = $isSingle;
        parent::__construct($resource);
    }

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array {
        $roles = $this->roles->map(function ($role) {
            return [
                'id' => $role->id,
                'name' => $role->name,
            ];
        });

        $data = [
            'id' => $this->id,
            'name' => "{$this->first_name} {$this->last_name}",
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'phone' => $this->phone,
            'country_id' => $this->country_id,
            'roles' => $roles,
            'customer_id' => $this->customer?->id,
            'provider_id' => $this->provider?->id,
            'created_at' => $this->created_at,
        ];

        if ($this->isSingle) {
            $data['permissions'] = count($roles) > 0 ? $this->roles[0]->permissions : $this->permissions;
        }

        return $data;
    }
}
