<?php

declare(strict_types=1);

namespace App\Domain\BankAccounts\ValueObjects;

enum BankAccountType: string
{
    case CHECKING = 'checking';
    case SAVINGS = 'savings';
    case INVESTMENT = 'investment';
    case CREDIT_CARD = 'credit_card';
    case WALLET = 'wallet';
    case OTHER = 'other';

    public function label(): string
    {
        return match ($this) {
            self::CHECKING => 'Conta Corrente',
            self::SAVINGS => 'Conta Poupança',
            self::INVESTMENT => 'Conta Investimento',
            self::CREDIT_CARD => 'Cartão de Crédito',
            self::WALLET => 'Carteira Digital',
            self::OTHER => 'Outro',
        };
    }

    public function isCreditCard(): bool
    {
        return $this === self::CREDIT_CARD;
    }

    public function isDebitAccount(): bool
    {
        return in_array($this, [self::CHECKING, self::SAVINGS, self::WALLET]);
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
