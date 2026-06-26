<?php

declare(strict_types=1);

namespace App\Domain\BankReconciliation;

enum ReconciliationStatus: string
{
    case PENDING = 'pending';
    case RECONCILED = 'reconciled';
    case DISCREPANCY = 'discrepancy';
    case ADJUSTED = 'adjusted';

    public function isPending(): bool
    {
        return $this === self::PENDING;
    }

    public function isReconciled(): bool
    {
        return $this === self::RECONCILED;
    }

    public function hasDiscrepancy(): bool
    {
        return $this === self::DISCREPANCY;
    }

    public function isAdjusted(): bool
    {
        return $this === self::ADJUSTED;
    }

    public function canBeReconciled(): bool
    {
        return $this === self::PENDING || $this === self::DISCREPANCY;
    }

    public function canBeAdjusted(): bool
    {
        return $this === self::DISCREPANCY;
    }

    public function getLabel(): string
    {
        return match ($this) {
            self::PENDING => 'Pendente',
            self::RECONCILED => 'Conciliado',
            self::DISCREPANCY => 'Divergência',
            self::ADJUSTED => 'Ajustado',
        };
    }
}