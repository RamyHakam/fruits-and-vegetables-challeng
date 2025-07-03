<?php

namespace App\Application\DTO;

class ImportResult
{
    public function __construct(
        public int    $importedCount,
        public array  $errors
    ) {}
}
