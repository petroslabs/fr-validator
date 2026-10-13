<?php

declare(strict_types=1);

namespace PetrosLabs\FrValidator\Nir;

use PetrosLabs\FrValidator\FailureReason;

enum NirFailureReason: string implements FailureReason
{
    case INVALID_LENGTH = 'invalid_length';
    case INVALID_SEX_DIGIT = 'invalid_sex_digit';
    case INVALID_MONTH = 'invalid_month';
    case INVALID_DEPARTMENT_CODE = 'invalid_department_code';
    case INVALID_FORMAT = 'invalid_format';
    case INVALID_CHECKSUM = 'invalid_checksum';
}
