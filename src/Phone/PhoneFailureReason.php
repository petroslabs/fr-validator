<?php

declare(strict_types=1);

namespace PetrosLabs\FrValidator\Phone;

use PetrosLabs\FrValidator\FailureReason;

enum PhoneFailureReason: string implements FailureReason
{
    case INVALID_LENGTH = 'invalid_length';
    case INVALID_FORMAT = 'invalid_format';
}
