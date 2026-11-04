<?php

namespace App\Controller;

use App\Repository\UserRepository;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
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

    #[Route('/nurse/{id}', name: 'app_nurse_update', requirements: ['id' => '\d+'], methods: ['PUT'])]
    public function update(int $id, Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $nurse = $this->userRepository->find($id);

        if (!$nurse) {
            return $this->json(['error' => 'Nurse not found'], 404);
        }

        try {
            $data = json_decode($request->getContent(), false, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            return $this->json(['error' => 'Invalid JSON body'], 400);
        }

        if (!is_object($data)) {
            return $this->json(['error' => 'The request body must be a JSON object'], 400);
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

            if (mb_strlen($value) > $rules['maxLength']) {
                $errors[$field] = sprintf('This field must not exceed %d characters.', $rules['maxLength']);
                continue;
            }

            if ($field === 'email' && filter_var($value, FILTER_VALIDATE_EMAIL) === false) {
                $errors[$field] = 'This field must be a valid email address.';
                continue;
            }

            if (($rules['unique'] ?? false) && $this->userRepository->findOneBy([$field => $value])?->getId() !== $id) {
                $errors[$field] = 'This value is already in use.';
                continue;
            }

            $values[$field] = $value;
        }

        if ($errors !== []) {
            return $this->json(['error' => 'Invalid nurse data', 'details' => $errors], 400);
        }

        foreach ($fields as $field => $rules) {
            $nurse->{$rules['setter']}($values[$field]);
        }

        try {
            $entityManager->flush();
        } catch (UniqueConstraintViolationException) {
            return $this->json(['error' => 'A nurse with one of these unique values already exists'], 400);
        }

        return $this->json([
            'id' => $nurse->getId(),
            'name' => $nurse->getName(),
            'last_name' => $nurse->getLastName(),
            'national_id' => $nurse->getNationalId(),
            'license_number' => $nurse->getLicenseNumber(),
            'specialty' => $nurse->getSpecialty(),
            'shift' => $nurse->getShift(),
            'email' => $nurse->getEmail(),
        ], 200);
    }
}