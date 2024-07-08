<?php

namespace App\Controller;

use App\Repository\TokenRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/token', name: 'api_token_')]
class TokenController extends AbstractController
{
    #[Route('/check', name: 'token-expired', methods: ['GET'])]
    public function getTokens(Request $request, TokenRepository $tokenRepository): JsonResponse
    {
        $tokenValue = $request->headers->get('Authorization');

        if (!$tokenValue) {
            return $this->json(['message' => 'Token not provided'], 400);
        }

        // Remove 'Bearer ' prefix if present
        if (str_starts_with($tokenValue, 'Bearer ')) {
            $tokenValue = substr($tokenValue, 7);
        }

        $token = $tokenRepository->findOneBy(['tokenValue' => $tokenValue]);

        if (!$token) {
            return $this->json(['message' => 'Token not found'], 404);
        }

        if ($token->isExpired()) {
            return $this->json(['message' => 'Token is expired'], 401);
        }

        $user = $token->getUser();

        return $this->json([
            'message' => 'Token is Valid',
            'user' => [
                'id' => $user->getId(),
                'username' => $user->getUsername(),
                'email' => $user->getEmail(),
            ]
        ]);
    }
}
