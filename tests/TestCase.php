<?php

namespace Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Foundation\Testing\TestResponse;
use PHPUnit\Framework\Assert as PHPUnit;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication, RefreshDatabase;

    protected function setUp()
    {
        parent::setUp();

        // Seed Database
        $this->seed(\DatabaseSeeder::class);
    }

    public function assertStatus(TestResponse $response, $status)
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
