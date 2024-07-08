<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\User;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api', name: 'api_')]
class RegistrationController extends AbstractController
{
    #[Route('/register', name: 'register', methods: ['POST'])]
    public function index(
        ManagerRegistry $doctrine,
        Request $request,
        UserPasswordHasherInterface $passwordHasher,
        ValidatorInterface $validator
    ): JsonResponse {
        $em = $doctrine->getManager();

        $data = json_decode($request->getContent(), true);

        $email = $data['email'] ?? null;
        $username = $data['username'] ?? null;

        $role = $data['role'] ?? null;
        $plaintextPassword = $data['password'] ?? null;


        $user = new User();
        $user->setEmail($email);
        $user->setUsername($username);
        $user->setRoles([$role]);

        $hashedPassword = $passwordHasher->hashPassword($user, $plaintextPassword);
        $user->setPassword($hashedPassword);

        $errors = $validator->validate($user);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[$error->getPropertyPath()][] = $error->getMessage();
            }

            return $this->json([
                'success' => false,
                'message' => $errorMessages,
            ], 400);
        }

        $em->persist($user);
        $em->flush();

        return $this->json(['success' => true, 'message' => 'Registered successfully'], 201);
    }
}
