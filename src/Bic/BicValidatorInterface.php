<?php

declare(strict_types=1);

namespace PetrosLabs\FrValidator\Bic;

use PetrosLabs\FrValidator\ValidationResult;
use PetrosLabs\FrValidator\ValidatorInterface;

interface BicValidatorInterface extends ValidatorInterface
{
    public function validate(string $bic): ValidationResult;
}
