<?php

namespace App\Controller;

use App\Entity\Room;
use Doctrine\Persistence\ManagerRegistry;
use http\Env\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api', name: 'api_')]
class RoomController extends AbstractController
{
    #[Route('/addroom', name: 'app_room')]
    public function addRoom(Request $request,ManagerRegistry $doctrine ):Response{
      $data = json_decode($request->getContent(), true);
      $entityManager = $doctrine->getManager();

      $room = new Room();
      $room->setName($data['name']);
      $room->setDescription($data['description']);
      $room->setPrice($data['price']);
      $room->setNbPersonne($data['nbPersonne']);

      $entityManager->persist($room);
      $entityManager->flush();

      return new Response(json_encode(['message'=>'room added successfully!']));
    }
}
