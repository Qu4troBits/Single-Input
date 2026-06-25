<?php

declare(strict_types=1);

namespace App\Domain\BankAccounts;

enum BankAccountType: string
{
    case CHECKING = 'checking';
    case SAVINGS = 'savings';
    case INVESTMENT = 'investment';
    case CREDIT_CARD = 'credit_card';
    case WALLET = 'wallet';
    case OTHER = 'other';
}