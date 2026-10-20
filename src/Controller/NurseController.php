<?php

namespace App\Controller;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

class NurseController extends AbstractController
{
    public function __construct(private UserRepository $userRepository)
    {
    }

    #[Route('/nurse/index', name: 'app_nurse_index', methods: ['GET'])]
    public function index(): JsonResponse
    {
        $nurses = $this->userRepository->findAll();
        return $this->json($nurses, 200);
    }

    #[Route('/nurse/name/{name}', name: 'app_nurse_find_by_name', methods: ['GET'])]
    public function findByName(string $name): JsonResponse
    {
        $nurse = $this->userRepository->findOneByName($name);

        if (!$nurse) {
            return $this->json(['error' => 'Nurse not found'], 404);
        }

        return $this->json($nurse, 200);
    }
}