<?php

namespace Bone\Generator\Command;

use Bone\Generator\Service\ControllerGeneratorService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ControllerCommand extends Command
{
    public function __construct(
        private ControllerGeneratorService $controllerGeneratorService
    ) {
        parent::__construct('controller');
    }

    protected function configure()
    {
        $this->setDescription('Generate a Controller');
        $this->setHelp('Clears the build folder out');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Bone Framework Generator');
        $srcFolderNamespace = $io->ask('Enter the base namespace for src/ : ', 'Bone\\App');
        $controllerNamespace = $io->ask('Enter the controller namespace: ', 'Controller');
        $controllerName = $io->ask('Enter the controller name: ', 'TestController');
        $features = [];
        $keepAddingFeatures = true;
        $chosenFeatures = [];
        $features = ['entity manager', 'i18n', 'logger', 'pdo', 'serializer', 'session', 'site config', 'view', 'continue'];

        while ($keepAddingFeatures) {
            $io->horizontalTable(['selected Featues'], [[\implode(', ', $chosenFeatures)]]);
            $feature = $io->choice('Select a feature or choose to continue', $features);

            if ($feature === 'continue') {
                $keepAddingFeatures = false;
            } else {
                $chosenFeatures[] = $feature;
                $key = \array_search($feature, $features);
                unset($features[$key]);
            }
        }

        $path = './src/' . $controllerNamespace . '/' . $controllerName . '.php';
        $io->writeln('Controller will be generated to ' . $path);
        $confirm = $io->confirm('Is this OK?');

        if (!$confirm) {
            $io->error('Controller generation cancelled.');

            return Command::SUCCESS;
        }

        $this->controllerGeneratorService->generateController($srcFolderNamespace, $controllerNamespace, $controllerName, $features);
        $io->success('Generated ' . $namespace . '\\' . $controllerName . ' in BUILDFOLDERHERE');

        return  Command::SUCCESS;
    }
}
