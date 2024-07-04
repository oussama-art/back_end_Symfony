<?php

namespace App\EventListener;

use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTDecodedEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTInvalidEvent;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

class JWTTokenValidationListener
{
    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function onJWTInvalid(JWTInvalidEvent $event)
    {
        $response = new JsonResponse(['message' => 'Invalid Token'], 401);
        $event->setResponse($response);
    }

    public function onJWTDecoded(JWTDecodedEvent $event)
    {
        $payload = $event->getPayload();

        // Log the payload
        $this->logger->info('JWT Payload:', $payload);

        // Custom validation logic for your token payload
        if (!isset($payload['username'])) { // Ensure your payload includes 'username'
            $event->markAsInvalid();
        }
    }
}
