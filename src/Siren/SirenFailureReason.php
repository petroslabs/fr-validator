<?php

declare(strict_types=1);

namespace PetrosLabs\FrValidator\Siren;

use PetrosLabs\FrValidator\FailureReason;

enum SirenFailureReason: string implements FailureReason
{
    case INVALID_LENGTH = 'invalid_length';
    case INVALID_FORMAT = 'invalid_format';
    case INVALID_CHECKSUM = 'invalid_checksum';
}
