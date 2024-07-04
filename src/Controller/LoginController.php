<?php

namespace App\Controller;

use App\Entity\User;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Doctrine\Persistence\ManagerRegistry; // Import ManagerRegistry


#[Route('/api', name: 'api_')]
class LoginController extends AbstractController
{
    #[Route('/login', name: 'login', methods: 'POST')]
    public function login(Request $request, UserPasswordHasherInterface $passwordEncoder,ManagerRegistry $doctrine, JWTTokenManagerInterface $JWTManager): JsonResponse
    {
        // Extract data from request
        $credentials = json_decode($request->getContent(), true);
        $email = $credentials['email'] ?? null;
        $password = $credentials['password'] ?? null;

        if (!$email || !$password) {
            return $this->json(['message' => 'Missing email or password'], 400);
        }

        // Find the user by email
        $user = $doctrine->getRepository(   User::class)->findOneBy(['email' => $email]);

        if (!$user) {
            return $this->json(['message' => 'User not found'], 404);
        }

        // Check if the provided password matches the stored password
        if (!$passwordEncoder->isPasswordValid($user, $password)) {
            return $this->json(['message' => 'Password incorrect']);
        }

        // Perform any additional logic here (e.g., generating JWT token)

//        return $this->json(['message' => 'Login successful']);
        $token = $JWTManager->create($user);

        // Return the JWT token in the response
        return $this->json(['token' => $token]);
    }
}
