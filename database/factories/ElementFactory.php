<?php

use App\Models\Answer;
use App\Models\Chapter;
use App\Models\Element;
use App\Models\Section;
use App\Models\Text;
use Faker\Generator as Faker;
use Illuminate\Support\Str;

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| This directory should contain each of the model factory definitions for
| your application. Factories provide a convenient way to generate new
| model instances for testing / seeding your application's database.
|
*/

/* @var \Illuminate\Database\Eloquent\Factory $factory */
$factory->define(Chapter::class, function (Faker $faker) {
});

$factory->define(Section::class, function (Faker $faker) {
});

$factory->define(Element::class, function (Faker $faker) {
    $text = factory(Text::class)->create(
        ['value'       => $faker->text(150)]
    );
    $chapter = Chapter::create([
        'name'            => $text->key,
        'print_name'      => $text->key,
        'slug_name'       => Str::slug($text->value),
        'sort'            => 0,
    ]);
    $section = Section::create([
        'headline'    => $text->key,
        'description' => $text->key,
        'slug_name'   => Str::slug($text->value),
        'sort'        => 0,
        'chapter_id'  => $chapter->id,
    ]);

    return [
        'section_id' => $section->id,
        'type'       => 'text',

        'content' => $text->key,
        'sort'    => 0,

//        // choice type values:
//        'choice_type_id',
//
//        // slider values:
//        'steps',
//        'min',
//        'max',
    ];
});

$factory->define(Answer::class, function (Faker $faker) {
    $element = factory(Element::class)->create();

    return [
        'element_id' => $element,
        'value'      => $faker->text,
    ];
});
