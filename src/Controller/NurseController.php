<?php

namespace App\Controller;

use App\Service\NurseProvider;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

class NurseController extends AbstractController
{
    public function __construct(private NurseProvider $nurseProvider)
    {
    }

    #[Route('/nurse/index', name: 'app_nurse_index', methods: ['GET'])]
    public function index(): JsonResponse
    {
        $nurses = $this->nurseProvider->getAll();
        return $this->json($nurses, 200);
    }

    #[Route('/nurse/name/{nombre}', name: 'app_nurse_find_by_name', methods: ['GET'])]
    public function findByName(string $nombre): JsonResponse
    {
        $nurses = $this->nurseProvider->getAll();

        foreach ($nurses as $nurse) {
            if (isset($nurse['name']) && strtolower($nurse['name']) === strtolower($nombre)) {
                return $this->json($nurse, 200);
            }
        }

        return $this->json(['error' => 'Nurse not found'], 404);
    }
}