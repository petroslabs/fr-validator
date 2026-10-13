<?php

declare(strict_types=1);

namespace PetrosLabs\FrValidator;

final class ValidationResult
{
    private function __construct(
        private readonly bool $valid,
        private readonly ?FailureReason $failureReason,
    ) {
    }

    public static function success(): self
    {
        return new self(true, null);
    }

    public static function failure(FailureReason $reason): self
    {
        return new self(false, $reason);
    }

    public function isValid(): bool
    {
        return $this->valid;
    }

    public function failureReason(): ?FailureReason
    {
        return $this->failureReason;
    }
}
