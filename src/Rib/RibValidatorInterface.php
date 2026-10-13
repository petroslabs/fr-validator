<?php

declare(strict_types=1);

namespace PetrosLabs\FrValidator\Rib;

use PetrosLabs\FrValidator\ValidationResult;

interface RibValidatorInterface
{
    public function validate(string $rib): ValidationResult;
}
