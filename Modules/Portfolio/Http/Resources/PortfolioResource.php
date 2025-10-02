<?php

namespace Modules\Portfolio\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PortfolioResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'title' => $this->title,
            'bio' => $this->bio,
            'avatar' => $this->avatar,
            'website' => $this->website,
            'linkedin' => $this->linkedin,
            'github' => $this->github,
            'skills' => SkillResource::collection($this->whenLoaded('skills')),
            'experiences' => ExperienceResource::collection($this->whenLoaded('experiences')),
            'educations' => EducationResource::collection($this->whenLoaded('educations')),
            'projects' => ProjectResource::collection($this->whenLoaded('projects')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
