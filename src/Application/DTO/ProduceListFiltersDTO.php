<?php

namespace App\Application\DTO;

use App\Domain\Enum\ProduceUnitType;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Constraints\Choice;

class ProduceListFiltersDTO
{
    public function __construct(
        #[Assert\GreaterThanOrEqual(0, message: 'Minimum quantity must be at least {{ compared_value }} kg')]
        public ?float $minQuantity = null,

        #[Assert\GreaterThanOrEqual(0, message: 'Maximum quantity must be at least {{ compared_value }} kg')]
        public ?float $maxQuantity = null,

        #[Assert\Length(min: 1, max: 100, normalizer: 'trim')]
        public ?string $name = null,
    ) {
    }
}