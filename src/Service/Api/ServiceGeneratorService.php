<?php

declare(strict_types=1);

namespace Bone\Generator\Service\Api;

use Bone\Generator\Traits\CanGenerateFile;
use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\PhpFile;
use Nette\PhpGenerator\PhpNamespace;

class ServiceGeneratorService
{
    use CanGenerateFile;

    public function generateService($outputFolder, string $baseNamespace, string $entityName): void
    {
        $entityNamespace = $baseNamespace . '\\Service';
        $entity = $baseNamespace . '\\Entity\\' . $entityName;
        $folderName = $outputFolder . '/Service';
        $fileName = $folderName . '/' . $entityName . 'Service.php';
        $this->ensureNoFileExists($fileName);
        $this->makeFolder($folderName);
        $file = $this->createPHPFile();
        $namespace = $this->createNamespace($file, $entityNamespace, $entity);
        $class = $this->createClass($namespace, $entityName);
        $this->createGetEntityClass($class, $entityName);
        $this->writeFile($file, $fileName);
    }

    private function createNamespace(PhpFile $file, string $entityNamespace, string $entity): PhpNamespace
    {
        $namespace = $file->addNamespace($entityNamespace);
        $namespace->addUse('Bone\BoneDoctrine\Service\RestService');
        $namespace->addUse($entity);

        return $namespace;
    }

    private function createClass(PhpNamespace $namespace, string $entityName): ClassType
    {
        $class = $namespace->addClass($entityName . 'Service');
        $class->setExtends('Bone\BoneDoctrine\Service\RestService');

        return $class;
    }

    private function createGetEntityClass(ClassType $class, string $entityName): void
    {
        $method = $class->addMethod('getEntityClass');
        $method->setReturnType('string');
        $method->setBody('return ' . $entityName . '::class;');
    }
}
