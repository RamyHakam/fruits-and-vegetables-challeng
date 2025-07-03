<?php

namespace App\Tests\Unit\Application\Service;

use App\Application\DTO\ProduceDTO;
use App\Application\Service\ProduceImporter;
use App\Application\Service\ProduceService;
use App\Domain\Enum\ProduceType;
use App\Domain\Enum\ProduceUnitType;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ProduceImporterTest extends TestCase
{
    private ValidatorInterface $validator;
    private ProduceService $produceService;
    private ProduceImporter $importer;

    protected function setUp(): void
    {
        $this->validator = $this->createMock(ValidatorInterface::class);
        $this->produceService = $this->createMock(ProduceService::class);
        $this->importer = new ProduceImporter(
            $this->validator,
            $this->produceService
        );
    }

    public function testImportWithNoDtos(): void
    {
        $result = $this->importer->import([]);
        $this->assertSame(0, $result->importedCount);
        $this->assertEmpty($result->errors);
    }

    public function testImportValidDto(): void
    {
        $dto = new ProduceDTO(id:1, name:'Apple', type:ProduceType::FRUIT, quantity:5, unit:ProduceUnitType::KILOGRAM);
        $violations = new ConstraintViolationList();
        $this->validator->method('validate')->with($dto)->willReturn($violations);

        // Expect service add called once
        $this->produceService->expects($this->once())->method('add')->with($dto);

        $result = $this->importer->import([$dto]);
        $this->assertSame(1, $result->importedCount);
        $this->assertEmpty($result->errors);
    }

    public function testImportInvalidDto(): void
    {
        $dto = new ProduceDTO(id:2, name:'', type:ProduceType::FRUIT, quantity:-1, unit:ProduceUnitType::KILOGRAM);
        // Create violations list
        $violation1 = new ConstraintViolation(
            message: 'Name should not be blank',
            messageTemplate: '',
            parameters: [],
            root: null,
            propertyPath: 'name',
            invalidValue: ''
        );
        $violation2 = new ConstraintViolation(
            message: 'Quantity must be positive',
            messageTemplate: '',
            parameters: [],
            root: null,
            propertyPath: 'quantity',
            invalidValue: -1
        );
        $violations = new ConstraintViolationList([$violation1, $violation2]);
        $this->validator->method('validate')->with($dto)->willReturn($violations);

        // Service add not called
        $this->produceService->expects($this->never())->method('add');

        $result = $this->importer->import([$dto]);
        $this->assertSame(0, $result->importedCount);
        $this->assertCount(2, $result->errors);
        $this->assertStringContainsString('[2] -> name: Name should not be blank', $result->errors[0]);
        $this->assertStringContainsString('[2] -> quantity: Quantity must be positive', $result->errors[1]);
    }
}
