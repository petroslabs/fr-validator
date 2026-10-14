<?php

declare(strict_types=1);

namespace PetrosLabs\FrValidator\Naf;

use PetrosLabs\FrValidator\ValidationResult;

interface NafValidatorInterface
{
    public function validate(string $naf): ValidationResult;
}
