<?php

namespace Tests;

use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Testing\TestResponse;
use PHPUnit\Framework\Assert as PHPUnit;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;
    use RefreshDatabase;

    /**
     * Indicates whether the default seeder should run before each test.
     *
     * @var bool
     */
    protected $seed = true;

    public static function assertStatus(TestResponse $response, $status)
    {
        $actual = $response->getStatusCode();
        if ($response->baseResponse instanceof StreamedResponse) {
            $content = 'stream response';
        } elseif ($response->baseResponse instanceof BinaryFileResponse) {
            $content = 'binary content';
        } else {
            $content = $response->content();
        }
        if (strlen($content) > 200) {
            // limit content to speed up tests
            $content = substr($content, 0, 500).'...';
        }
        PHPUnit::assertTrue(
            $actual === $status,
            "Expected status code {$status} but received {$actual}. Content: ".$content
        );
    }
}
