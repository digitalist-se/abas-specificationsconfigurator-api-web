<?php

namespace Database\Factories;

use App\Models\Element;
use App\Models\Section;
use Illuminate\Database\Eloquent\Factories\Factory;

class ElementFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $section = Section::factory()->create();

        return [
            'section_id' => $section->id,
            'type'       => 'text',
            'content'    => $section->headline,
            'sort'       => 0,
            //        // choice type values:
            //        'choice_type_id',
            //
            //        // slider values:
            //        'steps',
            //        'min',
            //        'max',
        ];
    }
}
