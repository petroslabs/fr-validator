<?php

declare(strict_types=1);

namespace PetrosLabs\FrValidator\Siret;

use PetrosLabs\FrValidator\FailureReason;

enum SiretFailureReason: string implements FailureReason
{
    case INVALID_LENGTH = 'invalid_length';
    case INVALID_FORMAT = 'invalid_format';
    case INVALID_SIREN_CHECKSUM = 'invalid_siren_checksum';
    case INVALID_CHECKSUM = 'invalid_checksum';
}
