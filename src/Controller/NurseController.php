<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpKernel\KernelInterface;

class NurseController extends AbstractController
{
    private KernelInterface $appKernel;

    public function __construct(KernelInterface $appKernel)
    {
        $this->appKernel = $appKernel;
    }

    private function getNursesData(): array
    {
        $filePath = $this->appKernel->getProjectDir() . '/public/nurses.json';

        if (!file_exists($filePath)) {
            return [];
        }

        $jsonData = file_get_contents($filePath);
        $data = json_decode($jsonData, true);

        return $data['nurses'] ?? [];
    }

    #[Route('/nurse/index', name: 'app_nurse_index', methods: ['GET'])]
    public function index(): JsonResponse
    {
        $nurses = $this->getNursesData();
        return $this->json($nurses, 200);
    }

    #[Route('/nurse/name/{nombre}', name: 'app_nurse_find_by_name', methods: ['GET'])]
    public function findByName(string $nombre): JsonResponse
    {
        $nurses = $this->getNursesData();

        foreach ($nurses as $nurse) {
            if (isset($nurse['name']) && strtolower($nurse['name']) === strtolower($nombre)) {
                return $this->json($nurse, 200);
            }
        }

        return $this->json(['error' => 'Nurse not found'], 404);
    }
}