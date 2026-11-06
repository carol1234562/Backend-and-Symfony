<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class NurseControllerTest extends WebTestCase
{
    public function testFullNurseCrudLifecycle(): void
    {
        $client = static::createClient();
        $uniqueSuffix = uniqid();

        // 1. CREATE (POST /nurse) -> 201 Created
        $createPayload = [
            'name' => 'TestNurse',
            'last_name' => 'Gómez',
            'national_id' => 'DNI-' . $uniqueSuffix,
            'license_number' => 'LIC-' . $uniqueSuffix,
            'specialty' => 'Cardiology',
            'shift' => 'Morning',
            'email' => 'nurse.' . $uniqueSuffix . '@hospital.com',
            'password' => 'Secret123!',
        ];

        $client->request(
            'POST',
            '/nurse',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($createPayload)
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);
        $createResponse = json_decode($client->getResponse()->getContent(), true);
        $this->assertTrue($createResponse['success'] ?? false);
        $this->assertArrayHasKey('nurse', $createResponse);
        $this->assertArrayNotHasKey('password', $createResponse['nurse']);

        $nurseId = $createResponse['nurse']['id'];
        $this->assertNotEmpty($nurseId);

        // 1b. CREATE (POST /nurse) -> 400 Bad Request (Duplicate email / national_id)
        $client->request(
            'POST',
            '/nurse',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($createPayload)
        );
        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
        $duplicateResponse = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('error', $duplicateResponse);

        // 1c. CREATE (POST /nurse) -> 400 Bad Request (Missing fields / Malformed JSON)
        $client->request(
            'POST',
            '/nurse',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode(['name' => 'Incomplete'])
        );
        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);

        $client->request(
            'POST',
            '/nurse',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            '{malformed_json'
        );
        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);

        // 2. READ ALL (GET /nurse/index) -> 200 OK & No password exposed
        $client->request('GET', '/nurse/index');
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $allNurses = json_decode($client->getResponse()->getContent(), true);
        $this->assertIsArray($allNurses);
        $this->assertNotEmpty($allNurses);
        $this->assertArrayNotHasKey('password', $allNurses[0]);

        // 3a. READ BY ID (GET /nurse/{id}) -> 200 OK & No password exposed
        $client->request('GET', '/nurse/' . $nurseId);
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $fetchedById = json_decode($client->getResponse()->getContent(), true);
        $this->assertSame($nurseId, $fetchedById['id']);
        $this->assertSame('TestNurse', $fetchedById['name']);
        $this->assertArrayNotHasKey('password', $fetchedById);

        // 3b. READ BY ID (GET /nurse/{id}) -> 404 Not Found
        $client->request('GET', '/nurse/99999999');
        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);

        // 3c. READ BY NAME (GET /nurse/name/{name}) -> 200 OK & 404 Not Found
        $client->request('GET', '/nurse/name/TestNurse');
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $fetchedByName = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayNotHasKey('password', $fetchedByName);

        $client->request('GET', '/nurse/name/NonExistentNurse_' . $uniqueSuffix);
        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);

        // 4a. UPDATE (PUT /nurse/{id}) -> 200 OK & No password exposed
        $updatePayload = [
            'name' => 'UpdatedNurse',
            'last_name' => 'Gómez Modificado',
            'national_id' => 'DNI-' . $uniqueSuffix,
            'license_number' => 'LIC-' . $uniqueSuffix,
            'specialty' => 'Neurology',
            'shift' => 'Night',
            'email' => 'nurse.' . $uniqueSuffix . '@hospital.com',
            'password' => 'NewSecret123!',
        ];

        $client->request(
            'PUT',
            '/nurse/' . $nurseId,
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($updatePayload)
        );
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $updatedNurse = json_decode($client->getResponse()->getContent(), true);
        $this->assertSame('UpdatedNurse', $updatedNurse['name']);
        $this->assertSame('Neurology', $updatedNurse['specialty']);
        $this->assertArrayNotHasKey('password', $updatedNurse);

        // 4b. UPDATE (PUT /nurse/{id}) -> 400 Bad Request (Invalid data)
        $client->request(
            'PUT',
            '/nurse/' . $nurseId,
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode(['name' => ''])
        );
        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);

        // 4c. UPDATE (PUT /nurse/{id}) -> 404 Not Found
        $client->request(
            'PUT',
            '/nurse/99999999',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($updatePayload)
        );
        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);

        // 5a. DELETE (DELETE /nurse/{id}) -> 200 OK
        $client->request('DELETE', '/nurse/' . $nurseId);
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        // 5b. DELETE (DELETE /nurse/{id}) -> 404 Not Found (Already deleted)
        $client->request('DELETE', '/nurse/' . $nurseId);
        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }
}
