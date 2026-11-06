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
        $data = array_map(fn (User $nurse): array => $this->serializeNurse($nurse), $nurses);

        return $this->json($data, 200);
    }

    #[Route('/nurse/name/{name}', name: 'app_nurse_find_by_name', methods: ['GET'])]
    public function findByName(string $name): JsonResponse
    {
        if (trim($name) === '') {
            return $this->json(['error' => 'Invalid nurse name', 'success' => false], 400);
        }

        $nurse = $this->userRepository->findOneByName($name);

        if (!$nurse) {
            return $this->json(['error' => 'Nurse not found', 'success' => false], 404);
        }

        return $this->json($this->serializeNurse($nurse), 200);
    }

    #[Route('/nurse/{id}', name: 'app_nurse_show', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function show(int $id): JsonResponse
    {
        $nurse = $this->userRepository->find($id);

        if (!$nurse) {
            return $this->json([
                'error' => 'Nurse not found',
                'success' => false,
            ], 404);
        }

        return $this->json($this->serializeNurse($nurse), 200);
    }

    #[Route('/nurse/{id}', name: 'app_nurse_update', requirements: ['id' => '\d+'], methods: ['PUT'])]
    public function update(
        int $id,
        Request $request,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher
    ): JsonResponse {
        $nurse = $this->userRepository->find($id);

        if (!$nurse) {
            return $this->json([
                'error' => 'Nurse not found',
                'success' => false,
            ], 404);
        }

        try {
            $data = json_decode($request->getContent(), false, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            return $this->json(['error' => 'Invalid JSON body', 'success' => false], 400);
        }

        if (!is_object($data)) {
            return $this->json(['error' => 'The request body must be a JSON object', 'success' => false], 400);
        }

        $fields = [
            'name' => ['maxLength' => 255, 'setter' => 'setName'],
            'last_name' => ['maxLength' => 255, 'setter' => 'setLastName'],
            'national_id' => ['maxLength' => 50, 'setter' => 'setNationalId', 'unique' => true],
            'license_number' => ['maxLength' => 50, 'setter' => 'setLicenseNumber', 'unique' => true],
            'specialty' => ['maxLength' => 100, 'setter' => 'setSpecialty'],
            'shift' => ['maxLength' => 50, 'setter' => 'setShift'],
            'email' => ['maxLength' => 255, 'setter' => 'setEmail', 'unique' => true],
            'password' => ['maxLength' => 255, 'setter' => 'setPassword'],
        ];
        $errors = [];
        $values = [];

        foreach ($fields as $field => $rules) {
            $value = $data->$field ?? null;

            if (!is_string($value) || trim($value) === '') {
                $errors[$field] = 'This field is required and must be a non-empty string.';
                continue;
            }

            $value = trim($value);

            if (mb_strlen($value) > $rules['maxLength']) {
                $errors[$field] = sprintf('This field must not exceed %d characters.', $rules['maxLength']);
                continue;
            }

            if ($field === 'email' && filter_var($value, FILTER_VALIDATE_EMAIL) === false) {
                $errors[$field] = 'This field must be a valid email address.';
                continue;
            }

            $existingUser = ($rules['unique'] ?? false)
                ? $this->userRepository->findOneBy([$field => $value])
                : null;
            if ($existingUser !== null && $existingUser->getId() !== $id) {
                $errors[$field] = 'This value is already in use.';
                continue;
            }

            $values[$field] = $value;
        }

        if ($errors !== []) {
            return $this->json([
                'error' => 'Invalid nurse data',
                'details' => $errors,
                'success' => false,
            ], 400);
        }

        foreach ($fields as $field => $rules) {
            if ($field === 'password') {
                $nurse->setPassword($passwordHasher->hashPassword($nurse, $values['password']));
            } else {
                $nurse->{$rules['setter']}($values[$field]);
            }
        }

        try {
            $entityManager->flush();
        } catch (UniqueConstraintViolationException) {
            return $this->json([
                'error' => 'A nurse with one of these unique values (email, national_id or license_number) already exists',
                'success' => false,
            ], 400);
        } catch (\Throwable $e) {
            return $this->json([
                'error' => 'Database error while updating nurse: ' . $e->getMessage(),
                'success' => false,
            ], 500);
        }

        return $this->json($this->serializeNurse($nurse), 200);
    }

    #[Route('/nurse', name: 'app_nurse_create', methods: ['POST'])]
    public function create(
        Request $request,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher
    ): JsonResponse {
        $rawContent = trim($request->getContent());
        $data = null;

        if ($rawContent !== '') {
            try {
                $data = json_decode($rawContent, true, 512, JSON_THROW_ON_ERROR);
            } catch (\JsonException) {
                return $this->json([
                    'error' => 'Malformed JSON payload',
                    'success' => false,
                ], 400);
            }
        } else {
            $data = $request->request->all();
        }

        if (!is_array($data) || empty($data)) {
            return $this->json([
                'error' => 'Invalid or empty JSON payload',
                'success' => false,
            ], 400);
        }

        $fieldMaxLengths = [
            'name' => 255,
            'last_name' => 255,
            'national_id' => 50,
            'license_number' => 50,
            'specialty' => 100,
            'shift' => 50,
            'email' => 255,
            'password' => 255,
        ];

        $requiredFields = ['name', 'last_name', 'national_id', 'license_number', 'email', 'password'];

        $missingFields = [];
        foreach ($requiredFields as $field) {
            if (!isset($data[$field]) || !is_scalar($data[$field]) || trim((string) $data[$field]) === '') {
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

        foreach (['specialty', 'shift'] as $optionalField) {
            if (array_key_exists($optionalField, $data) && (!is_scalar($data[$optionalField]) || trim((string) $data[$optionalField]) === '')) {
                return $this->json([
                    'error' => sprintf('Field "%s" cannot be an empty string', $optionalField),
                    'success' => false,
                ], 400);
            }
        }

        foreach ($fieldMaxLengths as $field => $maxLength) {
            if (isset($data[$field]) && is_scalar($data[$field]) && mb_strlen(trim((string) $data[$field])) > $maxLength) {
                return $this->json([
                    'error' => sprintf('Field "%s" must not exceed %d characters', $field, $maxLength),
                    'success' => false,
                ], 400);
            }
        }

        $email = trim((string) $data['email']);
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $this->json([
                'error' => 'Invalid email format',
                'success' => false,
            ], 400);
        }

        $nationalId = trim((string) $data['national_id']);
        $licenseNumber = trim((string) $data['license_number']);

        if ($this->userRepository->findOneBy(['email' => $email])) {
            return $this->json([
                'error' => 'Email already registered',
                'success' => false,
            ], 400);
        }

        if ($this->userRepository->findOneBy(['national_id' => $nationalId])) {
            return $this->json([
                'error' => 'National ID already registered',
                'success' => false,
            ], 400);
        }

        if ($this->userRepository->findOneBy(['license_number' => $licenseNumber])) {
            return $this->json([
                'error' => 'License number already registered',
                'success' => false,
            ], 400);
        }

        $user = new User();
        $user->setName(trim((string) $data['name']));
        $user->setLastName(trim((string) $data['last_name']));
        $user->setNationalId($nationalId);
        $user->setLicenseNumber($licenseNumber);
        $user->setSpecialty(isset($data['specialty']) && is_scalar($data['specialty']) ? trim((string) $data['specialty']) : '');
        $user->setShift(isset($data['shift']) && is_scalar($data['shift']) ? trim((string) $data['shift']) : '');
        $user->setEmail($email);

        $hashedPassword = $passwordHasher->hashPassword($user, trim((string) $data['password']));
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
            'nurse' => $this->serializeNurse($user),
        ], 201);
    }

    #[Route('/nurse/{id}', name: 'app_nurse_delete', requirements: ['id' => '\d+'], methods: ['DELETE'])]
    public function delete(int $id, EntityManagerInterface $entityManager): JsonResponse
    {
        $nurse = $this->userRepository->find($id);

        if (!$nurse) {
            return $this->json([
                'error' => 'Nurse not found',
                'success' => false,
            ], 404);
        }

        try {
            $entityManager->remove($nurse);
            $entityManager->flush();
        } catch (\Throwable $e) {
            return $this->json([
                'error' => 'Could not delete nurse: ' . $e->getMessage(),
                'success' => false,
            ], 500);
        }

        return $this->json([
            'message' => 'Nurse deleted successfully',
            'success' => true,
        ], 200);
    }

    /**
     * Serializa la entidad User excluyendo siempre el campo confidencial password.
     */
    private function serializeNurse(User $nurse): array
    {
        return [
            'id' => $nurse->getId(),
            'name' => $nurse->getName(),
            'last_name' => $nurse->getLastName(),
            'national_id' => $nurse->getNationalId(),
            'license_number' => $nurse->getLicenseNumber(),
            'specialty' => $nurse->getSpecialty(),
            'shift' => $nurse->getShift(),
            'email' => $nurse->getEmail(),
        ];
    }
}
