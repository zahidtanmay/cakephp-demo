<?php

namespace App\Traits;

use Firebase\JWT\JWT;

trait JwtToken
{
    protected function jwt($id) {
        $payload = [
            'iss' => "cake-jwt",
            'sub' => $id,
            'iat' => time(),
            'exp' => time() + 24 * 7 * 60*60
        ];

        return JWT::encode($payload, env('JWT_SECRET'));
    }

}