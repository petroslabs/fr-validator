<?php

declare(strict_types=1);

namespace PetrosLabs\FrValidator\Bic;

use PetrosLabs\FrValidator\ValidationResult;

interface BicValidatorInterface
{
    public function validate(string $bic): ValidationResult;
}
