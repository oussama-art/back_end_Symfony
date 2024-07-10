<?php

namespace App\Service;

use App\Entity\Riad;
use App\Entity\Room;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class RiadService
{
    private EntityManagerInterface $entityManager;
    private ValidatorInterface $validator;
    private string $uploadDirectory;

    public function __construct(EntityManagerInterface $entityManager, ValidatorInterface $validator, string $uploadDirectory)
    {
        $this->entityManager = $entityManager;
        $this->validator = $validator;
        $this->uploadDirectory = $uploadDirectory;
    }

    public function addRiad(Request $request): Response
    {
        $data = json_decode($request->getContent(), true);

        // Handle Riad data
        $riad = new Riad();
        $riad->setName($data['name']);
        $riad->setDescription($data['description']);
        $riad->setAddress($data['address']);

        // Handle image file upload
        $file = $request->files->get('imageFile');
        if ($file) {
            $fileName = md5(uniqid()) . '.' . $file->guessExtension();

            try {
                $file->move($this->uploadDirectory, $fileName);
                $riad->setImagefile($file);
                $riad->setImageName($fileName);
            } catch (FileException $e) {
                return new JsonResponse(['error' => 'Failed to upload image'], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        }

        // Handle Rooms
        if (isset($data['rooms']) && is_array($data['rooms'])) {
            foreach ($data['rooms'] as $roomData) {
                $room = new Room();
                $room->setName($roomData['name']);
                // Assuming addRoom method adds room to Riad entity
                $riad->addRoom($room);
                $this->entityManager->persist($room);
            }
        }

        // Validate Riad entity
        $errors = $this->validator->validate($riad);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[] = $error->getMessage();
            }

            return new JsonResponse(['errors' => $errorMessages], Response::HTTP_BAD_REQUEST);
        }

        // Persist Riad entity to database
        $this->entityManager->persist($riad);
        $this->entityManager->flush();

        return new JsonResponse(['status' => 'Riad and rooms created successfully!'], Response::HTTP_CREATED);
    }

    public function deleteRiad(int $id): array
    {
        $riad = $this->entityManager->getRepository(Riad::class)->find($id);

        if (!$riad) {
            return ['error' => 'Riad not found'];
        }

        foreach ($riad->getRooms() as $room) {
            $this->entityManager->remove($room);
        }

        $this->entityManager->remove($riad);
        $this->entityManager->flush();

        return ['status' => 'Riad deleted successfully!'];
    }

    public function updateRiad(int $id, array $data, $file): array
    {
        $riad = $this->entityManager->getRepository(Riad::class)->find($id);

        if (!$riad) {
            return ['error' => 'Riad not found'];
        }

        $riad->setName($data['name']);
        $riad->setDescription($data['description']);
        $riad->setAddress($data['address']);

        // Handle city update if applicable
        if (isset($data['city'])) {
            $riad->setCity($data['city']);
        }

        // Handle image file update
        if ($file) {
            $fileName = md5(uniqid()) . '.' . $file->guessExtension();

            try {
                $file->move($this->uploadDirectory, $fileName);
                $riad->setImageFile($file);
                $riad->setImageName($fileName);
            } catch (FileException $e) {
                return ['error' => 'Failed to upload image'];
            }
        }


        $errors = $this->validator->validate($riad);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[] = $error->getMessage();
            }

            return ['errors' => $errorMessages];
        }

        $this->entityManager->flush();

        return ['status' => 'Riad updated successfully!'];
    }

    public function getRiad(int $id): array
    {
        $riad = $this->entityManager->getRepository(Riad::class)->find($id);

        if (!$riad) {
            return ['error' => 'Riad not found'];
        }

        $response = [
            'id' => $riad->getId(),
            'name' => $riad->getName(),
            'description' => $riad->getDescription(),
            'address' => $riad->getAddress(),
            'imagefile' => $riad->getImagefile(),
            'city' => $riad->getCity(),
            'rooms' => array_map(function ($room) {
                return [
                    'id' => $room->getId(),
                    'name' => $room->getName(),
                    'description' => $room->getDescription(),
                    'price' => $room->getPrice(),
                    'nbPersonne' => $room->getNbPersonne(),
                ];
            }, $riad->getRooms()->toArray())
        ];

        return $response;
    }
}
