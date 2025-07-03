<?php

namespace App\UI\Command;

use App\Application\DTO\ProduceDTO;
use App\Application\Service\ProduceImporter;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Serializer\SerializerInterface;

#[AsCommand(name: 'app:import-from-file', description: 'Import produce items from a JSON file')]
final  class ImportProduceCommand extends Command
{
    public function __construct(
        private readonly SerializerInterface $serializer,
        private readonly ProduceImporter $produceImporter)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('file', InputArgument::REQUIRED, 'Path to request.json');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $symfonyIO = new SymfonyStyle($input, $output);
        $jsonPath = $input->getArgument('file');

        if (!file_exists($jsonPath)) {
            $symfonyIO->error('File not found: ' . $jsonPath);
            return Command::FAILURE;
        }
        $jsonData = file_get_contents($jsonPath);

        try {
            /** @var ProduceDTO[] $dtos */
            $dtos = $this->serializer->deserialize($jsonData, ProduceDTO::class . '[]', 'json');
            $result = $this->produceImporter->import($dtos);
            if ($result->errors) {
                $symfonyIO->warning("Imported {$result->importedCount} items, but found " . count($result->errors) . " invalid entries:");
                foreach ($result->errors as $e) {
                    $symfonyIO->error("  - $e");
                }
            } else {
                $symfonyIO->success("Imported {$result->importedCount} items without errors.");
            }
            return Command::SUCCESS;
        } catch (\Throwable $e) {
            $symfonyIO->error("Fatal import error: " . $e->getMessage());
            return Command::FAILURE;
        }
    }
}