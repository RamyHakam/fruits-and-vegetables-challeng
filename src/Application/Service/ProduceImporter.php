<?php

namespace App\Application\Service;

use App\Application\DTO\ImportResult;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final  readonly class ProduceImporter
{
    public function __construct(
        private ValidatorInterface  $validator,
        private ProduceService $produceService
    ) {}

    public function import(array $produceDTOs): ImportResult
    {
        $errors = [];
        $importedCount = 0;

        foreach ($produceDTOs as $dto) {
            $violations = $this->validator->validate($dto);

            if (count($violations) > 0) {
                foreach ($violations as $v) {
                    $errors[] = sprintf(
                        '[%s] %s: %s',
                        $dto->id ?? 'n/a',
                        $v->getPropertyPath(),
                        $v->getMessage()
                    );
                }
                continue;
            }

            $this->produceService->add($dto);
            $importedCount++;
        }

        return new ImportResult($importedCount, $errors);
    }
}