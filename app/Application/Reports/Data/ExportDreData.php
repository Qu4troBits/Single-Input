<?php

declare(strict_types=1);

namespace App\Application\Reports\Data;

final readonly class ExportDreData
{
    public function __construct(
        public string $dreId,
        public string $format,
        public ?bool $includeDetails = true,
        public ?bool $includeRatios = true,
        public ?bool $includeCharts = false,
        public ?string $language = 'pt-BR',
        public ?string $currency = 'BRL',
    ) {
        $this->validate();
    }

    private function validate(): void
    {
        if (empty($this->dreId)) {
            throw new \InvalidArgumentException('DRE ID cannot be empty.');
        }

        $validFormats = ['pdf', 'excel', 'csv', 'json'];
        if (!in_array($this->format, $validFormats, true)) {
            throw new \InvalidArgumentException('Invalid export format. Must be one of: ' . implode(', ', $validFormats));
        }

        $validLanguages = ['pt-BR', 'en-US', 'es-ES'];
        if (!in_array($this->language, $validLanguages, true)) {
            throw new \InvalidArgumentException('Invalid language. Must be one of: ' . implode(', ', $validLanguages));
        }

        $validCurrencies = ['BRL', 'USD', 'EUR'];
        if (!in_array($this->currency, $validCurrencies, true)) {
            throw new \InvalidArgumentException('Invalid currency. Must be one of: ' . implode(', ', $validCurrencies));
        }
    }

    public function getDreId(): string
    {
        return $this->dreId;
    }

    public function getFormat(): string
    {
        return $this->format;
    }

    public function getIncludeDetails(): bool
    {
        return $this->includeDetails ?? true;
    }

    public function getIncludeRatios(): bool
    {
        return $this->includeRatios ?? true;
    }

    public function getIncludeCharts(): bool
    {
        return $this->includeCharts ?? false;
    }

    public function getLanguage(): string
    {
        return $this->language ?? 'pt-BR';
    }

    public function getCurrency(): string
    {
        return $this->currency ?? 'BRL';
    }

    public function toArray(): array
    {
        return [
            'dre_id' => $this->dreId,
            'format' => $this->format,
            'include_details' => $this->includeDetails,
            'include_ratios' => $this->includeRatios,
            'include_charts' => $this->includeCharts,
            'language' => $this->language,
            'currency' => $this->currency,
        ];
    }
}