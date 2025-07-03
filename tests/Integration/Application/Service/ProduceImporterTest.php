<?php

namespace App\Tests\Integration\Application\Service;

use App\Application\DTO\ImportResult;
use App\Application\DTO\ProduceDTO;
use App\Application\DTO\ProduceListFiltersDTO;
use App\Application\Service\ProduceImporter;
use App\Application\Service\ProduceService;
use App\Domain\Enum\ProduceType;
use App\Domain\Enum\ProduceUnitType;
use App\Domain\Model\Produce;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class ProduceImporterTest extends  KernelTestCase
{
    private ProduceImporter $importer;
    private ProduceService $produceService;

    protected function setUp(): void
    {
        parent::setUp();
        self::bootKernel();

        $container = static::getContainer();
        $this->importer = $container->get(ProduceImporter::class);
        $this->produceService = $container->get(ProduceService::class);
    }

    public function testServiceIsAvailable(): void
    {
        $service = static ::getContainer()->get(ProduceService::class);
        $this->assertNotNull($service);
    }
    public function testImportSingleValidDto(): void
    {
        // Arrange: a single valid DTO
        $dto = new ProduceDTO(
            id: 1,
            name: 'TestApple',
            type: ProduceType::FRUIT,
            quantity: 1.2,
            unit: ProduceUnitType::KILOGRAM
        );

        // Act
        $result = $this->importer->import([$dto]);

        // Assert ImportResult
        $this->assertInstanceOf(ImportResult::class, $result);
        $this->assertSame(1, $result->importedCount);
        $this->assertEmpty($result->errors);

        // Assert ProduceService has stored one Produce
        $produces = $this->produceService->list(ProduceType::FRUIT, new ProduceListFiltersDTO());
        $this->assertCount(1, $produces);
        $this->assertInstanceOf(Produce::class, $produces[0]);
        $this->assertSame('TestApple', $produces[0]->getName());
    }

    public function testImportValidAndInvalidDtos(): void
    {
        $valid = new ProduceDTO(
            id: 2,
            name: 'ValidBanana',
            type: ProduceType::FRUIT,
            quantity: 500,
            unit: ProduceUnitType::GRAM
        );
        $invalid = new ProduceDTO(
            id: -5,
            name: '',
            type: ProduceType::FRUIT,
            quantity: -10.0,
            unit: ProduceUnitType::GRAM
        );

        $result = $this->importer->import([$valid, $invalid]);

        $this->assertSame(1, $result->importedCount);
        $this->assertNotEmpty($result->errors);

        $this->assertStringContainsString(' id: ', implode(' ', $result->errors));
        $this->assertStringContainsString('name:', implode(' ', $result->errors));
        $this->assertStringContainsString('quantity:', implode(' ', $result->errors));


        $produces = $this->produceService->list(ProduceType::FRUIT, new ProduceListFiltersDTO());
        $this->assertCount(1, $produces);
        $this->assertSame('ValidBanana', $produces[0]->getName());
    }

    public function testImportEmptyArrayProducesNoErrors(): void
    {
        $result = $this->importer->import([]);

        $this->assertSame(0, $result->importedCount);
        $this->assertEmpty($result->errors);
    }
}