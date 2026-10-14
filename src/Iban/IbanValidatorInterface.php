<?php

declare(strict_types=1);

namespace PetrosLabs\FrValidator\Iban;

use PetrosLabs\FrValidator\ValidationResult;

interface IbanValidatorInterface
{
    public function validate(string $iban): ValidationResult;
}
