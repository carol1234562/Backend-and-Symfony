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

    // Validación de inicio de sesión
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

        if ($user && ($user->getPassword() === $password || password_verify($password, $user->getPassword()))) {
            return $this->json(['success' => true, 'login' => true, 'user' => $user], 200);
        }

        return $this->json([
            'error' => 'Invalid credentials',
            'success' => false,
            'login' => false
        ], 401);
    }
}
