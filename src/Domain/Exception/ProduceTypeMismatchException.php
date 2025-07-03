<?php

namespace App\Domain\Exception;

use App\Domain\Enum\ProduceType;

class ProduceTypeMismatchException extends DomainException
{
    public function __construct(ProduceType $expected, ProduceType $actual)
    {
        parent::__construct(sprintf(
            'Expected type %s, got %s',
            $expected->value,
            $actual->value
        ));
    }

}