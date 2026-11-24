<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StageSixPwaAndSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_pwa_assets_are_available(): void
    {
        $this->get('/manifest.json')
            ->assertOk()
            ->assertJsonFragment(['short_name' => 'UjianKu']);

        $this->get('/sw.js')->assertOk();
        $this->get('/offline.html')->assertOk();
        $this->get('/icons/icon-192.png')->assertOk();
    }

    public function test_security_headers_are_present(): void
    {
        $response = $this->get('/');

        $response->assertHeader('X-Frame-Options', 'SAMEORIGIN');
        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
    }
}
