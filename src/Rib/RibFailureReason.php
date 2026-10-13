<?php

declare(strict_types=1);

namespace PetrosLabs\FrValidator\Rib;

use PetrosLabs\FrValidator\FailureReason;

enum RibFailureReason: string implements FailureReason
{
    case INVALID_LENGTH = 'invalid_length';
    case INVALID_BANK_CODE = 'invalid_bank_code';
    case INVALID_BRANCH_CODE = 'invalid_branch_code';
    case INVALID_ACCOUNT_NUMBER = 'invalid_account_number';
    case INVALID_FORMAT = 'invalid_format';
    case INVALID_CHECKSUM = 'invalid_checksum';
}
