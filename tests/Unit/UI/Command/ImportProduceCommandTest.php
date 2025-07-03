<?php

namespace App\Tests\Unit\UI\Command;

use App\Application\DTO\ImportResult;
use App\Application\DTO\ProduceDTO;
use App\Application\Service\ProduceImporter;
use App\Domain\Enum\ProduceType;
use App\Domain\Enum\ProduceUnitType;
use App\UI\Command\ImportProduceCommand;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Serializer\SerializerInterface;

class ImportProduceCommandTest extends TestCase
{
    private string $tempFile;
    private ProduceImporter|MockObject $importer;
    private CommandTester $tester;

    protected function setUp(): void
    {
        parent::setUp();
        $this->tempFile = sys_get_temp_dir() . '/produce_' . uniqid() . '.json';
        $this->importer = $this->createMock(ProduceImporter::class);
        $this->serializer = $this->createMock(SerializerInterface::class);

        $command = new ImportProduceCommand($this->serializer, $this->importer);
        $this->tester = new CommandTester($command);
    }
    public function testExecuteWithMissingFile(): void
    {
        $this->importer->expects($this->never())->method('import');
        $this->serializer->expects($this->never())->method('deserialize');

        $status = $this->tester->execute(['file' => '/nonexistent.json']);

        $this->assertSame(Command::FAILURE, $status);
        $this->assertStringContainsString('File not found', $this->tester->getDisplay());
    }

    public function testExecuteWithValidFile(): void
    {
        // Prepare JSON and DTO
        $jsonData = '[{"id":1,"name":"Apple","type":"fruit","quantity":1,"unit":"kg"}]';
        file_put_contents($this->tempFile, $jsonData);

        $dto = new ProduceDTO(id:1, name:'Apple', type: ProduceType::FRUIT, quantity:1, unit:ProduceUnitType::KILOGRAM);
        $this->serializer
            ->expects($this->once())
            ->method('deserialize')
            ->with($jsonData, ProduceDTO::class . '[]', 'json')
            ->willReturn([$dto]);

        $this->importer
            ->expects($this->once())
            ->method('import')
            ->with([$dto])
            ->willReturn(new ImportResult(importedCount:1, errors: []));

        $status = $this->tester->execute(['file' => $this->tempFile]);

        $this->assertSame(Command::SUCCESS, $status);
        $this->assertStringContainsString('Imported 1 items without errors.', $this->tester->getDisplay());
    }

    public function testExecuteWithInvalidEntries(): void
    {
        $jsonData = '[{"id":1,"name":"Apple","type":"fruit","quantity":-1,"unit":"kg"}]';
        file_put_contents($this->tempFile, $jsonData);

        $dto = new ProduceDTO(id:1, name:'Apple', type:ProduceType::FRUIT, quantity:-1, unit:ProduceUnitType::KILOGRAM);
        $this->serializer
            ->expects($this->once())
            ->method('deserialize')
            ->with($jsonData, ProduceDTO::class . '[]', 'json')
            ->willReturn([$dto]);

        $errors = ['Quantity must be a positive number', 'produce unit type not found'];
        $this->importer
            ->expects($this->once())
            ->method('import')
            ->with([$dto])
            ->willReturn(new ImportResult(importedCount:0, errors:$errors));

        $status = $this->tester->execute(['file' => $this->tempFile]);

        $this->assertSame(Command::SUCCESS, $status);
        $output = $this->tester->getDisplay();
        $this->assertStringContainsString('Imported 0 items, but found 2 invalid entries:', $output);
        foreach ($errors as $error) {
            $this->assertStringContainsString($error, $output);
        }
    }

    public function testExecuteWithFatalError(): void
    {
        $jsonData = '[{"id":1,"name":"Apple","type":"fruit","quantity":1,"unit":"kg"}]';
        file_put_contents($this->tempFile, $jsonData);

        $dto = new ProduceDTO(id:1, name:'Apple', type:ProduceType::FRUIT, quantity:1, unit:ProduceUnitType::KILOGRAM);
        $this->serializer
            ->expects($this->once())
            ->method('deserialize')
            ->willReturn([$dto]);

        $this->importer
            ->expects($this->once())
            ->method('import')
            ->with([$dto])
            ->willThrowException(new \RuntimeException('Fatal error occurred'));

        $status = $this->tester->execute(['file' => $this->tempFile]);

        $this->assertSame(Command::FAILURE, $status);
        $this->assertStringContainsString('Fatal import error: Fatal error occurred', $this->tester->getDisplay());
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        if (file_exists($this->tempFile)) {
            unlink($this->tempFile);
        }
    }
}
