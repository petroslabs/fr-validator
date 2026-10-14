<?php

declare(strict_types=1);

namespace PetrosLabs\FrValidator\Vat;

use PetrosLabs\FrValidator\ValidationResult;
use PetrosLabs\FrValidator\ValidatorInterface;

interface VatValidatorInterface extends ValidatorInterface
{
    public function validate(string $vat): ValidationResult;
}
