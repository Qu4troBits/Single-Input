<?php

declare(strict_types=1);

namespace App\Domain\Reports;

use App\Domain\Reports\ValueObjects\DrePeriod;

interface DreRepositoryInterface
{
    /**
     * Gera um Demonstrativo de Resultados do Exercício (DRE) para o período especificado
     *
     * @param DrePeriod $period Período do DRE
     * @param string|null $categoryId ID da categoria para filtrar (opcional)
     * @param string $scenario Cenário da projeção (base, optimistic, pessimistic, custom)
     * @return Dre DRE gerado
     */
    public function generate(DrePeriod $period, ?string $categoryId = null, string $scenario = 'base'): Dre;

    /**
     * Gera um DRE consolidado para múltiplos períodos
     *
     * @param array<DrePeriod> $periods Períodos a consolidar
     * @param string|null $categoryId ID da categoria para filtrar (opcional)
     * @param string $scenario Cenário da projeção
     * @return Dre DRE consolidado
     */
    public function generateConsolidated(array $periods, ?string $categoryId = null, string $scenario = 'base'): Dre;

    /**
     * Gera um DRE comparativo entre dois períodos
     *
     * @param DrePeriod $currentPeriod Período atual
     * @param DrePeriod $previousPeriod Período anterior para comparação
     * @param string|null $categoryId ID da categoria para filtrar (opcional)
     * @return Dre DRE comparativo
     */
    public function generateComparative(DrePeriod $currentPeriod, DrePeriod $previousPeriod, ?string $categoryId = null): Dre;

    /**
     * Gera um DRE projetado baseado em dados históricos e tendências
     *
     * @param DrePeriod $period Período da projeção
     * @param int $historicalMonths Número de meses históricos a considerar
     * @param string $scenario Cenário da projeção
     * @return Dre DRE projetado
     */
    public function generateProjected(DrePeriod $period, int $historicalMonths = 12, string $scenario = 'base'): Dre;

    /**
     * Gera um DRE detalhado por categoria
     *
     * @param DrePeriod $period Período do DRE
     * @param string $categoryType Tipo de categoria (revenue, expense)
     * @return Dre DRE detalhado por categoria
     */
    public function generateByCategory(DrePeriod $period, string $categoryType): Dre;

    /**
     * Exporta o DRE para o formato especificado
     *
     * @param Dre $dre DRE a ser exportado
     * @param string $format Formato de exportação (pdf, excel, csv, json)
     * @return string Conteúdo exportado ou caminho do arquivo
     */
    public function export(Dre $dre, string $format): string;

    /**
     * Salva um DRE gerado para referência futura
     *
     * @param Dre $dre DRE a ser salvo
     */
    public function save(Dre $dre): void;

    /**
     * Busca um DRE salvo pelo ID
     *
     * @param string $id ID do DRE
     * @return Dre|null DRE encontrado ou null
     */
    public function findById(string $id): ?Dre;

    /**
     * Lista DREs salvos por período
     *
     * @param DrePeriod $period Período para filtrar
     * @param string|null $scenario Cenário para filtrar (opcional)
     * @return array<Dre> DREs encontrados
     */
    public function findByPeriod(DrePeriod $period, ?string $scenario = null): array;

    /**
     * Remove um DRE salvo
     *
     * @param string $id ID do DRE a ser removido
     */
    public function delete(string $id): void;

    /**
     * Lista todos os DREs salvos
     *
     * @param int $limit Limite de resultados
     * @param int $offset Offset para paginação
     * @return array<Dre> DREs encontrados
     */
    public function findAll(int $limit = 100, int $offset = 0): array;

    /**
     * Gera um DRE com análise de variação (budget vs actual)
     *
     * @param DrePeriod $period Período do DRE
     * @param Dre $budgetDre DRE orçamentário
     * @param Dre $actualDre DRE realizado
     * @return Dre DRE com análise de variação
     */
    public function generateVarianceAnalysis(DrePeriod $period, Dre $budgetDre, Dre $actualDre): Dre;

    /**
     * Gera um DRE com análise de tendência (mês a mês)
     *
     * @param array<DrePeriod> $periods Períodos para análise de tendência
     * @param string|null $categoryId ID da categoria para filtrar (opcional)
     * @return Dre DRE com análise de tendência
     */
    public function generateTrendAnalysis(array $periods, ?string $categoryId = null): Dre;

    /**
     * Gera um DRE com análise de rentabilidade
     *
     * @param DrePeriod $period Período do DRE
     * @param Money $totalAssets Ativo total
     * @param Money $totalEquity Patrimônio líquido
     * @return Dre DRE com análise de rentabilidade
     */
    public function generateProfitabilityAnalysis(DrePeriod $period, Money $totalAssets, Money $totalEquity): Dre;
}