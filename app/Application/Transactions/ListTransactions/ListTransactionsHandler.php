<?php

declare(strict_types=1);

namespace App\Application\Transactions\ListTransactions;

use App\Domain\Transactions\TransactionRepositoryInterface;

final readonly class ListTransactionsHandler
{
    public function __construct(private TransactionRepositoryInterface $transactions)
    {
    }

    public function handle(int $limit = 50): array
    {
        return $this->transactions->listLatest($limit);
    }
}
