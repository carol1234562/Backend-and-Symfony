<?php

namespace App\Service;

use Symfony\Component\HttpKernel\KernelInterface;

/**
 * Centraliza la lectura de los datos de enfermeros desde public/nurses.json.
 */
class NurseProvider
{
    public function __construct(private KernelInterface $appKernel)
    {
    }

    /**
     * Devuelve la lista de enfermeros, o un array vacío si el archivo no existe.
     */
    public function getAll(): array
    {
        $filePath = $this->getFilePath();

        if (!file_exists($filePath)) {
            return [];
        }

        $data = json_decode(file_get_contents($filePath), true);

        return $data['nurses'] ?? [];
    }

    public function fileExists(): bool
    {
        return file_exists($this->getFilePath());
    }

    private function getFilePath(): string
    {
        return $this->appKernel->getProjectDir() . '/public/nurses.json';
    }
}
