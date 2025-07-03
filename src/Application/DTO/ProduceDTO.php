<?php

declare(strict_types=1);

namespace App\Application\DTO;

use App\Domain\Enum\ProduceType;
use App\Domain\Enum\ProduceUnitType;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

final class ProduceDTO
{
    public function __construct(
        #[Assert\Positive(message: 'ID must be a positive number')]
        #[Groups(['produce:read', 'produce:write'])]
        public ?int $id = null,

        #[Assert\NotBlank]
        #[Groups(['produce:read', 'produce:write'])]
        #[Assert\Regex(
            pattern: '/^[\p{L} ]+$/u',
            message: 'Name may only contain letters and spaces.'
        )]
        public string $name,

        #[Assert\Choice(callback: [ProduceType::class, 'cases'], message: 'The given produce type is not valid')]
        #[Groups(['produce:read', 'produce:write'])]
        public ProduceType $type,

        #[Assert\NotNull]
        #[Assert\Positive(message: 'Quantity must be a positive number')]
        #[Groups(['produce:read', 'produce:write'])]
        public float $quantity,

        #[Assert\Choice(callback: [ProduceUnitType::class, 'cases'], message: 'The given produce unit is not valid')]
        #[Groups(['produce:read', 'produce:write'])]
        public ProduceUnitType $unit,
    ) {}

    public function toArray(): array
    {
        return [
            'id'       => $this->id,
            'name'     => $this->name,
            'type'     => $this->type,
            'quantity' => $this->quantity,
            'unit'     => $this->unit,
        ];
    }
}
