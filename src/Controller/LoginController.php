<?php
namespace App\Controller;

use App\Service\NurseProvider;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

class LoginController extends AbstractController {
    public function __construct(private NurseProvider $nurseProvider) {
    }

#[Route('/nurse/login', name: 'app_nurse_login', methods: ['POST'])]
    public function login(Request $request): JsonResponse {
        // Decodificamos el JSON que llega en el body de la petición (ej desde Postman)
        $data = json_decode($request->getContent(), true);

        $email = $data['email'] ?? '';
        $password = $data['password'] ?? '';


        if (empty($email) || empty($password)) {
            return $this->json([
                'error' => 'Missing email or password',
                'success' => false, 
                'login' => false
            ], 400);
        }

        if (!$this->nurseProvider->fileExists()) {
            return $this->json(['error' => 'Data file not found'], 404);
        }

        $nurses = $this->nurseProvider->getAll();

        $authenticated = false;

        // Recorremos los datos para validar el email y la contraseña
        foreach ($nurses as $nurse) {
            if (isset($nurse['email'], $nurse['password']) && 
                $nurse['email'] === $email && 
                $nurse['password'] === $password) {
                $authenticated = true;
                break;
            }
        }

        if ($authenticated) {
            // Credenciales correctas: Devuelve 200 OK
            return $this->json(['success' => true, 'login' => true], 200);
        }

        // Tal como pide el diagrama de flujo del proyecto, devuelve true o false
        return $this->json(['error' => 'Invalid credentials',
            'success' => false, 
            'login' => false
        ], 401);
}
}