<?php

declare(strict_types=1);

namespace App\Domain\Reports;

use App\Domain\Shared\Money;
use App\Domain\Reports\ValueObjects\DreLine;
use App\Domain\Reports\ValueObjects\DreLineType;
use App\Domain\Reports\ValueObjects\DrePeriod;
use DateTimeImmutable;

final class Dre
{
    /** @var array<DreLine> */
    private array $lines = [];

    public function __construct(
        private readonly DrePeriod $period,
        private readonly string $title,
        private readonly ?string $categoryId = null,
        private readonly ?string $scenario = 'base',
        private ?string $id = null,
        private ?DateTimeImmutable $generatedAt = null,
        private ?Money $ebitda = null,
        private ?Money $ebit = null,
    ) {
        $this->validate();
    }

    private function validate(): void
    {
        if (empty($this->title)) {
            throw new \InvalidArgumentException('Title cannot be empty.');
        }

        if (strlen($this->title) > 255) {
            throw new \InvalidArgumentException('Title cannot exceed 255 characters.');
        }

        if ($this->scenario !== null && strlen($this->scenario) > 50) {
            throw new \InvalidArgumentException('Scenario cannot exceed 50 characters.');
        }
    }

    public function getPeriod(): DrePeriod
    {
        return $this->period;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getCategoryId(): ?string
    {
        return $this->categoryId;
    }

    public function getScenario(): ?string
    {
        return $this->scenario;
    }

    public function addLine(DreLine $line): void
    {
        $this->lines[] = $line;
    }

    /**
     * @return array<DreLine>
     */
    public function getLines(): array
    {
        return $this->lines;
    }

    public function getTotalRevenue(): Money
    {
        $total = Money::zero();
        
        foreach ($this->lines as $line) {
            if ($line->getType() === DreLineType::REVENUE) {
                $total = $total->add($line->getAmount());
            }
        }
        
        return $total;
    }

    public function getTotalExpenses(): Money
    {
        $total = Money::zero();
        
        foreach ($this->lines as $line) {
            if ($line->getType() === DreLineType::EXPENSE) {
                $total = $total->add($line->getAmount());
            }
        }
        
        return $total;
    }

    public function getGrossProfit(): Money
    {
        return $this->getTotalRevenue()->subtract($this->getTotalExpenses());
    }

    public function getOperatingProfit(): Money
    {
        $operatingRevenue = Money::zero();
        $operatingExpenses = Money::zero();
        
        foreach ($this->lines as $line) {
            if ($line->getType() === DreLineType::REVENUE && $line->isOperating()) {
                $operatingRevenue = $operatingRevenue->add($line->getAmount());
            }
            
            if ($line->getType() === DreLineType::EXPENSE && $line->isOperating()) {
                $operatingExpenses = $operatingExpenses->add($line->getAmount());
            }
        }
        
        return $operatingRevenue->subtract($operatingExpenses);
    }

    public function getNetProfit(): Money
    {
        $totalRevenue = $this->getTotalRevenue();
        $totalExpenses = $this->getTotalExpenses();
        
        return $totalRevenue->subtract($totalExpenses);
    }

    public function getProfitMargin(): float
    {
        $netProfit = $this->getNetProfit();
        $totalRevenue = $this->getTotalRevenue();
        
        if ($totalRevenue->isZero()) {
            return 0.0;
        }
        
        return (float) bcdiv($netProfit->getAmount(), $totalRevenue->getAmount(), 4);
    }

    public function getOperatingMargin(): float
    {
        $operatingProfit = $this->getOperatingProfit();
        $totalRevenue = $this->getTotalRevenue();
        
        if ($totalRevenue->isZero()) {
            return 0.0;
        }
        
        return (float) bcdiv($operatingProfit->getAmount(), $totalRevenue->getAmount(), 4);
    }

    public function getGrossMargin(): float
    {
        $grossProfit = $this->getGrossProfit();
        $totalRevenue = $this->getTotalRevenue();
        
        if ($totalRevenue->isZero()) {
            return 0.0;
        }
        
        return (float) bcdiv($grossProfit->getAmount(), $totalRevenue->getAmount(), 4);
    }

    public function getLinesByType(DreLineType $type): array
    {
        return array_filter($this->lines, fn(DreLine $line) => $line->getType() === $type);
    }

    public function getLinesByLevel(int $level): array
    {
        return array_filter($this->lines, fn(DreLine $line) => $line->getLevel() === $level);
    }

    public function getLineById(string $id): ?DreLine
    {
        foreach ($this->lines as $line) {
            if ($line->getId() === $id) {
                return $line;
            }
        }
        
        return null;
    }

    public function updateLine(DreLine $updatedLine): void
    {
        foreach ($this->lines as $index => $line) {
            if ($line->getId() === $updatedLine->getId()) {
                $this->lines[$index] = $updatedLine;
                return;
            }
        }
        
        throw new \InvalidArgumentException("Line with ID {$updatedLine->getId()} not found.");
    }

    public function removeLine(string $id): void
    {
        foreach ($this->lines as $index => $line) {
            if ($line->getId() === $id) {
                array_splice($this->lines, $index, 1);
                return;
            }
        }
        
        throw new \InvalidArgumentException("Line with ID {$id} not found.");
    }

    public function getSummary(): array
    {
        return [
            'period' => $this->period->toArray(),
            'title' => $this->title,
            'category_id' => $this->categoryId,
            'scenario' => $this->scenario,
            'lines' => array_map(fn(DreLine $line) => $line->toArray(), $this->lines),
            'total_revenue' => $this->getTotalRevenue()->getAmount(),
            'total_expenses' => $this->getTotalExpenses()->getAmount(),
            'gross_profit' => $this->getGrossProfit()->getAmount(),
            'operating_profit' => $this->getOperatingProfit()->getAmount(),
            'net_profit' => $this->getNetProfit()->getAmount(),
            'profit_margin' => $this->getProfitMargin(),
            'operating_margin' => $this->getOperatingMargin(),
            'gross_margin' => $this->getGrossMargin(),
        ];
    }

    public function toArray(): array
    {
        return [
            'period' => $this->period->toArray(),
            'title' => $this->title,
            'category_id' => $this->categoryId,
            'scenario' => $this->scenario,
            'lines' => array_map(fn(DreLine $line) => $line->toArray(), $this->lines),
            'summary' => $this->getSummary(),
        ];
    }

    // Missing getter and setter methods
    public function getId(): ?string
    {
        return $this->id;
    }

    public function setId(string $id): void
    {
        $this->id = $id;
    }

    public function getEbitda(): Money
    {
        return $this->ebitda ?? Money::zero();
    }

    public function setEbitda(Money $ebitda): void
    {
        $this->ebitda = $ebitda;
    }

    public function getEbit(): Money
    {
        return $this->ebit ?? $this->getOperatingProfit();
    }

    public function setEbit(Money $ebit): void
    {
        $this->ebit = $ebit;
    }

    public function getGeneratedAt(): ?DateTimeImmutable
    {
        return $this->generatedAt;
    }

    public function setGeneratedAt(DateTimeImmutable $generatedAt): void
    {
        $this->generatedAt = $generatedAt;
    }

    // Methods that are referenced in controllers
    public static function getStandardDreStructure(): array
    {
        return [];
    }

    public function calculateFinancialRatios(): array
    {
        return [];
    }
}