<?php

declare(strict_types=1);

namespace Bone\Generator\Service;

use Bone\Generator\Service\Api\ControllerGeneratorService;
use Bone\Generator\Service\Api\EntityGeneratorService;
use Bone\Generator\Service\Api\ServiceGeneratorService;
use Bone\Generator\Service\Api\TestGeneratorService;
use Bone\Generator\Traits\CanGenerateFile;
use Bone\Generator\Traits\CanGenerateRoutes;
use Symfony\Component\Console\Style\SymfonyStyle;

class ApiGeneratorService
{
    use CanGenerateFile;
    use CanGenerateRoutes;

    private SymfonyStyle $io;
    private string $baseNamespace;
    private string $entityName;
    private array $fields;
    private string $outputFolder;
    private string $specFolder;
    private string $testFolder;
    private string $testNamespace;

    public function setIo(SymfonyStyle $io) {
        $this->io = $io;
    }

    public function generateApi(array $data): void
    {
        $this->entityName = $data['entity'];
        $this->fields = $data['fields'];
        $this->outputFolder = $data['outputFolder'];
        $this->specFolder = $data['specFolder'];
        $this->testFolder = $data['testFolder'];
        $this->baseNamespace = $data['namespace'];
        $this->testNamespace = $data['testNamespace'];
        $this->generateEntity();
        $this->generateService();
        $this->generateController();
        $this->generateSpec();
        $this->generateTests();
        $this->generateRoutes();
    }

    public function generateEntity(): void
    {
        $generator = new EntityGeneratorService();
        $generator->generateEntity($this->outputFolder, $this->baseNamespace, $this->entityName, $this->fields);
        $this->io->writeln('generated entity..');
    }

    public function generateService(): void
    {
        $generator = new ServiceGeneratorService();
        $generator->generateService($this->outputFolder, $this->baseNamespace, $this->entityName);
        $this->io->writeln('generated service..');
    }

    public function generateController(): void
    {
        $generator = new ControllerGeneratorService();
        $generator->generateController($this->outputFolder, $this->baseNamespace, $this->entityName);
        $this->io->writeln('generated controller..');
    }

    public function generateSpec(): void
    {

    }

    public function generateTests(): void
    {
        $generator = new TestGeneratorService();
        $generator->generateTest($this->testFolder, $this->testNamespace, $this->entityName, $this->fields);
        $this->io->writeln('generated test..');
    }

    public function generateRoutes(): void
    {
        $slug = $this->getUrlSlug($this->entityName);
        $this->io->writeln('generated routes...');
        $this->io->info([
            'Please add the following to your router config in your package',
            '$router->apiResource(\'' . $slug . '\', ' . $this->entityName . 'Controller::class, $c);',
        ]);
    }
}
