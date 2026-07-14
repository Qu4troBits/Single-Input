<?php

declare(strict_types=1);

namespace App\Domain\Reports;

use App\Domain\Shared\Money;

final readonly class FinancialReportItem
{
    public function __construct(
        private string $code,
        private string $description,
        private Money $amount,
        private ?string $categoryId = null,
        private ?string $categoryName = null,
        private ?string $notes = null,
    ) {
        if (empty($this->code)) {
            throw new \InvalidArgumentException('Report item code cannot be empty.');
        }

        if (empty($this->description)) {
            throw new \InvalidArgumentException('Report item description cannot be empty.');
        }
    }

    public static function createRevenueItem(
        string $code,
        string $description,
        Money $amount,
        ?string $categoryId = null,
        ?string $categoryName = null,
        ?string $notes = null,
    ): self {
        return new self($code, $description, $amount, $categoryId, $categoryName, $notes);
    }

    public static function createExpenseItem(
        string $code,
        string $description,
        Money $amount,
        ?string $categoryId = null,
        ?string $categoryName = null,
        ?string $notes = null,
    ): self {
        return new self($code, $description, $amount, $categoryId, $categoryName, $notes);
    }

    public static function createGrossProfitItem(Money $revenue, Money $costOfGoodsSold): self
    {
        $grossProfit = $revenue->subtract($costOfGoodsSold);
        return new self('GP', 'Lucro Bruto', $grossProfit);
    }

    public static function createOperatingProfitItem(Money $grossProfit, Money $operatingExpenses): self
    {
        $operatingProfit = $grossProfit->subtract($operatingExpenses);
        return new self('OP', 'Lucro Operacional', $operatingProfit);
    }

    public static function createNetProfitItem(Money $operatingProfit, Money $nonOperatingItems, Money $taxes): self
    {
        $netProfit = $operatingProfit->add($nonOperatingItems)->subtract($taxes);
        return new self('NP', 'Lucro Líquido', $netProfit);
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getAmount(): Money
    {
        return $this->amount;
    }

    public function getCategoryId(): ?string
    {
        return $this->categoryId;
    }

    public function getCategoryName(): ?string
    {
        return $this->categoryName;
    }

    public function getNotes(): ?string
    {
        return $this->notes;
    }

    public function isRevenue(): bool
    {
        return str_starts_with($this->code, 'R') || in_array($this->code, ['GP', 'OP', 'NP']);
    }

    public function isExpense(): bool
    {
        return str_starts_with($this->code, 'E') || $this->code === 'COGS';
    }

    public function isProfitItem(): bool
    {
        return in_array($this->code, ['GP', 'OP', 'NP']);
    }

    public function withAmount(Money $newAmount): self
    {
        return new self(
            $this->code,
            $this->description,
            $newAmount,
            $this->categoryId,
            $this->categoryName,
            $this->notes
        );
    }

    public function withNotes(string $notes): self
    {
        return new self(
            $this->code,
            $this->description,
            $this->amount,
            $this->categoryId,
            $this->categoryName,
            $notes
        );
    }

    public function toArray(): array
    {
        return [
            'code' => $this->code,
            'description' => $this->description,
            'amount' => $this->amount->toNumeric(),
            'category_id' => $this->categoryId,
            'category_name' => $this->categoryName,
            'notes' => $this->notes,
        ];
    }
}