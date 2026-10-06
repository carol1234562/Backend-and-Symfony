<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpKernel\KernelInterface;

class LoginController extends AbstractController {
    private $appKernel;

    public function __construct(KernelInterface $appKernel) {
        $this->appKernel = $appKernel;
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

        // Ruta hacia el archivo JSON con los datos
        $filePath = $this->appKernel->getProjectDir() . '/public/nurses.json';
        
        if (!file_exists($filePath)) {
            return $this->json(['error' => 'Data file not found'], 404);
        }

        $jsonData = file_get_contents($filePath);
        $allData = json_decode($jsonData, true);
        $nurses = $allData['nurses'] ?? [];

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