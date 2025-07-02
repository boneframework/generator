<?php

namespace Bone\Generator;

use Barnacle\Container;
use Barnacle\RegistrationInterface;
use Bone\Console\CommandRegistrationInterface;
use Bone\Generator\Command\ApiEntityCommand;
use Bone\Generator\Command\ControllerCommand;
use Bone\Generator\Service\ApiGeneratorService;
use Bone\Generator\Service\ControllerGeneratorService;

class GeneratorPackage implements RegistrationInterface, CommandRegistrationInterface
{
    public function registerConsoleCommands(Container $container): array
    {
        $controllerGeneratorService = new ControllerGeneratorService();
        $controller = new ControllerCommand($controllerGeneratorService);
        $controller->setName('generate:controller');

        $apiGeneratorService = new ApiGeneratorService();
        $api = new ApiEntityCommand($apiGeneratorService);

        return [
            $controller,
            $api,
        ];
    }

    public function addToContainer(Container $c): void
    {
    }
}
