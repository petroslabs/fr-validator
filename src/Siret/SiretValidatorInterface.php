<?php

declare(strict_types=1);

namespace PetrosLabs\FrValidator\Siret;

use PetrosLabs\FrValidator\ValidationResult;
use PetrosLabs\FrValidator\ValidatorInterface;

interface SiretValidatorInterface extends ValidatorInterface
{
    public function validate(string $siret): ValidationResult;
}
