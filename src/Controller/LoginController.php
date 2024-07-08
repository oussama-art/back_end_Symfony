<?php
namespace App\Controller;

use App\Entity\Token;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Validator\Validator\ValidatorInterface;


#[Route('/api', name: 'api_')]
class LoginController extends AbstractController
{
    #[Route('/login', name: 'login', methods: 'POST')]
    public function login(
        Request $request,
        UserPasswordHasherInterface $passwordEncoder,
        ManagerRegistry $doctrine,
        JWTTokenManagerInterface $JWTManager,
        EntityManagerInterface $entityManager,
        ValidatorInterface $validator
    ): JsonResponse {

        $credentials = json_decode($request->getContent(), true);
        $email = $credentials['email'] ?? null;

        $user = $doctrine->getRepository(User::class)->findOneBy(['email' => $email]);

        if (!$user) {
            return $this->json(['message' => 'User not found'], 404);
        }

        $errors = $validator->validate($user);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[$error->getPropertyPath()][] = $error->getMessage();
            }

            return $this->json([
                'success' => false,
                'errors' => $errorMessages,
            ], 400);
        }

        // Generate JWT token
        $token = $JWTManager->create($user);

        $token_obj = new Token();
        $token_obj->setUser($user);
        $token_obj->setTokenValue($token);
        $token_obj->setExpired(false);

        $entityManager->persist($token_obj);
        $entityManager->flush();

        return $this->json(['token' => $token]);
    }
    #[Route('/logout', name: 'logout', methods: 'POST')]
    public function logout(EntityManagerInterface $entityManager,Request $request, ManagerRegistry $doctrine): JsonResponse
    {
        // Extract data from request
        $data = json_decode($request->getContent(), true);
        $token = $data['token'] ?? null;

        if (!$token) {
            return $this->json(['message' => 'Missing token'], 400);
        }

        // Find the token entity
        $tokenRepository = $doctrine->getRepository(Token::class);
        $tokenEntity = $tokenRepository->findOneBy(['tokenValue' => ['token' => $token], 'expired' => false]);


        $tokenEntity->setExpired(true);
        $entityManager->persist($tokenEntity);
        $em = $doctrine->getManager();
        $em->flush();

        return $this->json(['message' => 'Logged out successfully']);
    }
}
