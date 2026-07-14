<?php

declare(strict_types=1);

namespace Tests\Unit\Infrastructure\Persistence\Eloquent\Repositories;

use App\Domain\Reports\Dre;
use App\Domain\Reports\ValueObjects\DrePeriod;
use App\Domain\Reports\DreLineType;
use App\Infrastructure\Persistence\Eloquent\Repositories\EloquentDreRepository;
use App\Infrastructure\Persistence\Eloquent\Models\TransactionModel;
use App\Infrastructure\Persistence\Eloquent\Models\CategoryModel;
use App\Infrastructure\Persistence\Eloquent\Models\DreModel;
use App\Infrastructure\Persistence\Eloquent\Models\DreLineModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class EloquentDreRepositoryTest extends TestCase 
{
    use RefreshDatabase;

    private EloquentDreRepository $repository;

    protected function setUp(): void
    {
        // Skip tests if PostgreSQL driver is not available
        if (!extension_loaded('pdo_pgsql')) {
            $this->markTestSkipped('PostgreSQL driver is not available');
        }

        parent::setUp();
        
        $this->repository = new EloquentDreRepository();
        
        // Configurar schema de tenant para testes
        $this->setupTenantSchema();
    }

    public function test_generate_dre_for_monthly_period(): void
    {
        // Criar categoria
        $category = CategoryModel::factory()->create([
            'name' => 'Vendas',
            'code' => 'REV001',
            'is_operating' => true,
        ]);

        // Criar transações de receita
        TransactionModel::factory()->count(3)->create([
            'category_id' => $category->id,
            'direction' => 'revenue',
            'amount' => '1000.00',
            'competence_month' => '2024-01',
        ]);

        // Criar transações de despesa
        TransactionModel::factory()->count(2)->create([
            'category_id' => $category->id,
            'direction' => 'expense',
            'amount' => '500.00',
            'competence_month' => '2024-01',
        ]);

        $period = DrePeriod::createMonthly('2024-01');
        $dre = $this->repository->generate($period, $category->id);

        $this->assertInstanceOf(Dre::class, $dre);
        $this->assertEquals('Demonstrativo de Resultados do Exercício - janeiro/2024', $dre->getTitle());
        $this->assertEquals($category->id, $dre->getCategoryId());
        $this->assertEquals('base', $dre->getScenario());
        
        // Verificar totais
        $this->assertEquals('3000.00', $dre->getTotalRevenue()->getAmount()); // 3 * 1000
        $this->assertEquals('1000.00', $dre->getTotalExpenses()->getAmount()); // 2 * 500
        $this->assertEquals('2000.00', $dre->getNetProfit()->getAmount()); // 3000 - 1000
    }

    public function test_generate_dre_for_all_categories(): void
    {
        // Criar múltiplas categorias
        $category1 = CategoryModel::factory()->create([
            'name' => 'Vendas',
            'code' => 'REV001',
            'is_operating' => true,
        ]);
        
        $category2 = CategoryModel::factory()->create([
            'name' => 'Serviços',
            'code' => 'REV002',
            'is_operating' => true,
        ]);

        // Criar transações para diferentes categorias
        TransactionModel::factory()->create([
            'category_id' => $category1->id,
            'direction' => 'revenue',
            'amount' => '1000.00',
            'competence_month' => '2024-01',
        ]);
        
        TransactionModel::factory()->create([
            'category_id' => $category2->id,
            'direction' => 'revenue',
            'amount' => '2000.00',
            'competence_month' => '2024-01',
        ]);

        $period = DrePeriod::createMonthly('2024-01');
        $dre = $this->repository->generate($period);

        $this->assertInstanceOf(Dre::class, $dre);
        $this->assertEquals('Demonstrativo de Resultados do Exercício - janeiro/2024', $dre->getTitle());
        $this->assertNull($dre->getCategoryId());
        
        // Verificar totais (soma de todas as categorias)
        $this->assertEquals('3000.00', $dre->getTotalRevenue()->getAmount()); // 1000 + 2000
    }

    public function test_generate_consolidated_dre(): void
    {
        // Criar categoria
        $category = CategoryModel::factory()->create([
            'name' => 'Vendas',
            'code' => 'REV001',
            'is_operating' => true,
        ]);

        // Criar transações para diferentes meses
        TransactionModel::factory()->create([
            'category_id' => $category->id,
            'direction' => 'revenue',
            'amount' => '1000.00',
            'competence_month' => '2024-01',
        ]);
        
        TransactionModel::factory()->create([
            'category_id' => $category->id,
            'direction' => 'revenue',
            'amount' => '2000.00',
            'competence_month' => '2024-02',
        ]);

        $period1 = DrePeriod::createMonthly('2024-01');
        $period2 = DrePeriod::createMonthly('2024-02');
        
        $dre = $this->repository->generateConsolidated([$period1, $period2], $category->id);

        $this->assertInstanceOf(Dre::class, $dre);
        $this->assertStringContainsString('DRE Consolidado', $dre->getTitle());
        $this->assertStringContainsString('2 períodos', $dre->getTitle());
        
        // Verificar totais consolidados
        $this->assertEquals('3000.00', $dre->getTotalRevenue()->getAmount()); // 1000 + 2000
    }

    public function test_generate_comparative_dre(): void
    {
        // Criar categoria
        $category = CategoryModel::factory()->create([
            'name' => 'Vendas',
            'code' => 'REV001',
            'is_operating' => true,
        ]);

        // Criar transações para diferentes meses
        TransactionModel::factory()->create([
            'category_id' => $category->id,
            'direction' => 'revenue',
            'amount' => '1000.00',
            'competence_month' => '2024-01',
        ]);
        
        TransactionModel::factory()->create([
            'category_id' => $category->id,
            'direction' => 'revenue',
            'amount' => '2000.00',
            'competence_month' => '2024-02',
        ]);

        $currentPeriod = DrePeriod::createMonthly('2024-02');
        $previousPeriod = DrePeriod::createMonthly('2024-01');
        
        $dre = $this->repository->generateComparative($currentPeriod, $previousPeriod, $category->id);

        $this->assertInstanceOf(Dre::class, $dre);
        $this->assertStringContainsString('DRE Comparativo', $dre->getTitle());
        $this->assertStringContainsString('02/2024 vs 01/2024', $dre->getTitle());
    }

    public function test_save_and_find_dre(): void
    {
        $period = DrePeriod::createMonthly('2024-01');
        
        // Gerar DRE
        $dre = $this->repository->generate($period);
        $dreId = $dre->getId();
        
        // Salvar DRE
        $this->repository->save($dre);
        
        // Buscar DRE salvo
        $savedDre = $this->repository->findById($dreId);
        
        $this->assertInstanceOf(Dre::class, $savedDre);
        $this->assertEquals($dreId, $savedDre->getId());
        $this->assertEquals($dre->getTitle(), $savedDre->getTitle());
        $this->assertEquals($dre->getTotalRevenue()->getAmount(), $savedDre->getTotalRevenue()->getAmount());
        $this->assertEquals($dre->getTotalExpenses()->getAmount(), $savedDre->getTotalExpenses()->getAmount());
        $this->assertEquals($dre->getNetProfit()->getAmount(), $savedDre->getNetProfit()->getAmount());
    }

    public function test_find_dre_by_period(): void
    {
        $period = DrePeriod::createMonthly('2024-01');
        
        // Gerar e salvar DRE
        $dre = $this->repository->generate($period);
        $this->repository->save($dre);
        
        // Buscar DRE pelo período
        $foundDre = $this->repository->findByPeriod($period);
        
        $this->assertInstanceOf(Dre::class, $foundDre);
        $this->assertEquals($dre->getId(), $foundDre->getId());
        $this->assertEquals($dre->getTitle(), $foundDre->getTitle());
    }

    public function test_delete_dre(): void
    {
        $period = DrePeriod::createMonthly('2024-01');
        
        // Gerar e salvar DRE
        $dre = $this->repository->generate($period);
        $dreId = $dre->getId();
        $this->repository->save($dre);
        
        // Verificar que DRE existe
        $this->assertNotNull($this->repository->findById($dreId));
        
        // Deletar DRE
        $this->repository->delete($dreId);
        
        // Verificar que DRE foi deletado
        $this->assertNull($this->repository->findById($dreId));
    }

    public function test_export_dre_to_pdf(): void
    {
        $period = DrePeriod::createMonthly('2024-01');
        
        // Gerar DRE
        $dre = $this->repository->generate($period);
        $this->repository->save($dre);
        
        // Exportar para PDF
        $exportContent = $this->repository->export($dre, 'pdf');
        
        $this->assertIsString($exportContent);
        $this->assertNotEmpty($exportContent);
        $this->assertStringContainsString('DRE:', $exportContent);
        $this->assertStringContainsString('Receita Total:', $exportContent);
    }

    public function test_export_dre_to_excel(): void
    {
        $period = DrePeriod::createMonthly('2024-01');
        
        // Gerar DRE
        $dre = $this->repository->generate($period);
        $this->repository->save($dre);
        
        // Exportar para Excel
        $exportContent = $this->repository->export($dre, 'excel');
        
        $this->assertIsString($exportContent);
        $this->assertNotEmpty($exportContent);
        $this->assertJson($exportContent);
        
        $data = json_decode($exportContent, true);
        $this->assertIsArray($data);
        $this->assertNotEmpty($data);
    }

    public function test_export_dre_to_csv(): void
    {
        $period = DrePeriod::createMonthly('2024-01');
        
        // Gerar DRE
        $dre = $this->repository->generate($period);
        $this->repository->save($dre);
        
        // Exportar para CSV
        $exportContent = $this->repository->export($dre, 'csv');
        
        $this->assertIsString($exportContent);
        $this->assertNotEmpty($exportContent);
        $this->assertStringContainsString('Código,Descrição,Valor,Tipo,Nível', $exportContent);
    }

    public function test_export_dre_to_json(): void
    {
        $period = DrePeriod::createMonthly('2024-01');
        
        // Gerar DRE
        $dre = $this->repository->generate($period);
        $this->repository->save($dre);
        
        // Exportar para JSON
        $exportContent = $this->repository->export($dre, 'json');
        
        $this->assertIsString($exportContent);
        $this->assertNotEmpty($exportContent);
        $this->assertJson($exportContent);
        
        $data = json_decode($exportContent, true);
        $this->assertIsArray($data);
        $this->assertArrayHasKey('dre', $data);
        $this->assertArrayHasKey('lines', $data['dre']);
        $this->assertArrayHasKey('totals', $data['dre']);
    }

    public function test_generate_dre_with_different_scenarios(): void
    {
        $period = DrePeriod::createMonthly('2024-01');
        
        // Gerar DRE com cenário otimista
        $dreOptimistic = $this->repository->generate($period, null, 'optimistic');
        
        // Gerar DRE com cenário pessimista
        $drePessimistic = $this->repository->generate($period, null, 'pessimistic');
        
        // Gerar DRE com cenário conservador
        $dreConservative = $this->repository->generate($period, null, 'conservative');

        $this->assertInstanceOf(Dre::class, $dreOptimistic);
        $this->assertInstanceOf(Dre::class, $drePessimistic);
        $this->assertInstanceOf(Dre::class, $dreConservative);
        
        $this->assertEquals('optimistic', $dreOptimistic->getScenario());
        $this->assertEquals('pessimistic', $drePessimistic->getScenario());
        $this->assertEquals('conservative', $dreConservative->getScenario());
        
        $this->assertStringContainsString('Cenário: Otimista', $dreOptimistic->getTitle());
        $this->assertStringContainsString('Cenário: Pessimista', $drePessimistic->getTitle());
        $this->assertStringContainsString('Cenário: Conservador', $dreConservative->getTitle());
    }

    public function test_generate_dre_with_quarterly_period(): void
    {
        // Criar transações para o primeiro trimestre
        for ($month = 1; $month <= 3; $month++) {
            TransactionModel::factory()->create([
                'direction' => 'revenue',
                'amount' => '1000.00',
                'competence_month' => sprintf('2024-%02d', $month),
            ]);
        }

        $period = DrePeriod::createQuarterly('2024', 1);
        $dre = $this->repository->generate($period);

        $this->assertInstanceOf(Dre::class, $dre);
        $this->assertStringContainsString('1º Trimestre 2024', $dre->getTitle());
        $this->assertEquals('quarterly', $dre->getPeriod()->getPeriodType());
        
        // Verificar totais (3 meses * 1000)
        $this->assertEquals('3000.00', $dre->getTotalRevenue()->getAmount());
    }

    public function test_generate_dre_with_yearly_period(): void
    {
        // Criar transações para o ano
        for ($month = 1; $month <= 12; $month++) {
            TransactionModel::factory()->create([
                'direction' => 'revenue',
                'amount' => '1000.00',
                'competence_month' => sprintf('2024-%02d', $month),
            ]);
        }

        $period = DrePeriod::createYearly('2024');
        $dre = $this->repository->generate($period);

        $this->assertInstanceOf(Dre::class, $dre);
        $this->assertStringContainsString('Ano 2024', $dre->getTitle());
        $this->assertEquals('yearly', $dre->getPeriod()->getPeriodType());
        
        // Verificar totais (12 meses * 1000)
        $this->assertEquals('12000.00', $dre->getTotalRevenue()->getAmount());
    }

    public function test_generate_dre_with_custom_period(): void
    {
        // Criar transações para um período customizado
        TransactionModel::factory()->create([
            'direction' => 'revenue',
            'amount' => '1000.00',
            'competence_month' => '2024-01',
        ]);
        
        TransactionModel::factory()->create([
            'direction' => 'revenue',
            'amount' => '2000.00',
            'competence_month' => '2024-02',
        ]);

        $startDate = new \DateTimeImmutable('2024-01-01');
        $endDate = new \DateTimeImmutable('2024-02-28');
        $period = new DrePeriod($startDate, $endDate, 'custom');
        
        $dre = $this->repository->generate($period);

        $this->assertInstanceOf(Dre::class, $dre);
        $this->assertStringContainsString('01/01/2024 a 28/02/2024', $dre->getTitle());
        
        // Verificar totais (1000 + 2000)
        $this->assertEquals('3000.00', $dre->getTotalRevenue()->getAmount());
    }

    private function setupTenantSchema(): void
    {
        // Configurar schema de tenant para testes
        config(['database.connections.tenant.schema' => 'test_tenant']);
        
        // Criar schema de teste se não existir
        $schemaName = 'test_tenant';
        $connection = config('database.connections.tenant');
        
        \Illuminate\Support\Facades\DB::statement("CREATE SCHEMA IF NOT EXISTS {$schemaName}");
        
        // Configurar search_path para o schema do tenant
        \Illuminate\Support\Facades\DB::statement("SET search_path TO {$schemaName}, public");
    }

    protected function tearDown(): void
    {
        // Only run tearDown if we didn't skip the test
        if (extension_loaded('pdo_pgsql')) {
            // Limpar schema de teste
            $schemaName = 'test_tenant';
            \Illuminate\Support\Facades\DB::statement("DROP SCHEMA IF EXISTS $schemaName CASCADE");
        }
        
        parent::tearDown();
    }
}