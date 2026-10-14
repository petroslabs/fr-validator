<?php

declare(strict_types=1);

namespace PetrosLabs\FrValidator\Nir;

use PetrosLabs\FrValidator\ValidationResult;
use PetrosLabs\FrValidator\ValidatorInterface;

interface NirValidatorInterface extends ValidatorInterface
{
    public function validate(string $nir): ValidationResult;
}
