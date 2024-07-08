<?php

namespace App\Controller;

use App\Entity\Riad;
use App\Entity\Room;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api', name: 'api_')]
class RiadController extends AbstractController
{
    #[Route('/addriad', name: 'add_riad', methods: ['POST'])]
    public function addRiad(ManagerRegistry $doctrine, Request $request, ValidatorInterface $validator): Response
    {
        $entityManager = $doctrine->getManager();
        $data = json_decode($request->getContent(), true);

        if ($data === null) {
            return $this->json(['error' => 'Invalid JSON data'], Response::HTTP_BAD_REQUEST);
        }

        // Handle Riad data
        $riad = new Riad();
        $riad->setName($data['name'] ?? null);
        $riad->setDescription($data['description'] ?? null);
        $riad->setAddress($data['address'] ?? null);

        // Handle image file upload
        $file = $request->files->get('imageFile');
        if ($file) {
            $uploadDirectory = $this->getParameter('upload_directory');
            $fileName = md5(uniqid()) . '.' . $file->guessExtension();

            try {
                $file->move($uploadDirectory, $fileName);
                $riad->setImagefile($fileName);
            } catch (FileException $e) {
                return new Response('Failed to upload image', Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        } else {
            // Provide a default value if no image file is uploaded
            $riad->setImagefile('default_image.jpg'); // Change this to an actual default image if needed
        }

        // Validate Riad entity
        $errors = $validator->validate($riad);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[] = $error->getMessage();
            }

            return $this->json(['errors' => $errorMessages], Response::HTTP_BAD_REQUEST);
        }

        // Add rooms to Riad and persist each Room entity
        if (isset($data['rooms']) && is_array($data['rooms'])) {
            foreach ($data['rooms'] as $roomData) {
                $room = new Room();
                $room->setName($roomData['name'] ?? null);
                $room->setDescription($roomData['description'] ?? null);
                $room->setNbPersonne($roomData['nb_personne'] ?? null);
                $room->setPrice($roomData['price'] ?? null);

                $riad->addRoom($room);
                $entityManager->persist($room); // Explicitly persist each Room entity
            }
        }

        // Persist Riad entity to database
        $entityManager->persist($riad);
        $entityManager->flush();

        return $this->json(['status' => 'Riad and rooms created successfully!'], Response::HTTP_CREATED);
    }

    #[Route('/riad/{id}', name: 'delete_riad', methods: ['DELETE'])]
    public function deleteRiad(ManagerRegistry $doctrine, int $id): Response
    {
        $entityManager = $doctrine->getManager();
        $riad = $entityManager->getRepository(Riad::class)->find($id);

        if (!$riad) {
            return $this->json(['error' => 'Riad not found'], Response::HTTP_NOT_FOUND);
        }

        // Delete associated rooms (optional, depending on your business logic)
        foreach ($riad->getRooms() as $room) {
            $entityManager->remove($room);
        }

        $entityManager->remove($riad);
        $entityManager->flush();

        return $this->json(['status' => 'Riad deleted successfully!'], Response::HTTP_OK);
    }

    #[Route('/riad/{id}', name: 'update_riad', methods: ['PUT'])]
    public function updateRiad(ManagerRegistry $doctrine, Request $request, int $id, ValidatorInterface $validator): Response
    {
        $entityManager = $doctrine->getManager();
        $riad = $entityManager->getRepository(Riad::class)->find($id);

        if (!$riad) {
            return $this->json(['error' => 'Riad not found'], Response::HTTP_NOT_FOUND);
        }

        // Update Riad entity with request data
        $data = json_decode($request->getContent(), true);

        $riad->setName($data['name']);
        $riad->setDescription($data['description']);
        $riad->setAddress($data['address']);

        // Handle image file upload if provided
        $file = $request->files->get('imageFile');
        if ($file) {
            $uploadDirectory = $this->getParameter('upload_directory');
            $fileName = md5(uniqid()) . '.' . $file->guessExtension();

            try {
                $file->move($uploadDirectory, $fileName);
                $riad->setImagefile($fileName);
            } catch (FileException $e) {
                return new Response('Failed to upload image', Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        }

        // Validate updated entity data
        $errors = $validator->validate($riad);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[] = $error->getMessage();
            }

            return $this->json(['errors' => $errorMessages], Response::HTTP_BAD_REQUEST);
        }

        // Persist updated entity to database
        $entityManager->flush();

        return $this->json(['status' => 'Riad updated successfully!'], Response::HTTP_OK);
    }
    #[Route('/riad/{id}', name: 'get_riad', methods: ['GET'])]
    public function getRiad(ManagerRegistry $doctrine, int $id): Response
    {
        $entityManager = $doctrine->getManager();
        $riad = $entityManager->getRepository(Riad::class)->find($id);

        if (!$riad) {
            return $this->json(['error' => 'Riad not found'], Response::HTTP_NOT_FOUND);
        }

        // Serialize the Riad entity to JSON, including rooms
        $response = [
            'id' => $riad->getId(),
            'name' => $riad->getName(),
            'description' => $riad->getDescription(),
            'address' => $riad->getAddress(),
            'imagefile' => $riad->getImagefile(),
            'rooms' => array_map(function($room) {
                return [
                    'id' => $room->getId(),
                    'name' => $room->getName(),
                    'description' => $room->getDescription(),
                    'price' => $room->getPrice(),
                    'nbPersonne' => $room->getNbPersonne()
                ];
            }, $riad->getRooms()->toArray())
        ];

        return $this->json($response, Response::HTTP_OK);
    }
}
