<?php

declare(strict_types=1);

namespace Bone\Generator\Service;

use Bone\Generator\Exception\GeneratorException;
use function file_exists;

class ApiGeneratorService
{
    public function generateApi(array $data): void
    {
        $entityName = $data['entity'];
        $fields = $data['fields'];
    }

    private function ensureNoFileExists(string $fileName): void
    {
        if (file_exists($fileName)) {
            throw new GeneratorException(GeneratorException::FILE_EXISTS);
        }
    }
}
