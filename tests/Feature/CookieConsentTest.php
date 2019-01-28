<?php

namespace Tests\Feature;

use Tests\TestCase;

class CookieConsentTest extends TestCase
{
    public function testGetConfig()
    {
        $response = $this->getJson('api/cookieconsent');
        $this->assertStatus($response, 200);
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
