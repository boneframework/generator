<?php

namespace Bone\Generator;

use Barnacle\Container;
use Barnacle\RegistrationInterface;
use Bone\Console\CommandRegistrationInterface;
use Bone\Generator\Command\ControllerCommand;

class GeneratorPackage implements RegistrationInterface, CommandRegistrationInterface
{
    public function registerConsoleCommands(Container $container): array
    {
        $controller = new ControllerCommand();
        $controller->setName('generate:controller');

        return [
            $controller
        ];
    }

    public function addToContainer(Container $c): void
    {
    }
}
