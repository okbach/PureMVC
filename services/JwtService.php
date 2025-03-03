<?php

namespace App\Services;

use Firebase\JWT\JWT;

class JwtService
{
    private $secretKey;

    public function __construct(string $secretKey)
    {
        $this->secretKey = $secretKey;
    }

    public function generateToken(array $data, int $expiry = 3600): string
    {

        $payload = [
            'iss' => 'auth', 
            'aud' => 'wadiea.com', 
            'iat' => time(), 
            'exp' => time() + $expiry,
            'data' => $data
        ];  

        return JWT::encode($payload, $this->secretKey, 'HS256');
    }

    public function decodeToken(string $token): ?object
    {
        try {
            return JWT::decode($token, $this->secretKey, ['HS256']);
        } catch (\Exception $e) {
            return null;
        }
    }
}




?>