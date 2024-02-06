<?php

namespace Smadi0x86wsl\Backend\Utils;

use OpenSwoole\Http\Response;

class HttpUtils {
    public static function setCORSHeaders(Response $response, $allowedOrigin = '*') {
        $response->header("Access-Control-Allow-Origin", $allowedOrigin);
        $response->header("Access-Control-Allow-Methods", "GET, POST, OPTIONS");
        $response->header("Access-Control-Allow-Headers", "Origin, X-Requested-With, Content-Type, Accept, Authorization");
        $response->header("Access-Control-Allow-Credentials", "true");
    }
}
