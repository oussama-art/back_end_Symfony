<?php

namespace App\Service;

use App\Entity\Token;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class LoginService
{
    private UserPasswordHasherInterface $passwordEncoder;
    private ManagerRegistry $doctrine;
    private JWTTokenManagerInterface $JWTManager;
    private EntityManagerInterface $entityManager;
    private ValidatorInterface $validator;

    public function __construct(
        UserPasswordHasherInterface $passwordEncoder,
        ManagerRegistry $doctrine,
        JWTTokenManagerInterface $JWTManager,
        EntityManagerInterface $entityManager,
        ValidatorInterface $validator
    ) {
        $this->passwordEncoder = $passwordEncoder;
        $this->doctrine = $doctrine;
        $this->JWTManager = $JWTManager;
        $this->entityManager = $entityManager;
        $this->validator = $validator;
    }

    public function login(array $credentials): JsonResponse
    {
        $email = $credentials['email'] ?? null;
        $password = $credentials['password'] ?? null;

        $user = $this->doctrine->getRepository(User::class)->findOneBy(['email' => $email]);

        if (!$user) {
            return new JsonResponse(['message' => 'User not found'], 404);
        }

        if (!$this->passwordEncoder->isPasswordValid($user, $password)) {
            return new JsonResponse(['message' => 'Password Incorrect'], 401);
        }

        $errors = $this->validator->validate($user);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[$error->getPropertyPath()][] = $error->getMessage();
            }

            return new JsonResponse(['success' => false, 'errors' => $errorMessages], 400);
        }

        $token = $this->JWTManager->create($user);

        $tokenEntity = new Token();
        $tokenEntity->setUser($user);
        $tokenEntity->setTokenValue($token);
        $tokenEntity->setExpired(false);

        $this->entityManager->persist($tokenEntity);
        $this->entityManager->flush();

        return new JsonResponse(['token' => $token]);
    }

    public function logout(string $tokenValue): JsonResponse
    {
        $tokenRepository = $this->doctrine->getRepository(Token::class);
        $tokenEntity = $tokenRepository->findOneBy(['tokenValue' => $tokenValue]);

        if (!$tokenEntity) {
            return new JsonResponse(['message' => 'Invalid token'], 400);
        }

        $tokenEntity->setExpired(true);
        $this->entityManager->persist($tokenEntity);
        $this->entityManager->flush();

        return new JsonResponse(['message' => 'Logged out successfully']);
    }
}
