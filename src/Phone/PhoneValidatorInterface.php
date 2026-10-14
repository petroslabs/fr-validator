<?php

declare(strict_types=1);

namespace PetrosLabs\FrValidator\Phone;

use PetrosLabs\FrValidator\ValidationResult;
use PetrosLabs\FrValidator\ValidatorInterface;

interface PhoneValidatorInterface extends ValidatorInterface
{
    public function validate(string $phone): ValidationResult;
}
