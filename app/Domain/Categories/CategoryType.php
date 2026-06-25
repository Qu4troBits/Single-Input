<?php

declare(strict_types=1);

namespace App\Domain\Categories;

enum CategoryType: string
{
    case INCOME = 'income';
    case EXPENSE = 'expense';
    case TRANSFER = 'transfer';
}