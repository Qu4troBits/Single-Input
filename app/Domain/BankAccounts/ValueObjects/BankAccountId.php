<?php

declare(strict_types=1);

namespace App\Domain\BankAccounts\ValueObjects;

use App\Domain\Shared\UuidIdentifier;

final class BankAccountId extends UuidIdentifier
{
    public static function getPrefix(): string
    {
        return 'bank_';
    }
}
