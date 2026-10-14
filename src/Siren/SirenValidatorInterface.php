<?php

declare(strict_types=1);

namespace PetrosLabs\FrValidator\Siren;

use PetrosLabs\FrValidator\ValidationResult;

interface SirenValidatorInterface
{
    public function validate(string $siren): ValidationResult;
}
