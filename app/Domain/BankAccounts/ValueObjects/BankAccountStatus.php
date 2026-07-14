<?php

declare(strict_types=1);

namespace App\Domain\BankAccounts\ValueObjects;

enum BankAccountStatus: string
{
    case ACTIVE = 'active';
    case INACTIVE = 'inactive';
    case CLOSED = 'closed';
    case BLOCKED = 'blocked';

    public function label(): string
    {
        return match ($this) {
            self::ACTIVE => 'Ativa',
            self::INACTIVE => 'Inativa',
            self::CLOSED => 'Encerrada',
            self::BLOCKED => 'Bloqueada',
        };
    }

    public function isActive(): bool
    {
        return $this === self::ACTIVE;
    }

    public function canReceiveTransactions(): bool
    {
        return $this === self::ACTIVE;
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
