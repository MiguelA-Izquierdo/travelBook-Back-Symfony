<?php
namespace App\Service\User;

use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;


use App\Exceptions\HttpError;
use Error;

class AuthService 
{
    private $jwtManager;

    public function __construct(JWTTokenManagerInterface $jwtManager)
    {
        $this->jwtManager = $jwtManager;
    }

    public function createJWT($user)
    {
        // Crea un token JWT utilizando el servicio JWTTokenManager
        return $this->jwtManager->create($user);
    }

    public function verifyJWTAndGetPayload($token)
    {
        try {
            // Verifica y decodifica el token JWT
            $payload = $this->jwtManager->decode($token);
            if (is_string($payload)) {
                throw new \Exception();
            }

            return $payload;
        } catch (\Exception $error) {
            throw new Error();
        }
    }
}
