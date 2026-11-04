<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class RequestForgeryProtectionTest extends TestCase
{
    public function test_cross_site_post_without_a_token_is_rejected(): void
    {
        $this->app['env'] = 'production';
        Route::middleware('web')->post('/test-request-forgery', fn () => response('ok'));

        $this->withHeader('Sec-Fetch-Site', 'cross-site')
            ->post('/test-request-forgery')
            ->assertStatus(419);
    }

    public function test_same_origin_post_is_allowed(): void
    {
        $this->app['env'] = 'production';
        Route::middleware('web')->post('/test-request-forgery', fn () => response('ok'));

        $this->withHeader('Sec-Fetch-Site', 'same-origin')
            ->post('/test-request-forgery')
            ->assertOk();
    }

    public function test_cross_site_post_with_a_valid_token_is_allowed(): void
    {
        $this->app['env'] = 'production';
        Route::middleware('web')->post('/test-request-forgery', fn () => response('ok'));

        $this->withSession(['_token' => 'test-token'])
            ->withHeader('Sec-Fetch-Site', 'cross-site')
            ->post('/test-request-forgery', ['_token' => 'test-token'])
            ->assertOk();
    }
}
