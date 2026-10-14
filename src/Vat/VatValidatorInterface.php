<?php

declare(strict_types=1);

namespace PetrosLabs\FrValidator\Vat;

use PetrosLabs\FrValidator\ValidationResult;

interface VatValidatorInterface
{
    public function validate(string $vat): ValidationResult;
}
