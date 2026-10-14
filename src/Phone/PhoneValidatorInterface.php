<?php

declare(strict_types=1);

namespace PetrosLabs\FrValidator\Phone;

use PetrosLabs\FrValidator\ValidationResult;

interface PhoneValidatorInterface
{
    public function validate(string $phone): ValidationResult;
}
