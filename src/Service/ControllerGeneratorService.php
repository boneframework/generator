<?php

declare(strict_types=1);

namespace Bone\Generator\Service;

use Bone\Generator\Exception\GeneratorException;
use Nette\PhpGenerator\PhpFile;
use Nette\PhpGenerator\PsrPrinter;

use function explode;
use function file_exists;
use function file_put_contents;
use function implode;
use function in_array;
use function sprintf;

class ControllerGeneratorService
{
    const F_ENTITY_MANAGER = 'entity manager';
    const F_I18N = 'i18n';
    const F_LOGGER = 'logger';
    const F_PDO = 'pdo';
    const F_SERIALIZER = 'serializer';
    const F_SESSION = 'session';
    const F_SITE_CONFIG = 'site config';
    const F_VIEW = 'view';

    const FEATURES = [
        self::F_ENTITY_MANAGER,
        self::F_I18N,
        self::F_LOGGER,
        self::F_PDO,
        self::F_SERIALIZER,
        self::F_SESSION,
        self::F_SITE_CONFIG,
        self::F_VIEW,
    ];

    const INTERFACES = [
        self::F_ENTITY_MANAGER => 'Bone\BoneDoctrine\EntityManagerAwareInterface',
        self::F_I18N => 'Bone\I18n\I18nAwareInterface',
        self::F_LOGGER => 'Bone\Log\LoggerAwareInterface',
        self::F_PDO => 'Bone\Db\DbProviderInterface',
        self::F_SERIALIZER => 'Bone\Controller\SerializerAwareInterface',
        self::F_SESSION => 'Bone\Server\SessionAwareInterface',
        self::F_SITE_CONFIG => 'Bone\Server\SiteConfigAwareInterface',
        self::F_VIEW => 'Bone\View\ViewAwareInterface',
    ];

    const TRAITS = [
        self::F_ENTITY_MANAGER => 'Bone\BoneDoctrine\Traits\HasEntityManagerTrait',
        self::F_I18N => 'Bone\I18n\Traits\HasTranslatorTrait',
        self::F_LOGGER => 'Bone\Log\Traits\HasLoggerTrait',
        self::F_PDO => 'Bone\Db\HasDbTrait',
        self::F_SERIALIZER => 'Bone\Controller\Traits\HasSerializer',
        self::F_SESSION => 'Bone\Server\Traits\HasSessionTrait',
        self::F_SITE_CONFIG => 'Bone\Server\Traits\HasSiteConfigTrait',
        self::F_VIEW => 'Bone\View\Traits\HasViewTrait',
    ];

    public function generateController(
        string $srcFolderNamespace,
        string $controllerNamespace,
        string $controllerName,
        array $features
    ): void {
        $controllerNamespacePath = implode('/', explode('\\', $controllerNamespace));
        $fileName = './src/' . $controllerNamespacePath . '/' . $controllerName . '.php';
        $this->ensureNoFileExists($fileName);
        $file = new PhpFile();
        $file->setStrictTypes();
        $namespace = $srcFolderNamespace . '\\' . $controllerNamespace;
        $namespace = $file->addNamespace($namespace);
        $namespace->addUse('Bone\Http\Response');
        $namespace->addUse('Psr\Http\Message\ResponseInterface');
        $namespace->addUse('Psr\Http\Message\ServerRequestInterface');
        $class = $namespace->addClass($controllerName);

        foreach ($features as $feature) {
            $namespace->addUse(self::INTERFACES[$feature]);
            $namespace->addUse(self::TRAITS[$feature]);
            $class->addImplement(self::INTERFACES[$feature]);
            $class->addTrait(self::TRAITS[$feature]);
        }

        $method = $class->addMethod('index');
        $method->addParameter('request')->setType('Psr\Http\Message\ServerRequestInterface');
        $method->setReturnType('Psr\Http\Message\ResponseInterface');
        $method->setBody('return new Response();');

        if (in_array('logger', $features)) {
            $method = $class->addMethod('getChannel');
            $method->setReturnType('string');
            $method->setBody('return \'default\';');
        }

        $printer = new PsrPrinter();
        $code = $printer->printFile($file);
        $result = file_put_contents($fileName, $code);

        if (!$result) {
            throw new GeneratorException(sprintf(GeneratorException::FILE_WRITE_ERROR, $fileName));
        }
    }

    private function ensureNoFileExists(string $fileName): void
    {
        if (file_exists($fileName)) {
            throw new GeneratorException(GeneratorException::FILE_EXISTS);
        }
    }
}
