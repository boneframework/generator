<?php

namespace Bone\Generator;

use Barnacle\Container;
use Bone\Console\CommandRegistrationInterface;
use Bone\Contracts\Container\RegistrationInterface;

class GeneratorPackage implements RegistrationInterface, CommandRegistrationInterface
{
    public function registerConsoleCommands(Container $container): array
    {
        $controller = new ControllerCommand();
        $controller->setName('generate:contrtoller');

        return [
            $controller
        ];
    }

    public function addToContainer(Container $c): void
    {
        die('AYE');
    }
}
