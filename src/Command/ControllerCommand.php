<?php

namespace Del\Generator;

use _PHPStan_62c6a0a8b\Symfony\Component\Console\Style\SymfonyStyle;
use InvalidArgumentException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ClearBuildsCommand extends Command
{
    public function __construct()
    {
        parent::__construct('controller');
    }

    protected function configure()
    {
        $this->setDescription('Generate a Controller');
        $this->setHelp('Clears the build folder out');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle();
        $io->success('Bone Framework Generator');

        return  Command::SUCCESS;
    }
}
