<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent\Repositories;

use App\Domain\Reports\Dre;
use App\Domain\Reports\DreRepositoryInterface;
use App\Domain\Reports\ValueObjects\DrePeriod;
use App\Domain\Reports\ValueObjects\DreLine;
use App\Domain\Reports\DreLineType;
use App\Domain\Shared\Money;
use App\Infrastructure\Persistence\Eloquent\Models\DreModel;
use App\Infrastructure\Persistence\Eloquent\Models\DreLineModel;
use App\Infrastructure\Persistence\Eloquent\Models\TransactionModel;
use App\Infrastructure\Persistence\Eloquent\Models\CategoryModel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;

final class EloquentDreRepository implements DreRepositoryInterface
{
    public function generate(DrePeriod $period, ?string $categoryId = null, string $scenario = 'base'): Dre
    {
        // Buscar transações do período
        $transactions = $this->getTransactionsForPeriod($period, $categoryId);
        
        // Gerar linhas do DRE baseado nas transações
        $lines = $this->generateDreLinesFromTransactions($transactions);
        
        // Adicionar linhas de estrutura padrão
        $lines = $this->addStandardDreStructure($lines);
        
        // Calcular totais e subtotais
        $lines = $this->calculateTotals($lines);
        
        // Criar título do DRE
        $title = $this->generateDreTitle($period, $categoryId, $scenario);
        
        // Criar entidade DRE
        $dre = new Dre(
            $period,
            $title,
            $categoryId,
            $scenario
        );
        
        // Adicionar linhas ao DRE
        foreach ($lines as $line) {
            $dre->addLine($line);
        }
        
        return $dre;
    }
    
    public function generateConsolidated(array $periods, ?string $categoryId = null, string $scenario = 'base'): Dre
    {
        if (empty($periods)) {
            throw new \InvalidArgumentException('At least one period is required for consolidated DRE.');
        }
        
        // Gerar DRE para cada período
        $dres = [];
        foreach ($periods as $period) {
            if (!$period instanceof DrePeriod) {
                throw new \InvalidArgumentException('All periods must be instances of DrePeriod.');
            }
            $dres[] = $this->generate($period, $categoryId, $scenario);
        }
        
        // Consolidar períodos
        $consolidatedPeriod = $this->createConsolidatedPeriod($periods);
        $consolidatedLines = $this->consolidateDreLines($dres);
        
        $title = $this->generateConsolidatedTitle($periods, $categoryId, $scenario);
        
        $dre = new Dre(
            $consolidatedPeriod,
            $title,
            $categoryId,
            $scenario
        );
        
        foreach ($consolidatedLines as $line) {
            $dre->addLine($line);
        }
        
        return $dre;
    }
    
    public function generateComparative(DrePeriod $currentPeriod, DrePeriod $previousPeriod, ?string $categoryId = null): Dre
    {
        $currentDre = $this->generate($currentPeriod, $categoryId);
        $previousDre = $this->generate($previousPeriod, $categoryId);
        
        // Criar linhas comparativas
        $comparativeLines = $this->createComparativeLines($currentDre, $previousDre);
        
        $title = $this->generateComparativeTitle($currentPeriod, $previousPeriod, $categoryId);
        
        $dre = new Dre(
            $currentPeriod,
            $title,
            $categoryId,
            'comparative'
        );
        
        foreach ($comparativeLines as $line) {
            $dre->addLine($line);
        }
        
        return $dre;
    }
    
    public function export(Dre $dre, string $format): string
    {
        return match ($format) {
            'pdf' => $this->exportToPdf($dre),
            'excel' => $this->exportToExcel($dre),
            'csv' => $this->exportToCsv($dre),
            'json' => $this->exportToJson($dre),
            default => throw new \InvalidArgumentException("Unsupported export format: {$format}"),
        };
    }
    
    public function save(Dre $dre): void
    {
        DB::transaction(function () use ($dre) {
            // Salvar DRE
            $dreModel = DreModel::create([
                'id' => $dre->getId(),
                'period_start' => $dre->getPeriod()->getStartDate(),
                'period_end' => $dre->getPeriod()->getEndDate(),
                'period_type' => $dre->getPeriod()->getPeriodType(),
                'title' => $dre->getTitle(),
                'category_id' => $dre->getCategoryId(),
                'scenario' => $dre->getScenario(),
                'total_revenue' => $dre->getTotalRevenue()->getAmount(),
                'total_expenses' => $dre->getTotalExpenses()->getAmount(),
                'net_profit' => $dre->getNetProfit()->getAmount(),
                'gross_profit' => $dre->getGrossProfit()->getAmount(),
                'operating_profit' => $dre->getOperatingProfit()->getAmount(),
                'ebitda' => $dre->getEbitda()->getAmount(),
                'ebit' => $dre->getEbit()->getAmount(),
                'generated_at' => now(),
            ]);
            
            // Salvar linhas do DRE
            foreach ($dre->getLines() as $line) {
                DreLineModel::create([
                    'id' => $line->getId(),
                    'dre_id' => $dreModel->id,
                    'code' => $line->getCode(),
                    'description' => $line->getDescription(),
                    'amount' => $line->getAmount()->getAmount(),
                    'type' => $line->getType()->value,
                    'level' => $line->getLevel(),
                    'is_operating' => $line->isOperating(),
                    'parent_id' => $line->getParentId(),
                    'category_id' => $line->getCategoryId(),
                    'category_name' => $line->getCategoryName(),
                    'notes' => $line->getNotes(),
                ]);
            }
        });
    }
    
    public function findById(string $id): ?Dre
    {
        $dreModel = DreModel::with('lines')->find($id);
        
        if (!$dreModel) {
            return null;
        }
        
        return $this->mapToDomain($dreModel);
    }
    
    public function findByPeriod(DrePeriod $period, ?string $categoryId = null, string $scenario = 'base'): ?Dre
    {
        $query = DreModel::with('lines')
            ->where('period_start', $period->getStartDate())
            ->where('period_end', $period->getEndDate())
            ->where('scenario', $scenario);
            
        if ($categoryId) {
            $query->where('category_id', $categoryId);
        }
        
        $dreModel = $query->first();
        
        if (!$dreModel) {
            return null;
        }
        
        return $this->mapToDomain($dreModel);
    }
    
    public function delete(string $id): void
    {
        DreModel::destroy($id);
    }
    
    private function getTransactionsForPeriod(DrePeriod $period, ?string $categoryId = null): Collection
    {
        $query = TransactionModel::whereBetween('competence_month', [
            $period->getStartDate()->format('Y-m'),
            $period->getEndDate()->format('Y-m'),
        ]);
        
        if ($categoryId) {
            $query->where('category_id', $categoryId);
        }
        
        return $query->get();
    }
    
    private function generateDreLinesFromTransactions(Collection $transactions): array
    {
        $lines = [];
        
        // Agrupar transações por categoria
        $groupedByCategory = $transactions->groupBy('category_id');
        
        foreach ($groupedByCategory as $categoryId => $categoryTransactions) {
            $category = CategoryModel::find($categoryId);
            
            // Calcular total por categoria
            $totalAmount = Money::zero();
            foreach ($categoryTransactions as $transaction) {
                $amount = Money::of($transaction->amount);
                if ($transaction->direction === 'expense') {
                    $amount = $amount->multiply(-1);
                }
                $totalAmount = $totalAmount->add($amount);
            }
            
            // Determinar tipo baseado no sinal do total
            $type = $totalAmount->isPositive() 
                ? DreLineType::REVENUE 
                : DreLineType::EXPENSE;
            
            // Criar linha do DRE
            $lines[] = new DreLine(
                id: \Illuminate\Support\Str::uuid()->toString(),
                code: $category?->code ?? 'CAT-' . substr($categoryId, 0, 8),
                description: $category?->name ?? 'Categoria ' . substr($categoryId, 0, 8),
                amount: $totalAmount->absolute(),
                type: $type,
                level: 2,
                isOperating: $category?->is_operating ?? true,
                categoryId: $categoryId,
                categoryName: $category?->name,
                notes: "Baseado em {$categoryTransactions->count()} transações"
            );
        }
        
        return $lines;
    }
    
    private function addStandardDreStructure(array $lines): array
    {
        $standardStructure = [
            // Receita Operacional
            new DreLine(
                id: \Illuminate\Support\Str::uuid()->toString(),
                code: 'REV',
                description: 'RECEITA OPERACIONAL BRUTA',
                amount: Money::zero(),
                type: DreLineType::REVENUE,
                level: 1
            ),
            
            // Deduções da Receita
            new DreLine(
                id: \Illuminate\Support\Str::uuid()->toString(),
                code: 'REV-DED',
                description: '(-) Deduções da Receita',
                amount: Money::zero(),
                type: DreLineType::REVENUE,
                level: 2,
                parentId: 'REV'
            ),
            
            // Receita Operacional Líquida
            new DreLine(
                id: \Illuminate\Support\Str::uuid()->toString(),
                code: 'REV-NET',
                description: 'RECEITA OPERACIONAL LÍQUIDA',
                amount: Money::zero(),
                type: DreLineType::REVENUE,
                level: 1
            ),
            
            // Custo dos Produtos/Serviços Vendidos
            new DreLine(
                id: \Illuminate\Support\Str::uuid()->toString(),
                code: 'COGS',
                description: '(-) Custo dos Produtos/Serviços Vendidos',
                amount: Money::zero(),
                type: DreLineType::EXPENSE,
                level: 1
            ),
            
            // Lucro Bruto
            new DreLine(
                id: \Illuminate\Support\Str::uuid()->toString(),
                code: 'GROSS',
                description: 'LUCRO BRUTO',
                amount: Money::zero(),
                type: DreLineType::PROFIT,
                level: 1
            ),
            
            // Despesas Operacionais
            new DreLine(
                id: \Illuminate\Support\Str::uuid()->toString(),
                code: 'OPEX',
                description: '(-) Despesas Operacionais',
                amount: Money::zero(),
                type: DreLineType::EXPENSE,
                level: 1
            ),
            
            // Lucro Operacional (EBIT)
            new DreLine(
                id: \Illuminate\Support\Str::uuid()->toString(),
                code: 'EBIT',
                description: 'LUCRO OPERACIONAL (EBIT)',
                amount: Money::zero(),
                type: DreLineType::PROFIT,
                level: 1
            ),
            
            // Receitas/Despesas Não Operacionais
            new DreLine(
                id: \Illuminate\Support\Str::uuid()->toString(),
                code: 'NON-OP',
                description: 'Receitas/Despesas Não Operacionais',
                amount: Money::zero(),
                type: DreLineType::REVENUE,
                level: 1
            ),
            
            // Lucro Antes do IR (EBT)
            new DreLine(
                id: \Illuminate\Support\Str::uuid()->toString(),
                code: 'EBT',
                description: 'LUCRO ANTES DO IMPOSTO DE RENDA (EBT)',
                amount: Money::zero(),
                type: DreLineType::PROFIT,
                level: 1
            ),
            
            // Provisão para IR
            new DreLine(
                id: \Illuminate\Support\Str::uuid()->toString(),
                code: 'TAX',
                description: '(-) Provisão para Imposto de Renda',
                amount: Money::zero(),
                type: DreLineType::EXPENSE,
                level: 2,
                parentId: 'EBT'
            ),
            
            // Lucro Líquido
            new DreLine(
                id: \Illuminate\Support\Str::uuid()->toString(),
                code: 'NET',
                description: 'LUCRO LÍQUIDO',
                amount: Money::zero(),
                type: DreLineType::PROFIT,
                level: 1
            ),
        ];
        
        // Combinar linhas padrão com linhas de transações
        return array_merge($standardStructure, $lines);
    }
    
    private function calculateTotals(array $lines): array
    {
        // Agrupar linhas por código para fácil acesso
        $linesByCode = [];
        foreach ($lines as $line) {
            $linesByCode[$line->getCode()] = $line;
        }
        
        // Calcular Receita Operacional Bruta (soma de todas as receitas de nível 2)
        $totalRevenue = Money::zero();
        foreach ($lines as $line) {
            if ($line->getType() === DreLineType::REVENUE && $line->getLevel() === 2) {
                $totalRevenue = $totalRevenue->add($line->getAmount());
            }
        }
        
        // Atualizar linha REV
        if (isset($linesByCode['REV'])) {
            $linesByCode['REV'] = new DreLine(
                id: $linesByCode['REV']->getId(),
                code: 'REV',
                description: 'RECEITA OPERACIONAL BRUTA',
                amount: $totalRevenue,
                type: DreLineType::REVENUE,
                level: 1
            );
        }
        
        // Calcular Receita Operacional Líquida (REV - REV-DED)
        $revenueDeductions = $linesByCode['REV-DED']->getAmount() ?? Money::zero();
        $netRevenue = $totalRevenue->subtract($revenueDeductions);
        
        if (isset($linesByCode['REV-NET'])) {
            $linesByCode['REV-NET'] = new DreLine(
                id: $linesByCode['REV-NET']->getId(),
                code: 'REV-NET',
                description: 'RECEITA OPERACIONAL LÍQUIDA',
                amount: $netRevenue,
                type: DreLineType::REVENUE,
                level: 1
            );
        }
        
        // Calcular Lucro Bruto (REV-NET - COGS)
        $cogs = $linesByCode['COGS']->getAmount() ?? Money::zero();
        $grossProfit = $netRevenue->subtract($cogs);
        
        if (isset($linesByCode['GROSS'])) {
            $linesByCode['GROSS'] = new DreLine(
                id: $linesByCode['GROSS']->getId(),
                code: 'GROSS',
                description: 'LUCRO BRUTO',
                amount: $grossProfit,
                type: DreLineType::PROFIT,
                level: 1
            );
        }
        
        // Calcular Lucro Operacional (GROSS - OPEX)
        $opex = $linesByCode['OPEX']->getAmount() ?? Money::zero();
        $operatingProfit = $grossProfit->subtract($opex);
        
        if (isset($linesByCode['EBIT'])) {
            $linesByCode['EBIT'] = new DreLine(
                id: $linesByCode['EBIT']->getId(),
                code: 'EBIT',
                description: 'LUCRO OPERACIONAL (EBIT)',
                amount: $operatingProfit,
                type: DreLineType::PROFIT,
                level: 1
            );
        }
        
        // Calcular Lucro Antes do IR (EBIT + NON-OP)
        $nonOp = $linesByCode['NON-OP']->getAmount() ?? Money::zero();
        $ebt = $operatingProfit->add($nonOp);
        
        if (isset($linesByCode['EBT'])) {
            $linesByCode['EBT'] = new DreLine(
                id: $linesByCode['EBT']->getId(),
                code: 'EBT',
                description: 'LUCRO ANTES DO IMPOSTO DE RENDA (EBT)',
                amount: $ebt,
                type: DreLineType::PROFIT,
                level: 1
            );
        }
        
        // Calcular Lucro Líquido (EBT - TAX)
        $tax = $linesByCode['TAX']->getAmount() ?? Money::zero();
        $netProfit = $ebt->subtract($tax);
        
        if (isset($linesByCode['NET'])) {
            $linesByCode['NET'] = new DreLine(
                id: $linesByCode['NET']->getId(),
                code: 'NET',
                description: 'LUCRO LÍQUIDO',
                amount: $netProfit,
                type: DreLineType::PROFIT,
                level: 1
            );
        }
        
        return array_values($linesByCode);
    }
    
    private function generateDreTitle(DrePeriod $period, ?string $categoryId, string $scenario): string
    {
        $periodType = $period->getPeriodType();
        $startDate = $period->getStartDate()->format('d/m/Y');
        $endDate = $period->getEndDate()->format('d/m/Y');
        
        $title = "Demonstrativo de Resultados do Exercício";
        
        if ($periodType === 'monthly') {
            $title .= " - {$period->getStartDate()->format('F/Y')}";
        } elseif ($periodType === 'quarterly') {
            $quarter = ceil($period->getStartDate()->format('n') / 3);
            $year = $period->getStartDate()->format('Y');
            $title .= " - {$quarter}º Trimestre {$year}";
        } elseif ($periodType === 'yearly') {
            $title .= " - Ano {$period->getStartDate()->format('Y')}";
        } else {
            $title .= " - {$startDate} a {$endDate}";
        }
        
        if ($categoryId) {
            $category = CategoryModel::find($categoryId);
            if ($category) {
                $title .= " - {$category->name}";
            }
        }
        
        if ($scenario !== 'base') {
            $title .= " - Cenário: " . ucfirst($scenario);
        }
        
        return $title;
    }
    
    private function createConsolidatedPeriod(array $periods): DrePeriod
    {
        $startDates = array_map(fn($p) => $p->getStartDate(), $periods);
        $endDates = array_map(fn($p) => $p->getEndDate(), $periods);
        
        $minStartDate = min($startDates);
        $maxEndDate = max($endDates);
        
        return new DrePeriod($minStartDate, $maxEndDate, 'consolidated');
    }
    
    private function consolidateDreLines(array $dres): array
    {
        $consolidatedLines = [];
        
        // Para cada DRE, somar valores por código de linha
        foreach ($dres as $dre) {
            foreach ($dre->getLines() as $line) {
                $code = $line->getCode();
                
                if (!isset($consolidatedLines[$code])) {
                    $consolidatedLines[$code] = [
                        'id' => \Illuminate\Support\Str::uuid()->toString(),
                        'code' => $code,
                        'description' => $line->getDescription(),
                        'amount' => Money::zero(),
                        'type' => $line->getType(),
                        'level' => $line->getLevel(),
                        'isOperating' => $line->isOperating(),
                        'parentId' => $line->getParentId(),
                        'categoryId' => $line->getCategoryId(),
                        'categoryName' => $line->getCategoryName(),
                        'notes' => 'Consolidado de ' . count($dres) . ' períodos',
                    ];
                }
                
                $consolidatedLines[$code]['amount'] = $consolidatedLines[$code]['amount']->add($line->getAmount());
            }
        }
        
        // Converter para objetos DreLine
        $lines = [];
        foreach ($consolidatedLines as $lineData) {
            $lines[] = new DreLine(
                id: $lineData['id'],
                code: $lineData['code'],
                description: $lineData['description'],
                amount: $lineData['amount'],
                type: $lineData['type'],
                level: $lineData['level'],
                isOperating: $lineData['isOperating'],
                parentId: $lineData['parentId'],
                categoryId: $lineData['categoryId'],
                categoryName: $lineData['categoryName'],
                notes: $lineData['notes'],
            );
        }
        
        return $lines;
    }
    
    private function generateConsolidatedTitle(array $periods, ?string $categoryId, string $scenario): string
    {
        $periodCount = count($periods);
        $firstPeriod = $periods[0];
        $lastPeriod = $periods[$periodCount - 1];
        
        $title = "DRE Consolidado";
        $title .= " - {$firstPeriod->getStartDate()->format('d/m/Y')} a {$lastPeriod->getEndDate()->format('d/m/Y')}";
        $title .= " ({$periodCount} períodos)";
        
        if ($categoryId) {
            $category = CategoryModel::find($categoryId);
            if ($category) {
                $title .= " - {$category->name}";
            }
        }
        
        if ($scenario !== 'base') {
            $title .= " - Cenário: " . ucfirst($scenario);
        }
        
        return $title;
    }
    
    private function createComparativeLines(Dre $currentDre, Dre $previousDre): array
    {
        $comparativeLines = [];
        
        // Para cada linha do DRE atual, encontrar correspondente no anterior
        foreach ($currentDre->getLines() as $currentLine) {
            $previousLine = $this->findMatchingLine($currentLine, $previousDre);
            
            $variation = $previousLine 
                ? $currentLine->getAmount()->subtract($previousLine->getAmount())
                : $currentLine->getAmount();
                
            $variationPercentage = $previousLine && !$previousLine->getAmount()->isZero()
                ? $variation->divide($previousLine->getAmount())->multiply(100)
                : Money::zero();
            
            $comparativeLines[] = new DreLine(
                id: \Illuminate\Support\Str::uuid()->toString(),
                code: $currentLine->getCode() . '-COMP',
                description: $currentLine->getDescription() . ' (Comparativo)',
                amount: $currentLine->getAmount(),
                type: $currentLine->getType(),
                level: $currentLine->getLevel(),
                isOperating: $currentLine->isOperating(),
                parentId: $currentLine->getParentId(),
                categoryId: $currentLine->getCategoryId(),
                categoryName: $currentLine->getCategoryName(),
                notes: "Período atual: {$currentLine->getAmount()->format()} | "
                     . "Período anterior: " . ($previousLine?->getAmount()->format() ?? '0,00') . " | "
                     . "Variação: {$variation->format()} ({$variationPercentage->format()}%)",
            );
        }
        
        return $comparativeLines;
    }
    
    private function findMatchingLine(DreLine $line, Dre $dre): ?DreLine
    {
        foreach ($dre->getLines() as $dreLine) {
            if ($dreLine->getCode() === $line->getCode()) {
                return $dreLine;
            }
        }
        
        return null;
    }
    
    private function generateComparativeTitle(DrePeriod $currentPeriod, DrePeriod $previousPeriod, ?string $categoryId): string
    {
        $title = "DRE Comparativo";
        $title .= " - {$currentPeriod->getStartDate()->format('m/Y')} vs {$previousPeriod->getStartDate()->format('m/Y')}";
        
        if ($categoryId) {
            $category = CategoryModel::find($categoryId);
            if ($category) {
                $title .= " - {$category->name}";
            }
        }
        
        return $title;
    }
    
    private function exportToPdf(Dre $dre): string
    {
        // Implementação básica - em produção usar DomPDF ou similar
        $content = "DRE: {$dre->getTitle()}\n";
        $content .= "Período: {$dre->getPeriod()->getStartDate()->format('d/m/Y')} a {$dre->getPeriod()->getEndDate()->format('d/m/Y')}\n\n";
        
        foreach ($dre->getLines() as $line) {
            $indent = str_repeat('  ', $line->getLevel() - 1);
            $content .= "{$indent}{$line->getCode()} - {$line->getDescription()}: {$line->getAmount()->format()}\n";
        }
        
        $content .= "\nTotal Receita: {$dre->getTotalRevenue()->format()}\n";
        $content .= "Total Despesas: {$dre->getTotalExpenses()->format()}\n";
        $content .= "Lucro Líquido: {$dre->getNetProfit()->format()}\n";
        
        return $content;
    }
    
    private function exportToExcel(Dre $dre): string
    {
        // Implementação básica - em produção usar PhpSpreadsheet
        $lines = [];
        $lines[] = ['DRE', $dre->getTitle()];
        $lines[] = ['Período', "{$dre->getPeriod()->getStartDate()->format('d/m/Y')} a {$dre->getPeriod()->getEndDate()->format('d/m/Y')}"];
        $lines[] = [];
        $lines[] = ['Código', 'Descrição', 'Valor', 'Tipo', 'Nível'];
        
        foreach ($dre->getLines() as $line) {
            $lines[] = [
                $line->getCode(),
                $line->getDescription(),
                $line->getAmount()->getAmount(),
                $line->getType()->value,
                $line->getLevel(),
            ];
        }
        
        $lines[] = [];
        $lines[] = ['Total Receita', $dre->getTotalRevenue()->getAmount()];
        $lines[] = ['Total Despesas', $dre->getTotalExpenses()->getAmount()];
        $lines[] = ['Lucro Líquido', $dre->getNetProfit()->getAmount()];
        
        return json_encode($lines, JSON_PRETTY_PRINT);
    }
    
    private function exportToCsv(Dre $dre): string
    {
        $output = fopen('php://temp', 'r+');
        
        fputcsv($output, ['DRE', $dre->getTitle()]);
        fputcsv($output, ['Período', "{$dre->getPeriod()->getStartDate()->format('d/m/Y')} a {$dre->getPeriod()->getEndDate()->format('d/m/Y')}"]);
        fputcsv($output, []);
        fputcsv($output, ['Código', 'Descrição', 'Valor', 'Tipo', 'Nível']);
        
        foreach ($dre->getLines() as $line) {
            fputcsv($output, [
                $line->getCode(),
                $line->getDescription(),
                $line->getAmount()->getAmount(),
                $line->getType()->value,
                $line->getLevel(),
            ]);
        }
        
        fputcsv($output, []);
        fputcsv($output, ['Total Receita', $dre->getTotalRevenue()->getAmount()]);
        fputcsv($output, ['Total Despesas', $dre->getTotalExpenses()->getAmount()]);
        fputcsv($output, ['Lucro Líquido', $dre->getNetProfit()->getAmount()]);
        
        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);
        
        return $csv;
    }
    
    private function exportToJson(Dre $dre): string
    {
        $data = [
            'dre' => [
                'id' => $dre->getId(),
                'title' => $dre->getTitle(),
                'period' => [
                    'start' => $dre->getPeriod()->getStartDate()->format('Y-m-d'),
                    'end' => $dre->getPeriod()->getEndDate()->format('Y-m-d'),
                    'type' => $dre->getPeriod()->getPeriodType(),
                ],
                'category_id' => $dre->getCategoryId(),
                'scenario' => $dre->getScenario(),
                'totals' => [
                    'revenue' => $dre->getTotalRevenue()->getAmount(),
                    'expenses' => $dre->getTotalExpenses()->getAmount(),
                    'net_profit' => $dre->getNetProfit()->getAmount(),
                    'gross_profit' => $dre->getGrossProfit()->getAmount(),
                    'operating_profit' => $dre->getOperatingProfit()->getAmount(),
                    'ebitda' => $dre->getEbitda()->getAmount(),
                    'ebit' => $dre->getEbit()->getAmount(),
                ],
                'lines' => [],
            ],
        ];
        
        foreach ($dre->getLines() as $line) {
            $data['dre']['lines'][] = [
                'id' => $line->getId(),
                'code' => $line->getCode(),
                'description' => $line->getDescription(),
                'amount' => $line->getAmount()->getAmount(),
                'type' => $line->getType()->value,
                'level' => $line->getLevel(),
                'is_operating' => $line->isOperating(),
                'parent_id' => $line->getParentId(),
                'category_id' => $line->getCategoryId(),
                'category_name' => $line->getCategoryName(),
                'notes' => $line->getNotes(),
            ];
        }
        
        return json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }
    
    private function mapToDomain(DreModel $dreModel): Dre
    {
        $period = new DrePeriod(
            $dreModel->period_start,
            $dreModel->period_end,
            $dreModel->period_type
        );
        
        $dre = new Dre(
            $period,
            $dreModel->title,
            $dreModel->category_id,
            $dreModel->scenario
        );
        
        foreach ($dreModel->lines as $lineModel) {
            $line = new DreLine(
                id: $lineModel->id,
                code: $lineModel->code,
                description: $lineModel->description,
                amount: Money::of($lineModel->amount),
                type: DreLineType::from($lineModel->type),
                level: $lineModel->level,
                isOperating: $lineModel->is_operating,
                parentId: $lineModel->parent_id,
                categoryId: $lineModel->category_id,
                categoryName: $lineModel->category_name,
                notes: $lineModel->notes,
            );
            
            $dre->addLine($line);
        }
        
        return $dre;
    }
}