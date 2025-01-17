<?php

namespace Bone\Generator;

use Barnacle\Container;
use Barnacle\RegistrationInterface;
use Bone\Console\CommandRegistrationInterface;
use Bone\Generator\Command\ControllerCommand;
use Bone\Generator\Service\ControllerGeneratorService;

class GeneratorPackage implements RegistrationInterface, CommandRegistrationInterface
{
    public function registerConsoleCommands(Container $container): array
    {
        $controllerGeneratorService = new ControllerGeneratorService();
        $controller = new ControllerCommand($controllerGeneratorService);
        $controller->setName('generate:controller');

        return [
            $controller
        ];
    }

    public function addToContainer(Container $c): void
    {
    }
}
