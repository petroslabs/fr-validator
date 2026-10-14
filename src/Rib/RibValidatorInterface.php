<?php

declare(strict_types=1);

namespace PetrosLabs\FrValidator\Rib;

use PetrosLabs\FrValidator\ValidationResult;
use PetrosLabs\FrValidator\ValidatorInterface;

interface RibValidatorInterface extends ValidatorInterface
{
    public function validate(string $rib): ValidationResult;
}
