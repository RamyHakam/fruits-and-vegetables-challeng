<?php

namespace App\Tests\Unit\Application\Service;

use App\Application\DTO\ProduceDTO;
use App\Application\Service\ProduceImporter;
use App\Application\Service\ProduceService;
use App\Domain\Enum\ProduceType;
use App\Domain\Enum\ProduceUnitType;
use App\Domain\Factory\ProduceFactory;
use App\Domain\Model\Produce;
use App\Domain\ValueObject\Quantity;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ProduceImporterTest extends TestCase
{
    private ProduceImporter $importer;
    private SerializerInterface|MockObject $serializer;
    private ValidatorInterface|MockObject $validator;
    private ProduceFactory|MockObject $factory;
    private ProduceService|MockObject $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->serializer = $this->createMock(SerializerInterface::class);
        $this->validator  = $this->createMock(ValidatorInterface::class);
        $this->factory    = $this->createMock(ProduceFactory::class);
       $this->service    = $this->createMock(ProduceService::class);

        $this->importer = new ProduceImporter(
            $this->serializer,
            $this->validator,
            $this->factory,
            $this->service
        );
    }

    public function testImportAllValid(): void
    {
        $json = '[{"id":1,"name":"Apple","type":"fruit","quantity":1,"unit":"kg"}]';
        $dto = new ProduceDTO(
            id: 1,
            name: 'Apple',
            type: ProduceType::FRUIT,
            quantity: 1,
            unit: ProduceUnitType::KILOGRAM
        );

        $this->serializer
            ->expects($this->once())
            ->method('deserialize')
            ->with($json, ProduceDTO::class . '[]', 'json')
            ->willReturn([$dto]);

        $this->validator
            ->method('validate')
            ->with($dto)
            ->willReturn(new ConstraintViolationList());

        $produce = new Produce(
            id: $dto->id,
            name: $dto->name,
            type: $dto->type,
            quantity: new Quantity($dto->quantity, $dto->unit),
            produceUnitType: $dto->unit
        );
        $this->factory
            ->expects($this->once())
            ->method('fromDto')
            ->with($dto)
            ->willReturn($produce);

        $this->service
            ->expects($this->once())
            ->method('add')
            ->with($produce);

        $result = $this->importer->import($json);

        $this->assertSame(1, $result->importedCount);
        $this->assertEmpty($result->errors);
    }

    public function testImportWithViolationsCollectsErrors(): void
    {
        $json = '[]';
        $dto = new ProduceDTO(
            id: 2,
            name: '',
            type: ProduceType::FRUIT,
            quantity: -5,
            unit: ProduceUnitType::GRAM
        );

        $this->serializer
            ->method('deserialize')
            ->willReturn([$dto]);

        $violation = new ConstraintViolation(
            'Cannot be blank', null, [], '', 'name', ''
        );
        $this->validator
            ->method('validate')
            ->with($dto)
            ->willReturn(new ConstraintViolationList([$violation]));

        $this->factory
            ->expects($this->never())
            ->method('fromDto');

        $this->service
            ->expects($this->never())
            ->method('add');

        $result = $this->importer->import($json);

        $this->assertSame(0, $result->importedCount);
        $this->assertNotEmpty($result->errors);
        $this->assertStringContainsString('name: Cannot be blank', $result->errors[0]);
    }

    public function testImportThrowsOnParseError(): void
    {
        $this->serializer
            ->method('deserialize')
            ->willThrowException(new RuntimeException('Parse error'));

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Parse error');

        $this->importer->import('{ invalid json }');
    }
}
