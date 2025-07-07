<?php

declare(strict_types=1);

namespace Bone\Generator\Service;

use Bone\Generator\Service\Api\EntityGeneratorService;
use Bone\Generator\Traits\CanGenerateFile;
use Symfony\Component\Console\Style\SymfonyStyle;

class ApiGeneratorService
{
    use CanGenerateFile;

    private SymfonyStyle $io;
    private string $entityName;
    private array $fields;
    private string $outputFolder;
    private string $baseNamespace;

    public function setIo(SymfonyStyle $io) {
        $this->io = $io;
    }

    public function generateApi(array $data): void
    {
        $this->entityName = $data['entity'];
        $this->fields = $data['fields'];
        $this->outputFolder = $data['outputFolder'];
        $this->baseNamespace = $data['namespace'];
        $this->generateEntity();
        $this->generateService();
        $this->generateController();
        $this->generateSpec();
        $this->generateTests();
    }

    public function generateEntity(): void
    {
        $generator = new EntityGeneratorService();
        $generator->generateEntity($this->outputFolder, $this->baseNamespace, $this->entityName, $this->fields);
        $this->io->info('generated entity..');
    }

    public function generateService(): void
    {

    }

    public function generateController(): void
    {

    }

    public function generateSpec(): void
    {

    }

    public function generateTests(): void
    {

    }

    public function generateRoute(): void
    {

    }
}
