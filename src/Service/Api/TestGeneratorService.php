<?php

declare(strict_types=1);

namespace Bone\Generator\Service\Api;

use _PHPStan_bc6352b8e\Nette\Utils\DateTime;
use Bone\Generator\Traits\CanGenerateFile;
use Bone\Generator\Traits\CanGenerateRoutes;
use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\PhpFile;
use Nette\PhpGenerator\PhpNamespace;
use function random_int;
use function uniqid;

class TestGeneratorService
{
    use CanGenerateFile;
    use CanGenerateRoutes;

    public function generateTest($outputFolder, string $baseNamespace, string $entityName, array $fields): void
    {
        $fileName = $outputFolder . '/' . $entityName . 'Test.php';
        $this->ensureNoFileExists($fileName);
        $this->makeFolder($outputFolder);
        $file = $this->createPHPFile();
        $namespace = $this->createNamespace($file, $baseNamespace);
        $class = $this->createClass($namespace, $entityName);
        $this->createBefore($class);
        $this->generateCreateTest($class, $entityName, $fields);
        $this->generateIndexTest($class, $entityName);
        $this->generateReadTest($class, $entityName);
        $this->generateUpdateTest($class, $entityName, $fields);
        $this->generateDeleteTest($class, $entityName);
        $this->writeFile($file, $fileName);
    }

    private function createNamespace(PhpFile $file, string $testNamespace): PhpNamespace
    {
        $namespace = $file->addNamespace($testNamespace);
        $namespace->addUse('Tests\Support\ApiTester');

        return $namespace;
    }

    private function createClass(PhpNamespace $namespace, string $entityName): ClassType
    {
        $class = $namespace->addClass($entityName . 'Test');
        $id = $class->addProperty('id');
        $id->setType('int');
        $id->setVisibility('private');

        return $class;
    }

    private function createBefore(ClassType $class): void
    {
        $method = $class->addMethod('_before');
        $method->setReturnType('void');
        $arg = $method->addParameter('I');
        $arg->setType('Tests\Support\ApiTester');
        $body = '$I->haveHttpHeader(\'Accept\', \'application/json\');
$I->haveHttpHeader(\'Content-Type\', \'application/json\');';
        $method->addBody($body);
    }

    private function generateCreateTest(ClassType $class, string $entityName, array $fields): void
    {
        $method = $class->addMethod('tryCreateEndpoint');
        $method->setReturnType('void');
        $param = $method->addParameter('I');
        $param->setType('Tests\Support\ApiTester');
        $urlSlug = $this->getUrlSlug($entityName);
        $data = $this->getFields($fields);
        $body = '$I->sendPost(\'/api/' . $urlSlug . '\', ' . $data . ');
$I->seeResponseCodeIsSuccessful();
$I->seeResponseIsJson();
$I->seeResponseCodeIs(201);
$body = $I->grabResponse();
$I->canSeeValidApiSpec(\'post\', \'/' . $urlSlug . '\', $body, 201);
$this->id = $I->grabDataFromResponseByJsonPath(\'$.id\')[0];';
        $method->setBody($body);
    }

    private function generateIndexTest(ClassType $class, string $entityName): void
    {
        $method = $class->addMethod('tryIndexEndpoint');
        $method->setReturnType('void');
        $param = $method->addParameter('I');
        $param->setType('Tests\Support\ApiTester');
        $urlSlug = $this->getUrlSlug($entityName);
        $body = '$I->sendGet(\'/api/' . $urlSlug . '\');
$I->seeResponseCodeIsSuccessful();
$I->seeResponseIsJson();
$I->seeResponseCodeIs(200);
$body = $I->grabResponse();
$I->canSeeValidApiSpec(\'get\', \'' . $urlSlug . '\', $body);';
        $method->setBody($body);
    }

    private function generateReadTest(ClassType $class, string $entityName): void
    {
        $method = $class->addMethod('tryViewEndpoint');
        $method->setReturnType('void');
        $param = $method->addParameter('I');
        $param->setType('Tests\Support\ApiTester');
        $urlSlug = $this->getUrlSlug($entityName);
        $url = $this->getRecordRoute($entityName);
        $body = '$I->sendGet(\'/api/' . $urlSlug . '/\' . $this->id);
$I->seeResponseCodeIsSuccessful();
$I->seeResponseIsJson();
$I->seeResponseCodeIs(200);
$body = $I->grabResponse();
$I->canSeeValidApiSpec(\'get\', \'' . $url . '\', $body);';
        $method->setBody($body);
    }

    private function generateUpdateTest(ClassType $class, string $entityName, array $fields): void
    {
        $method = $class->addMethod('tryUpdateEndpoint');
        $method->setReturnType('void');
        $param = $method->addParameter('I');
        $param->setType('Tests\Support\ApiTester');
        $urlSlug = $this->getUrlSlug($entityName);
        $url = $this->getRecordRoute($entityName);
        $data = $this->getFields($fields);
        $body = '$I->sendPatch(\'/api/' . $urlSlug . '/\' . $this->id, ' . $data . ');
$I->seeResponseCodeIsSuccessful();
$I->seeResponseIsJson();
$I->seeResponseCodeIs(200);
$body = $I->grabResponse();
$I->canSeeValidApiSpec(\'patch\', \'' . $url . '\', $body);';
        $method->setBody($body);
    }

    private function generateDeleteTest(ClassType $class, string $entityName): void
    {
        $method = $class->addMethod('tryDeleteEndpoint');
        $method->setReturnType('void');
        $param = $method->addParameter('I');
        $param->setType('Tests\Support\ApiTester');
        $urlSlug = $this->getUrlSlug($entityName);
        $url = $this->getRecordRoute($entityName);
        $body = '$I->sendDelete(\'/api/' . $urlSlug . '/\' . $this->id);
$I->seeResponseCodeIsSuccessful();
$I->seeResponseCodeIs(204);
$body = $I->grabResponse();
$I->canSeeValidApiSpec(\'delete\', \'' . $url . '\', $body, 204);';
        $method->setBody($body);
    }

    private function getFields(array $fields): string
    {
        $days = random_int(1,20);
        $date = new DateTime('+' . $days . ' days');
        $data = "[\n";

        foreach ($fields as $field) {
            $data .= '    \'' . $field['name'] . '\' => ';

            switch ($field['type']) {
                case 'integer':
                case 'float':
                    $data .= random_int(1,100);
                    break;
                case 'date':
                case 'datetime':
                    $data .= "'" . $date->format('Y-m-d H:i:s') . "'";
                    break;
                default:
                    $data .= "'" . uniqid('test_', true) . "'";
                    break;
            }

            $data .= ",\n";
        }

        $data .= "]";

        return $data;
    }
}
