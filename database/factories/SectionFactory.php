<?php

namespace Database\Factories;

use App\Models\Chapter;
use App\Models\Section;
use Illuminate\Database\Eloquent\Factories\Factory;

class SectionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $chapter = Chapter::factory()->create();

        return [
            'headline'    => $chapter->name,
            'description' => $chapter->name,
            'slug_name'   => $chapter->slug_name,
            'sort'        => 0,
            'chapter_id'  => $chapter->id,
        ];
    }
}
