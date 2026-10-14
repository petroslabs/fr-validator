<?php

declare(strict_types=1);

namespace PetrosLabs\FrValidator;

/**
 * Common shape implemented by every validator in this package. Each
 * validator also keeps its own narrower interface (NirValidatorInterface,
 * RibValidatorInterface...) extending this one, so callers can still type
 * against the specific one — this exists for code that genuinely needs to
 * treat validators uniformly, such as the Symfony bridge package.
 */
interface ValidatorInterface
{
    public function validate(string $value): ValidationResult;
}
