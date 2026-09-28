<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProjectResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'name'        => $this->name,
            'description' => $this->description,
            'status'      => $this->status,
            'archived_at' => $this->archived_at?->toIso8601String(),
            'creator'     => $this->whenLoaded('creator', fn () => [
                'id'   => $this->creator->id,
                'name' => $this->creator->name,
            ]),
            'members'     => $this->whenLoaded('members', fn () =>
                $this->members->map(fn ($u) => [
                    'id'           => $u->id,
                    'name'         => $u->name,
                    'email'        => $u->email,
                    'project_role' => $u->pivot->project_role,
                ])
            ),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}