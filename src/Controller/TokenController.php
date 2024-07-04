<?php

namespace App\Controller;

use App\Entity\Token;
use App\Repository\TokenRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
#[Route('/api/token', name: 'api_token_')]
class TokenController extends AbstractController
{
    #[Route('/token', name: 'app_token')]
    public function index(): Response
    {
        return $this->render('token/index.html.twig', [
            'controller_name' => 'TokenController',
        ]);
    }
    #[Route('/create', name: 'create', methods: ['POST'])]
    public function createToken(EntityManagerInterface $entityManager, TokenRepository $tokenRepository): JsonResponse
    {
        // Example: Create a new token for a user
        $user = $this->getUser(); // Retrieve authenticated user

        // Create a new token entity
        $token = new Token();
        $token->setTokenValue('generated_token_value_here');
        $token->setExpired(false);
        $token->setUser($user);

        // Persist and flush token to database
        $entityManager->persist($token);
        $entityManager->flush();

        return $this->json(['message' => 'Token created successfully']);
    }

    #[Route('/user/{id}', name: 'user_tokens', methods: ['GET'])]
    public function getUserTokens(TokenRepository $tokenRepository, int $id): JsonResponse
    {
        // Example: Retrieve all tokens for a specific user
        $tokens = $tokenRepository->findActiveTokensByUser($id);

        // Return tokens as JSON response
        return $this->json(['tokens' => $tokens]);
    }
}
