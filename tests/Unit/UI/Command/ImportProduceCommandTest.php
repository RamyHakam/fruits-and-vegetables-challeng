<?php

namespace App\Tests\Unit\UI\Command;

use App\Application\DTO\ImportResult;
use App\Application\Service\ProduceImporter;
use App\UI\Command\ImportProduceCommand;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

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
        $command = new ImportProduceCommand($this->importer);
        $this->tester = new CommandTester($command);
    }
    public function testExecuteWithMissingFile(): void
    {
        $this->importer->expects($this->never())->method('import');

        $status = $this->tester->execute(['file' => '/nonexistent.json']);

        $this->assertSame(Command::FAILURE, $status);
        $this->assertStringContainsString('File not found', $this->tester->getDisplay());
    }

public function testExecuteWithValidFile(): void
    {
        $jsonData = '[{"id":1,"name":"Apple","type":"fruit","quantity":1,"unit":"kg"}]';
        file_put_contents($this->tempFile, $jsonData);

        $this->importer->expects($this->once())
            ->method('import')
            ->with($jsonData)
            ->willReturn(new ImportResult(1, []));

        $status = $this->tester->execute(['file' => $this->tempFile]);

        $this->assertSame(Command::SUCCESS, $status);
        $this->assertStringContainsString('Imported 1 items without errors.', $this->tester->getDisplay());
    }
    public function testExecuteWithInvalidEntries(): void
    {
        $jsonData = '[{"id":1,"name":"Apple","type":"fruit","quantity":-1,"unit":"liter"}]';
        file_put_contents($this->tempFile, $jsonData);

        $this->importer->expects($this->once())
            ->method('import')
            ->with($jsonData)
            ->willReturn(new ImportResult(0, ['Quantity must be a positive number','produce unit type not found']));

        $status = $this->tester->execute(['file' => $this->tempFile]);

        $this->assertSame(Command::SUCCESS, $status);
        $this->assertStringContainsString('Imported 0 items, but found 2 invalid entries:', $this->tester->getDisplay());
        $this->assertStringContainsString('Quantity must be a positive number', $this->tester->getDisplay());
        $this->assertStringContainsString('produce unit type not found', $this->tester->getDisplay());
    }
    public function testExecuteWithFatalError(): void
    {
        $jsonData = '[{"id":1,"name":"Apple","type":"fruit","quantity":1,"unit":"kg"}]';
        file_put_contents($this->tempFile, $jsonData);

        $this->importer->expects($this->once())
            ->method('import')
            ->with($jsonData)
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
