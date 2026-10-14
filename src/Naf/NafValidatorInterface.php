<?php

declare(strict_types=1);

namespace PetrosLabs\FrValidator\Naf;

use PetrosLabs\FrValidator\ValidationResult;
use PetrosLabs\FrValidator\ValidatorInterface;

interface NafValidatorInterface extends ValidatorInterface
{
    public function validate(string $naf): ValidationResult;
}
