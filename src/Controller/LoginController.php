<?php

namespace App\Controller;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/nurse')]
class LoginController extends AbstractController
{
    public function __construct(private UserRepository $userRepository)
    {
    }

    // 1. Obtener todas las enfermeras
    #[Route('/index', name: 'nurse_index', methods: ['GET'])]
    public function index(): JsonResponse
    {
        $nurses = $this->userRepository->findAll();
        return $this->json($nurses, 200);
    }

    // 2. Validación de inicio de sesión
    #[Route('/login', name: 'app_nurse_login', methods: ['POST'])]
    public function login(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        if (!is_array($data)) {
            $data = $request->request->all();
        }

        $email = $data['email'] ?? '';
        $password = $data['password'] ?? '';

        if (empty($email) || empty($password)) {
            return $this->json([
                'error' => 'Missing email or password',
                'success' => false,
                'login' => false
            ], 400);
        }

        $user = $this->userRepository->findOneBy(['email' => $email]);

        if ($user && $user->getPassword() === $password) {
            return $this->json(['success' => true, 'login' => true, 'user' => $user], 200);
        }

        return $this->json([
            'error' => 'Invalid credentials',
            'success' => false,
            'login' => false
        ], 401);
    }

    // 3. Buscar enfermera por nombre
    #[Route('/name/{name}', name: 'nurse_by_name', methods: ['GET'])]
    public function findByName(string $name): JsonResponse
    {
        // Busca enfermeras cuyo nombre coincida (puedes ajustar el repositorio si prefieres una búsqueda parcial con LIKE)
        $nurses = $this->userRepository->findBy(['name' => $name]);

        if (empty($nurses)) {
            return $this->json(['message' => 'No se encontraron enfermeras con ese nombre'], 404);
        }

        return $this->json($nurses, 200);
    }
}