<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Reports\Services;

use App\Domain\Reports\Dre;
use App\Domain\Reports\DreGenerator;
use App\Domain\Reports\DreRepositoryInterface;
use App\Domain\Reports\ValueObjects\DrePeriod;
use App\Domain\Reports\DreLineType;
use App\Domain\Shared\Money;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;

final class DreGeneratorTest extends TestCase
{
    use ProphecyTrait;

    private ObjectProphecy $dreRepository;
    private DreGenerator $generator;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->dreRepository = $this->prophesize(DreRepositoryInterface::class);
        $this->generator = new DreGenerator($this->dreRepository->reveal());
    }

    public function test_generate_monthly_dre(): void
    {
        $period = DrePeriod::createMonthly('2024-01');
        
        $dre = $this->prophesize(Dre::class);
        $dre->getPeriod()->willReturn($period);
        $dre->getTitle()->willReturn('Demonstrativo de Resultados do Exercício - janeiro/2024');
        $dre->getCategoryId()->willReturn(null);
        $dre->getScenario()->willReturn('base');
        
        $this->dreRepository->generate($period, null, 'base')
            ->willReturn($dre->reveal())
            ->shouldBeCalled();

        $result = $this->generator->generate($period);

        $this->assertInstanceOf(Dre::class, $result);
        $this->assertEquals($period, $result->getPeriod());
        $this->assertEquals('Demonstrativo de Resultados do Exercício - janeiro/2024', $result->getTitle());
        $this->assertNull($result->getCategoryId());
        $this->assertEquals('base', $result->getScenario());
    }

    public function test_generate_dre_with_category(): void
    {
        $period = DrePeriod::createMonthly('2024-01');
        $categoryId = 'category-123';
        
        $dre = $this->prophesize(Dre::class);
        $dre->getPeriod()->willReturn($period);
        $dre->getTitle()->willReturn('Demonstrativo de Resultados do Exercício - janeiro/2024 - Vendas');
        $dre->getCategoryId()->willReturn($categoryId);
        $dre->getScenario()->willReturn('base');
        
        $this->dreRepository->generate($period, $categoryId, 'base')
            ->willReturn($dre->reveal())
            ->shouldBeCalled();

        $result = $this->generator->generate($period, $categoryId);

        $this->assertInstanceOf(Dre::class, $result);
        $this->assertEquals($period, $result->getPeriod());
        $this->assertEquals('Demonstrativo de Resultados do Exercício - janeiro/2024 - Vendas', $result->getTitle());
        $this->assertEquals($categoryId, $result->getCategoryId());
        $this->assertEquals('base', $result->getScenario());
    }

    public function test_generate_dre_with_scenario(): void
    {
        $period = DrePeriod::createMonthly('2024-01');
        $scenario = 'optimistic';
        
        $dre = $this->prophesize(Dre::class);
        $dre->getPeriod()->willReturn($period);
        $dre->getTitle()->willReturn('Demonstrativo de Resultados do Exercício - janeiro/2024 - Cenário: Otimista');
        $dre->getCategoryId()->willReturn(null);
        $dre->getScenario()->willReturn($scenario);
        
        $this->dreRepository->generate($period, null, $scenario)
            ->willReturn($dre->reveal())
            ->shouldBeCalled();

        $result = $this->generator->generate($period, null, $scenario);

        $this->assertInstanceOf(Dre::class, $result);
        $this->assertEquals($period, $result->getPeriod());
        $this->assertEquals('Demonstrativo de Resultados do Exercício - janeiro/2024 - Cenário: Otimista', $result->getTitle());
        $this->assertNull($result->getCategoryId());
        $this->assertEquals($scenario, $result->getScenario());
    }

    public function test_generate_consolidated_dre(): void
    {
        $periods = [
            DrePeriod::createMonthly('2024-01'),
            DrePeriod::createMonthly('2024-02'),
        ];
        
        $dre = $this->prophesize(Dre::class);
        $dre->getPeriod()->willReturn(new DrePeriod(
            new \DateTimeImmutable('2024-01-01'),
            new \DateTimeImmutable('2024-02-29'),
            'consolidated'
        ));
        $dre->getTitle()->willReturn('DRE Consolidado - 01/01/2024 a 29/02/2024 (2 períodos)');
        $dre->getCategoryId()->willReturn(null);
        $dre->getScenario()->willReturn('base');
        
        $this->dreRepository->generateConsolidated($periods, null, 'base')
            ->willReturn($dre->reveal())
            ->shouldBeCalled();

        $result = $this->generator->generateConsolidated($periods);

        $this->assertInstanceOf(Dre::class, $result);
        $this->assertEquals('DRE Consolidado - 01/01/2024 a 29/02/2024 (2 períodos)', $result->getTitle());
    }

    public function test_generate_comparative_dre(): void
    {
        $currentPeriod = DrePeriod::createMonthly('2024-02');
        $previousPeriod = DrePeriod::createMonthly('2024-01');
        
        $dre = $this->prophesize(Dre::class);
        $dre->getPeriod()->willReturn($currentPeriod);
        $dre->getTitle()->willReturn('DRE Comparativo - 02/2024 vs 01/2024');
        $dre->getCategoryId()->willReturn(null);
        $dre->getScenario()->willReturn('comparative');
        
        $this->dreRepository->generateComparative($currentPeriod, $previousPeriod, null)
            ->willReturn($dre->reveal())
            ->shouldBeCalled();

        $result = $this->generator->generateComparative($currentPeriod, $previousPeriod);

        $this->assertInstanceOf(Dre::class, $result);
        $this->assertEquals('DRE Comparativo - 02/2024 vs 01/2024', $result->getTitle());
    }

    public function test_generate_projected_dre(): void
    {
        $period = DrePeriod::createMonthly('2024-03');
        $growthRate = 0.10; // 10%
        
        $dre = $this->prophesize(Dre::class);
        $dre->getPeriod()->willReturn($period);
        $dre->getTitle()->willReturn('DRE Projetado - março/2024 - Crescimento: 10%');
        $dre->getCategoryId()->willReturn(null);
        $dre->getScenario()->willReturn('projected');
        
        $this->dreRepository->generate($period, null, 'projected')
            ->willReturn($dre->reveal())
            ->shouldBeCalled();

        $result = $this->generator->generateProjected($period, $growthRate);

        $this->assertInstanceOf(Dre::class, $result);
        $this->assertEquals('DRE Projetado - março/2024 - Crescimento: 10%', $result->getTitle());
    }

    public function test_generate_variance_analysis_dre(): void
    {
        $actualPeriod = DrePeriod::createMonthly('2024-02');
        $budgetPeriod = DrePeriod::createMonthly('2024-02');
        
        $dre = $this->prophesize(Dre::class);
        $dre->getPeriod()->willReturn($actualPeriod);
        $dre->getTitle()->willReturn('Análise de Variação - 02/2024');
        $dre->getCategoryId()->willReturn(null);
        $dre->getScenario()->willReturn('variance');
        
        $this->dreRepository->generate($actualPeriod, null, 'variance')
            ->willReturn($dre->reveal())
            ->shouldBeCalled();

        $result = $this->generator->generateVarianceAnalysis($actualPeriod, $budgetPeriod);

        $this->assertInstanceOf(Dre::class, $result);
        $this->assertEquals('Análise de Variação - 02/2024', $result->getTitle());
    }

    public function test_generate_trend_analysis_dre(): void
    {
        $periods = [
            DrePeriod::createMonthly('2024-01'),
            DrePeriod::createMonthly('2024-02'),
            DrePeriod::createMonthly('2024-03'),
        ];
        
        $dre = $this->prophesize(Dre::class);
        $dre->getPeriod()->willReturn(new DrePeriod(
            new \DateTimeImmutable('2024-01-01'),
            new \DateTimeImmutable('2024-03-31'),
            'trend'
        ));
        $dre->getTitle()->willReturn('Análise de Tendência - 01/01/2024 a 31/03/2024');
        $dre->getCategoryId()->willReturn(null);
        $dre->getScenario()->willReturn('trend');
        
        $this->dreRepository->generateConsolidated($periods, null, 'trend')
            ->willReturn($dre->reveal())
            ->shouldBeCalled();

        $result = $this->generator->generateTrendAnalysis($periods);

        $this->assertInstanceOf(Dre::class, $result);
        $this->assertEquals('Análise de Tendência - 01/01/2024 a 31/03/2024', $result->getTitle());
    }

    public function test_generate_profitability_analysis_dre(): void
    {
        $period = DrePeriod::createMonthly('2024-01');
        
        $dre = $this->prophesize(Dre::class);
        $dre->getPeriod()->willReturn($period);
        $dre->getTitle()->willReturn('Análise de Rentabilidade - janeiro/2024');
        $dre->getCategoryId()->willReturn(null);
        $dre->getScenario()->willReturn('profitability');
        
        $this->dreRepository->generate($period, null, 'profitability')
            ->willReturn($dre->reveal())
            ->shouldBeCalled();

        $result = $this->generator->generateProfitabilityAnalysis($period);

        $this->assertInstanceOf(Dre::class, $result);
        $this->assertEquals('Análise de Rentabilidade - janeiro/2024', $result->getTitle());
    }

    public function test_get_standard_dre_structure(): void
    {
        $structure = $this->generator->getStandardDreStructure();

        $this->assertIsArray($structure);
        $this->assertNotEmpty($structure);
        
        // Verificar elementos básicos da estrutura
        $this->assertArrayHasKey('code', $structure[0]);
        $this->assertArrayHasKey('description', $structure[0]);
        $this->assertArrayHasKey('type', $structure[0]);
        $this->assertArrayHasKey('level', $structure[0]);
        
        // Verificar se contém os principais códigos
        $codes = array_column($structure, 'code');
        $this->assertContains('REV', $codes); // Receita Operacional Bruta
        $this->assertContains('COGS', $codes); // Custo dos Produtos/Serviços Vendidos
        $this->assertContains('GROSS', $codes); // Lucro Bruto
        $this->assertContains('EBIT', $codes); // Lucro Operacional (EBIT)
        $this->assertContains('NET', $codes); // Lucro Líquido
    }

    public function test_calculate_financial_ratios(): void
    {
        $dre = $this->prophesize(Dre::class);
        
        // Configurar valores para cálculo de índices
        $dre->getTotalRevenue()->willReturn(Money::of('10000.00'));
        $dre->getGrossProfit()->willReturn(Money::of('4000.00'));
        $dre->getOperatingProfit()->willReturn(Money::of('2500.00'));
        $dre->getNetProfit()->willReturn(Money::of('1800.00'));
        $dre->getEbitda()->willReturn(Money::of('3000.00'));

        $ratios = $this->generator->calculateFinancialRatios($dre->reveal());

        $this->assertIsArray($ratios);
        $this->assertNotEmpty($ratios);
        
        // Verificar índices básicos
        $this->assertArrayHasKey('gross_margin', $ratios);
        $this->assertArrayHasKey('operating_margin', $ratios);
        $this->assertArrayHasKey('net_margin', $ratios);
        $this->assertArrayHasKey('ebitda_margin', $ratios);
        
        // Verificar cálculos (valores aproximados)
        $this->assertEquals('40.00', $ratios['gross_margin']); // 4000 / 10000 * 100
        $this->assertEquals('25.00', $ratios['operating_margin']); // 2500 / 10000 * 100
        $this->assertEquals('18.00', $ratios['net_margin']); // 1800 / 10000 * 100
        $this->assertEquals('30.00', $ratios['ebitda_margin']); // 3000 / 10000 * 100
    }

    public function test_generate_dre_title(): void
    {
        $period = DrePeriod::createMonthly('2024-01');
        $categoryId = 'category-123';
        $scenario = 'optimistic';
        
        $title = $this->generator->generateDreTitle($period, $categoryId, $scenario);

        $this->assertIsString($title);
        $this->assertNotEmpty($title);
        $this->assertStringContainsString('Demonstrativo de Resultados do Exercício', $title);
        $this->assertStringContainsString('janeiro/2024', $title);
        $this->assertStringContainsString('Cenário: Otimista', $title);
    }

    public function test_generate_dre_title_without_category(): void
    {
        $period = DrePeriod::createMonthly('2024-01');
        $scenario = 'base';
        
        $title = $this->generator->generateDreTitle($period, null, $scenario);

        $this->assertIsString($title);
        $this->assertNotEmpty($title);
        $this->assertStringContainsString('Demonstrativo de Resultados do Exercício', $title);
        $this->assertStringContainsString('janeiro/2024', $title);
        $this->assertStringNotContainsString('Cenário:', $title); // Cenário base não mostra
    }

    public function test_generate_dre_title_with_quarterly_period(): void
    {
        $period = DrePeriod::createQuarterly('2024', 1);
        
        $title = $this->generator->generateDreTitle($period, null, 'base');

        $this->assertIsString($title);
        $this->assertNotEmpty($title);
        $this->assertStringContainsString('Demonstrativo de Resultados do Exercício', $title);
        $this->assertStringContainsString('1º Trimestre 2024', $title);
    }

    public function test_generate_dre_title_with_yearly_period(): void
    {
        $period = DrePeriod::createYearly('2024');
        
        $title = $this->generator->generateDreTitle($period, null, 'base');

        $this->assertIsString($title);
        $this->assertNotEmpty($title);
        $this->assertStringContainsString('Demonstrativo de Resultados do Exercício', $title);
        $this->assertStringContainsString('Ano 2024', $title);
    }

    public function test_generate_dre_title_with_custom_period(): void
    {
        $startDate = new \DateTimeImmutable('2024-01-01');
        $endDate = new \DateTimeImmutable('2024-01-31');
        $period = new DrePeriod($startDate, $endDate, 'custom');
        
        $title = $this->generator->generateDreTitle($period, null, 'base');

        $this->assertIsString($title);
        $this->assertNotEmpty($title);
        $this->assertStringContainsString('Demonstrativo de Resultados do Exercício', $title);
        $this->assertStringContainsString('01/01/2024 a 31/01/2024', $title);
    }
}