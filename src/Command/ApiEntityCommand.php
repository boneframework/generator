<?php

namespace Bone\Generator\Command;

use Bone\Generator\Service\ApiGeneratorService;
use Bone\Generator\Service\ControllerGeneratorService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

use function file_put_contents;
use function strtolower;

class ApiEntityCommand extends Command
{
    public function __construct(
        private ApiGeneratorService $apiGeneratorService
    ){
        parent::__construct('generate:api-entity');
    }

    protected function configure()
    {
        $this->setDescription('Generate an entity with REST controller and service');
        $this->setHelp('Generate an entity with REST controller and service');
        $this->addOption('load', 'l', InputOption::VALUE_REQUIRED, 'Load the config json file');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('💀 Bone Framework API Entity Generator');
        $load = $input->hasOption('load') ? $input->getOption('load') : false;

        if ($load) {
            $json = file_get_contents($load . '.json');
            $data = json_decode($json, true);
            $this->apiGeneratorService->setIo($io);
            $this->apiGeneratorService->generateApi($data);
            $io->success('Generated API. Have a nice day!');
        } else {
            $outputFolder = $io->ask('Where should we generate the code? ', 'src/App');
            $outputFolderNamespace = $io->ask('What is the base namespace for ' . $outputFolder . '? ', 'Bone\\App');
            $entityName = $io->ask('Enter the entity name: ');
            $fields = [];

            do {
                $fieldName = $io->ask('Enter a field name');
                $io->comment('Available types (integer, numeric, float, decimal, date, datetime, boolean, string, json)');

                $type = $io->ask('Enter a type', 'string');
                $decimal = null;

                if ($type === 'decimal' || $type === 'float') {
                    $decimal = $io->ask('Enter length', '11,2');
                }

                $isRequired = $io->confirm('Is this field required?', true);
                $required = $isRequired ? 'required|' : '';

                switch ($type) {
                    case 'integer':
                        $rule = $required . 'integer';
                        break;
                    case 'float':
                    case 'numeric':
                        $rule = $required . 'numeric';
                        break;
                    case 'decimal':
                        $rule = $required . 'decimal:' . $decimal;
                        break;
                    case 'date':
                        $rule = $required . 'date_format:Y-m-d';
                        break;
                    case 'datetime':
                        $rule = $required . 'date';
                        break;
                    case 'boolean':
                        $rule = $required . 'boolean';
                        break;
                    case 'json':
                        $rule = $required . 'json';
                        break;
                    case 'string':
                    default:
                        $rule = $required . 'string|max:255';
                        break;
                }
                $validation = $io->ask('Enter validation rules.', $rule);
                $isSearchable = $io->confirm('Is this field searchable on the index page?');
                $default = $io->ask('Enter a default value, if any: ');

                $fields[] = [
                    'name' => $fieldName,
                    'type' => $type,
                    'required' => $isRequired,
                    'searchable' => $isSearchable,
                    'validation' => $validation,
                    'decimal' => $decimal,
                    'default' => $default,
                ];

                $continue = $io->confirm('Add another field?');
            } while ($continue === true);

            $data = [
                'entity' => $entityName,
                'fields' => $fields,
                'outputFolder' => $outputFolder,
                'namespace' => $outputFolderNamespace,
            ];
            $json = json_encode($data);
            $path = strtolower($entityName) . '.json';
            file_put_contents($path, $json);
            $io->success([
                'Config saved to ' . $path,
                'Run `bone generate:api-entity --load=' . strtolower($entityName) . '` to generate.'
                ]);
        }

        return Command::SUCCESS;
    }
}
