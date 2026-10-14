<?php

declare(strict_types=1);

namespace PetrosLabs\FrValidator\Siren;

use PetrosLabs\FrValidator\ValidationResult;
use PetrosLabs\FrValidator\ValidatorInterface;

interface SirenValidatorInterface extends ValidatorInterface
{
    public function validate(string $siren): ValidationResult;
}
