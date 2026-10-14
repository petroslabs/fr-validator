<?php

declare(strict_types=1);

namespace PetrosLabs\FrValidator\Iban;

use PetrosLabs\FrValidator\ValidationResult;
use PetrosLabs\FrValidator\ValidatorInterface;

interface IbanValidatorInterface extends ValidatorInterface
{
    public function validate(string $iban): ValidationResult;
}
