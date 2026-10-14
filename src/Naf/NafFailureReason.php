<?php

declare(strict_types=1);

namespace PetrosLabs\FrValidator\Naf;

use PetrosLabs\FrValidator\FailureReason;

enum NafFailureReason: string implements FailureReason
{
    case INVALID_LENGTH = 'invalid_length';
    case INVALID_FORMAT = 'invalid_format';
    case UNKNOWN_CODE = 'unknown_code';
}
