<?php

declare(strict_types=1);

namespace PetrosLabs\FrValidator;

/**
 * Marker interface implemented by each validator's own backed enum of
 * failure reasons. Extending BackedEnum (rather than just UnitEnum) makes
 * `->value` statically known as `string` on anything typed as
 * FailureReason, without each caller needing to know the concrete enum.
 */
interface FailureReason extends \BackedEnum
{
}
