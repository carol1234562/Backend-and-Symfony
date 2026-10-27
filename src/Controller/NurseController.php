<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
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

    #[Route('/nurse', name: 'app_nurse_create', methods: ['POST'])]
    public function create(
        Request $request,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);
        if (!is_array($data)) {
            $data = $request->request->all();
        }

        if (!is_array($data) || empty($data)) {
            return $this->json([
                'error' => 'Invalid or empty JSON payload',
                'success' => false,
            ], 400);
        }

        $requiredFields = [
            'name',
            'last_name',
            'national_id',
            'license_number',
            'specialty',
            'shift',
            'email',
            'password',
        ];

        $missingFields = [];
        foreach ($requiredFields as $field) {
            if (!isset($data[$field]) || trim((string)$data[$field]) === '') {
                $missingFields[] = $field;
            }
        }

        if (!empty($missingFields)) {
            return $this->json([
                'error' => 'Missing required fields: ' . implode(', ', $missingFields),
                'missing_fields' => $missingFields,
                'success' => false,
            ], 400);
        }

        $email = trim((string)$data['email']);
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $this->json([
                'error' => 'Invalid email format',
                'success' => false,
            ], 400);
        }

        if ($this->userRepository->findOneBy(['email' => $email])) {
            return $this->json([
                'error' => 'Email already registered',
                'success' => false,
            ], 400);
        }

        if ($this->userRepository->findOneBy(['national_id' => trim((string)$data['national_id'])])) {
            return $this->json([
                'error' => 'National ID already registered',
                'success' => false,
            ], 400);
        }

        if ($this->userRepository->findOneBy(['license_number' => trim((string)$data['license_number'])])) {
            return $this->json([
                'error' => 'License number already registered',
                'success' => false,
            ], 400);
        }

        $user = new User();
        $user->setName(trim((string)$data['name']));
        $user->setLastName(trim((string)$data['last_name']));
        $user->setNationalId(trim((string)$data['national_id']));
        $user->setLicenseNumber(trim((string)$data['license_number']));
        $user->setSpecialty(trim((string)$data['specialty']));
        $user->setShift(trim((string)$data['shift']));
        $user->setEmail($email);

        $hashedPassword = $passwordHasher->hashPassword($user, (string)$data['password']);
        $user->setPassword($hashedPassword);

        try {
            $entityManager->persist($user);
            $entityManager->flush();
        } catch (UniqueConstraintViolationException) {
            return $this->json([
                'error' => 'A user with the provided email, national ID or license number already exists',
                'success' => false,
            ], 400);
        } catch (\Throwable $e) {
            return $this->json([
                'error' => 'Could not save nurse: ' . $e->getMessage(),
                'success' => false,
            ], 500);
        }

        return $this->json([
            'message' => 'Nurse created successfully',
            'success' => true,
            'nurse' => $user,
        ], 201);
    }
}