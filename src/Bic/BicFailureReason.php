<?php

declare(strict_types=1);

namespace PetrosLabs\FrValidator\Bic;

use PetrosLabs\FrValidator\FailureReason;

enum BicFailureReason: string implements FailureReason
{
    case INVALID_LENGTH = 'invalid_length';
    case INVALID_BANK_CODE = 'invalid_bank_code';
    case INVALID_COUNTRY_CODE = 'invalid_country_code';
    case INVALID_LOCATION_CODE = 'invalid_location_code';
    case INVALID_BRANCH_CODE = 'invalid_branch_code';
}
