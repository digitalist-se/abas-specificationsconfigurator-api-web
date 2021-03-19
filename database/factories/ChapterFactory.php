<?php

namespace Database\Factories;

use App\Models\Chapter;
use App\Models\Text;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ChapterFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Chapter::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $text = Text::factory()->create(['value' => $this->faker->text(150)]);

        return [
            'name'            => $text->key,
            'print_name'      => $text->key,
            'slug_name'       => Str::slug($text->value),
            'sort'            => 0,
        ];
    }
}
