<?php

use Smadi0x86wsl\Backend\Utils\HttpUtils;
use OpenSwoole\Http\Response;

it('sets CORS headers correctly', function () {
    $response = mock(Response::class);

    $response->shouldReceive('header')
             ->with('Access-Control-Allow-Origin', '*')
             ->once()
             ->shouldReceive('header')
             ->with('Access-Control-Allow-Methods', 'GET, POST, OPTIONS')
             ->once()
             ->shouldReceive('header')
             ->with('Access-Control-Allow-Headers', 'Origin, X-Requested-With, Content-Type, Accept, Authorization')
             ->once()
             ->shouldReceive('header')
             ->with('Access-Control-Allow-Credentials', 'true')
             ->once();

    HttpUtils::setCORSHeaders($response);
});

