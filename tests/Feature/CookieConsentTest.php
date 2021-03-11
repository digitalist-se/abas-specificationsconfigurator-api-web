<?php

namespace Tests\Feature;

use Tests\TestCase;

class CookieConsentTest extends TestCase
{
    public function test_get_config()
    {
        $response = $this->getJson('api/cookieconsent');
        static::assertStatus($response, 200);
        $response->assertJsonStructure([
            'palette' => [
                'popup' => [
                    'background',
                ],
                'button' => [
                    'background',
                ],
            ],
            'position',
            'cookie'   => [
                'domain',
            ],
            'content' => [
                'message',
                'dismiss',
                'link',
                'href',
            ],
        ]);
    }
}
