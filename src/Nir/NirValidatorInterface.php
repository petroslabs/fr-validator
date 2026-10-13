<?php

declare(strict_types=1);

namespace PetrosLabs\FrValidator\Nir;

use PetrosLabs\FrValidator\ValidationResult;

interface NirValidatorInterface
{
    public function validate(string $nir): ValidationResult;
}
