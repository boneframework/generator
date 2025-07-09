<?php

declare(strict_types=1);

namespace Bone\Generator\Service\Api;

use Bone\Generator\Traits\CanGenerateFile;
use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\PhpFile;
use Nette\PhpGenerator\PhpNamespace;

class ControllerGeneratorService
{
    use CanGenerateFile;

    public function generateController($outputFolder, string $baseNamespace, string $entityName): void
    {
        $entityNamespace = $baseNamespace . '\\Http\\Controller\\Api';
        $service = $baseNamespace . '\\Service\\' . $entityName . 'Service';
        $folderName = $outputFolder . '/Http/Controller/Api';
        $fileName = $folderName . '/' . $entityName . 'Controller.php';
        $this->ensureNoFileExists($fileName);
        $this->makeFolder($folderName);
        $file = $this->createPHPFile();
        $namespace = $this->createNamespace($file, $entityNamespace, $service);
        $class = $this->createClass($namespace, $entityName);
        $this->createGetServiceClass($class, $entityName);
        $this->writeFile($file, $fileName);
    }

    private function createNamespace(PhpFile $file, string $entityNamespace, string $service): PhpNamespace
    {
        $namespace = $file->addNamespace($entityNamespace);
        $namespace->addUse('Bone\Http\Controller\ApiController');
        $namespace->addUse($service);

        return $namespace;
    }

    private function createClass(PhpNamespace $namespace, string $entityName): ClassType
    {
        $class = $namespace->addClass($entityName . 'Controller');
        $class->setExtends('Bone\Http\Controller\ApiController');

        return $class;
    }

    private function createGetServiceClass(ClassType $class, string $entityName): void
    {
        $method = $class->addMethod('getServiceClass');
        $method->setReturnType('string');
        $method->setBody('return ' . $entityName . 'Service::class;');
    }
}
