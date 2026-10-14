<?php

declare(strict_types=1);

namespace PetrosLabs\FrValidator\Siret;

use PetrosLabs\FrValidator\ValidationResult;

interface SiretValidatorInterface
{
    public function validate(string $siret): ValidationResult;
}
