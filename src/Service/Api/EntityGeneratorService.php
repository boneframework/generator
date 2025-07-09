<?php

declare(strict_types=1);

namespace Bone\Generator\Service\Api;

use Bone\Generator\Traits\CanGenerateFile;
use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\PhpFile;
use Nette\PhpGenerator\PhpNamespace;
use function array_key_exists;
use function explode;
use function in_array;
use function preg_match;
use function ucfirst;

class EntityGeneratorService
{
    use CanGenerateFile;

    const ENTITY_TRAITS = [
        'createdAt' => 'Bone\BoneDoctrine\Traits\HasCreatedAtDate',
        'deletedAt' => 'Bone\BoneDoctrine\Traits\HasDeletedAtDate',
        'email' => 'Bone\BoneDoctrine\Traits\HasEmail',
        'expiryDate' => 'Bone\BoneDoctrine\Traits\HasExpiryDate',
        'image' => 'Bone\BoneDoctrine\Traits\HasImage',
        'latitude' => 'Bone\BoneDoctrine\Traits\HasLocationCoordinates',
        'longitude' => 'Bone\BoneDoctrine\Traits\HasLocationCoordinates',
        'name' => 'Bone\BoneDoctrine\Traits\HasName',
        'private' => 'Bone\BoneDoctrine\Traits\HasPrivacy',
        'settings' => 'Bone\BoneDoctrine\Traits\HasSettings',
        'telephone' => 'Bone\BoneDoctrine\Traits\HasTelephone',
        'updatedAt' => 'Bone\BoneDoctrine\Traits\HasUpdatedAtDate',
        'url' => 'Bone\BoneDoctrine\Traits\HasUrl',
        'urlSlug' => 'Bone\BoneDoctrine\Traits\HasUrlSlug',
        'visible' => 'Bone\BoneDoctrine\Traits\HasVisibility',
    ];

    private array $used = [];

    public function generateEntity($outputFolder, string $baseNamespace, string $entityName, array $fields): void
    {
        $entityNamespace = $baseNamespace . '\\Entity';
        $folderName = $outputFolder . '/Entity';
        $fileName = $folderName . '/' . $entityName . '.php';
        $this->ensureNoFileExists($fileName);
        $this->makeFolder($folderName);
        $file = $this->createPHPFile();
        $namespace = $this->createNamespace($file, $entityNamespace);
        $class = $this->createClass($namespace, $entityName);

        foreach ($fields as $field) {
            $this->createField($namespace, $class, $field);
        }

        $this->createToArray($class, $fields);
        $this->writeFile($file, $fileName);
    }

    private function createNamespace(PhpFile $file, string $entityNamespace): PhpNamespace
    {
        $namespace = $file->addNamespace($entityNamespace);
        $namespace->addUse('Bone\BoneDoctrine\Traits\HasId');
        $namespace->addUse('DateTimeInterface');
        $namespace->addUse('Del\Form\Field\Attributes\Field');
        $namespace->addUse('Del\Form\Traits\HasFormFields');
        $namespace->addUse('Doctrine\ORM\Mapping', 'ORM');

        return $namespace;
    }

    private function createClass(PhpNamespace $namespace, string $entityName): ClassType
    {
        $class = $namespace->addClass($entityName);
        $class->addAttribute('Doctrine\ORM\Mapping\Entity');
        $class->addAttribute('Doctrine\ORM\Mapping\HasLifecycleCallbacks');
        $class->addTrait('Del\Form\Traits\HasFormFields');
        $class->addTrait('Bone\BoneDoctrine\Traits\HasId');

        return $class;
    }

    private function createField(PhpNamespace $namespace, ClassType $class, array $data): void
    {
        $name = $data['name'];
        $type = $data['type'];
        $phpType = $this->getPhpType($type);
        $doctrineType = $this->getDoctrineType($type);
        $required = $data['required'];
        $default = $data['default'];
        $decimal = $data['decimal'];
        $validation = $data['validation'];

        if ($this->shouldUseTrait($name)) {
            if ($this->isAlreadyUsed($name)) {
                return;
            }

            $namespace->addUse(self::ENTITY_TRAITS[$name]);
            $class->addTrait(self::ENTITY_TRAITS[$name]);
            $this->used[] = self::ENTITY_TRAITS[$name];

            return;
        }

        $property = $class->addProperty($name);
        $property->setPrivate();
        $property->setType($phpType);
        $attributeData = ['type' => $doctrineType];
        $regex = '#.+max:(?<length>\d+).+#';
        preg_match($regex, $validation, $result);

        if ($result['length']) {
            $attributeData['length'] = (int) $result['length'];
        }

        if (!$required) {
            $attributeData['nullable'] = true;
        }

        if ($type === 'float') {
            $split = explode(',', $decimal);
            $attributeData['precision'] = (int) $split[0];
            $attributeData['scale'] = (int) $split[1];
        }

        if ($default) {
            $attributeData['default'] = $default;
        }

        $property->addAttribute('Doctrine\ORM\Mapping\Column', $attributeData);
        $property->addAttribute('Del\Form\Field\Attributes\Field', [$validation]);
        $this->addGetterAndSetter($name, $type, $class);
    }

    private function shouldUseTrait(string $name): bool
    {
       return array_key_exists($name, self::ENTITY_TRAITS);
    }

    private function isAlreadyUsed(string $name): bool
    {
        return in_array($name, $this->used);
    }

    private function getPhpType(string $type): string
    {
        switch ($type) {
            case 'integer':
                return 'int';
            case 'boolean':
                return 'bool';
            case 'date':
            case 'datetime':
                return 'DateTimeInterface';
            case 'float':
            case 'decimal':
            case 'numeric':
                return 'float';
            case 'json':
            case 'string':
            default:
                return 'string';
        }
    }

    private function getDoctrineType(string $type): string
    {
        switch ($type) {
            case 'integer':
                return 'integer';
            case 'boolean':
                return 'boolean';
            case 'date':
            case 'datetime':
                return 'datetime';
            case 'float':
            case 'decimal':
            case 'numeric':
                return 'float';
            case 'json':
            case 'string':
            default:
                return 'string';
        }
    }

    private function addGetterAndSetter(string $field, string $type, ClassType $class): void
    {
        $getterName = 'get' . ucfirst($field);
        $setterName = 'set' . ucfirst($field);
        $getter = $class->addMethod($getterName);
        $setter = $class->addMethod($setterName);
        $setter->setReturnType('void');
        $param = $setter->addParameter($field);

        if ($type === 'json') {
            $getter->setReturnType('array');
            $getterBody = 'return \json_decode($this->' . $field . ', true);';
            $param->setType('array');
            $setterBody = '$this->' . $field . ' = \json_encode($' . $field . ');';
        } else {
            $getter->setReturnType($this->getPhpType($type));
            $getterBody = 'return $this->' . $field . ';';
            $param->setType($this->getPhpType($type));
            $setterBody = '$this->' . $field . ' = $' . $field . ';';
        }

        $getter->setBody($getterBody);
        $setter->setBody($setterBody);
    }

    private function createToArray(ClassType $class, array $fields): void
    {
        $method = $class->addMethod('toArray');
        $method->setReturnType('array');
        $body = "return [\n";

        foreach ($fields as $field) {
            $body .= '    \'' . $field['name'] . '\' => ';
            $body .= $this->getToArrayGetter($field['name'], $field['type']);
            $body .= "\n";
        }

        $body .= '];';
        $method->setBody($body);
    }

    private function getToArrayGetter(string $field, string $type): string
    {
        switch ($type) {
            case 'date':
            case 'datetime':
                return '$this->' . $field . '->format(\'Y-m-d H:i:s\'),';
            case 'json':
                return '$this->get' . ucfirst($field) . '(),';
            default:
                return '$this->' . $field . ',';
        }
    }
}
