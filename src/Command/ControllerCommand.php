<?php

namespace Bone\Generator\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ControllerCommand extends Command
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
        $io = new SymfonyStyle($input, $output);
        $io->success('Bone Framework Generator');

        return  Command::SUCCESS;
    }
}
