<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Application\Reports\Data\GenerateDreData;
use App\Application\Reports\Data\ExportDreData;
use App\Application\Reports\Handlers\GenerateDreHandler;
use App\Domain\Reports\Dre;
use App\Domain\Reports\DreRepositoryInterface;
use App\Http\Requests\GenerateDreRequest;
use App\Http\Requests\ExportDreRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

final class DreController extends Controller
{
    public function __construct(
        private readonly GenerateDreHandler $generateDreHandler,
        private readonly DreRepositoryInterface $dreRepository,
    ) {}

    public function index(Request $request): Response
    {
        $periodType = $request->query('period_type', 'monthly');
        $yearMonth = $request->query('year_month', date('Y-m'));
        $year = $request->query('year', date('Y'));
        $quarter = (int) $request->query('quarter', ceil(date('n') / 3));
        $categoryId = $request->query('category_id');
        $scenario = $request->query('scenario', 'base');
        
        $dres = $this->dreRepository->findAll(
            $periodType,
            $yearMonth,
            $year,
            $quarter,
            $categoryId,
            $scenario,
            $request->query('page', 1),
            20
        );

        return Inertia::render('Dre/Index', [
            'dres' => $dres,
            'filters' => [
                'period_type' => $periodType,
                'year_month' => $yearMonth,
                'year' => $year,
                'quarter' => $quarter,
                'category_id' => $categoryId,
                'scenario' => $scenario,
            ],
            'categories' => \App\Infrastructure\Persistence\Eloquent\Models\CategoryModel::all(),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Dre/Create', [
            'categories' => \App\Infrastructure\Persistence\Eloquent\Models\CategoryModel::all(),
            'period_types' => [
                ['value' => 'monthly', 'label' => 'Mensal'],
                ['value' => 'quarterly', 'label' => 'Trimestral'],
                ['value' => 'yearly', 'label' => 'Anual'],
                ['value' => 'custom', 'label' => 'Período Customizado'],
            ],
            'scenarios' => [
                ['value' => 'base', 'label' => 'Cenário Base'],
                ['value' => 'optimistic', 'label' => 'Cenário Otimista'],
                ['value' => 'pessimistic', 'label' => 'Cenário Pessimista'],
                ['value' => 'conservative', 'label' => 'Cenário Conservador'],
            ],
        ]);
    }

    public function store(GenerateDreRequest $request): JsonResponse
    {
        $data = new GenerateDreData(
            periodType: $request->validated('period_type'),
            yearMonth: $request->validated('year_month', ''),
            year: $request->validated('year', ''),
            quarter: (int) $request->validated('quarter', 0),
            categoryId: $request->validated('category_id'),
            scenario: $request->validated('scenario', 'base'),
            startDate: $request->validated('start_date'),
            endDate: $request->validated('end_date'),
        );

        $dre = $this->generateDreHandler->handle($data);

        // Salvar o DRE gerado
        $this->dreRepository->save($dre);

        return response()->json([
            'message' => 'DRE gerado com sucesso!',
            'dre' => [
                'id' => $dre->getId(),
                'title' => $dre->getTitle(),
                'period' => [
                    'start' => $dre->getPeriod()->getStartDate()->format('Y-m-d'),
                    'end' => $dre->getPeriod()->getEndDate()->format('Y-m-d'),
                    'type' => $dre->getPeriod()->getPeriodType(),
                ],
                'totals' => [
                    'revenue' => $dre->getTotalRevenue()->getAmount(),
                    'expenses' => $dre->getTotalExpenses()->getAmount(),
                    'net_profit' => $dre->getNetProfit()->getAmount(),
                    'gross_profit' => $dre->getGrossProfit()->getAmount(),
                    'operating_profit' => $dre->getOperatingProfit()->getAmount(),
                    'ebitda' => $dre->getEbitda()->getAmount(),
                    'ebit' => $dre->getEbit()->getAmount(),
                ],
            ],
        ], 201);
    }

    public function show(string $id): Response
    {
        $dre = $this->dreRepository->findById($id);
        
        if (!$dre) {
            abort(404, 'DRE não encontrado.');
        }

        return Inertia::render('Dre/Show', [
            'dre' => $this->formatDreForResponse($dre),
        ]);
    }

    public function edit(string $id): Response
    {
        $dre = $this->dreRepository->findById($id);
        
        if (!$dre) {
            abort(404, 'DRE não encontrado.');
        }

        return Inertia::render('Dre/Edit', [
            'dre' => $this->formatDreForResponse($dre),
            'categories' => \App\Infrastructure\Persistence\Eloquent\Models\CategoryModel::all(),
            'scenarios' => [
                ['value' => 'base', 'label' => 'Cenário Base'],
                ['value' => 'optimistic', 'label' => 'Cenário Otimista'],
                ['value' => 'pessimistic', 'label' => 'Cenário Pessimista'],
                ['value' => 'conservative', 'label' => 'Cenário Conservador'],
            ],
        ]);
    }

    public function update(GenerateDreRequest $request, string $id): JsonResponse
    {
        $dre = $this->dreRepository->findById($id);
        
        if (!$dre) {
            abort(404, 'DRE não encontrado.');
        }

        $data = new GenerateDreData(
            periodType: $request->validated('period_type'),
            yearMonth: $request->validated('year_month', ''),
            year: $request->validated('year', ''),
            quarter: (int) $request->validated('quarter', 0),
            categoryId: $request->validated('category_id'),
            scenario: $request->validated('scenario', 'base'),
            startDate: $request->validated('start_date'),
            endDate: $request->validated('end_date'),
        );

        $updatedDre = $this->generateDreHandler->handle($data);
        $updatedDre->setId($dre->getId());

        $this->dreRepository->save($updatedDre);

        return response()->json([
            'message' => 'DRE atualizado com sucesso!',
            'dre' => [
                'id' => $updatedDre->getId(),
                'title' => $updatedDre->getTitle(),
            ],
        ]);
    }

    public function destroy(string $id): JsonResponse
    {
        $dre = $this->dreRepository->findById($id);
        
        if (!$dre) {
            abort(404, 'DRE não encontrado.');
        }

        $this->dreRepository->delete($id);

        return response()->json([
            'message' => 'DRE excluído com sucesso!',
        ]);
    }

    public function generateConsolidated(Request $request): JsonResponse
    {
        $request->validate([
            'periods' => 'required|array|min:2',
            'periods.*.start_date' => 'required|date',
            'periods.*.end_date' => 'required|date|after_or_equal:periods.*.start_date',
            'category_id' => 'nullable|string|exists:categories,id',
            'scenario' => 'string|in:base,optimistic,pessimistic,conservative',
        ]);

        $periods = [];
        foreach ($request->periods as $periodData) {
            $periods[] = new \App\Domain\Reports\ValueObjects\DrePeriod(
                new \DateTimeImmutable($periodData['start_date']),
                new \DateTimeImmutable($periodData['end_date']),
                'custom'
            );
        }

        $dre = $this->dreRepository->generateConsolidated(
            $periods,
            $request->category_id,
            $request->scenario ?? 'base'
        );

        $this->dreRepository->save($dre);

        return response()->json([
            'message' => 'DRE consolidado gerado com sucesso!',
            'dre' => [
                'id' => $dre->getId(),
                'title' => $dre->getTitle(),
            ],
        ], 201);
    }

    public function generateComparative(Request $request): JsonResponse
    {
        $request->validate([
            'current_period_start' => 'required|date',
            'current_period_end' => 'required|date|after_or_equal:current_period_start',
            'previous_period_start' => 'required|date',
            'previous_period_end' => 'required|date|after_or_equal:previous_period_start',
            'category_id' => 'nullable|string|exists:categories,id',
        ]);

        $currentPeriod = new \App\Domain\Reports\ValueObjects\DrePeriod(
            new \DateTimeImmutable($request->current_period_start),
            new \DateTimeImmutable($request->current_period_end),
            'custom'
        );

        $previousPeriod = new \App\Domain\Reports\ValueObjects\DrePeriod(
            new \DateTimeImmutable($request->previous_period_start),
            new \DateTimeImmutable($request->previous_period_end),
            'custom'
        );

        $dre = $this->dreRepository->generateComparative(
            $currentPeriod,
            $previousPeriod,
            $request->category_id
        );

        $this->dreRepository->save($dre);

        return response()->json([
            'message' => 'DRE comparativo gerado com sucesso!',
            'dre' => [
                'id' => $dre->getId(),
                'title' => $dre->getTitle(),
            ],
        ], 201);
    }

    public function export(ExportDreRequest $request, string $id): JsonResponse
    {
        $dre = $this->dreRepository->findById($id);
        
        if (!$dre) {
            abort(404, 'DRE não encontrado.');
        }

        $data = new ExportDreData(
            dreId: $id,
            format: $request->validated('format'),
            includeDetails: $request->validated('include_details', true),
            includeRatios: $request->validated('include_ratios', true),
            includeCharts: $request->validated('include_charts', false),
            language: $request->validated('language', 'pt-BR'),
            currency: $request->validated('currency', 'BRL'),
        );

        $exportContent = $this->dreRepository->export($dre, $data->format);

        $filename = "dre_{$dre->getId()}_{$data->format}." . match ($data->format) {
            'pdf' => 'pdf',
            'excel' => 'xlsx',
            'csv' => 'csv',
            'json' => 'json',
            default => 'txt',
        };

        return response()->json([
            'message' => 'DRE exportado com sucesso!',
            'filename' => $filename,
            'content' => base64_encode($exportContent),
            'format' => $data->format,
        ]);
    }

    public function downloadExport(Request $request, string $id): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        $dre = $this->dreRepository->findById($id);
        
        if (!$dre) {
            abort(404, 'DRE não encontrado.');
        }

        $format = $request->query('format', 'pdf');
        $exportContent = $this->dreRepository->export($dre, $format);

        $filename = "dre_{$dre->getId()}_{$format}." . match ($format) {
            'pdf' => 'pdf',
            'excel' => 'xlsx',
            'csv' => 'csv',
            'json' => 'json',
            default => 'txt',
        };

        $tempPath = tempnam(sys_get_temp_dir(), 'dre_export_');
        file_put_contents($tempPath, $exportContent);

        return response()->download($tempPath, $filename)->deleteFileAfterSend(true);
    }

    public function getStandardStructure(): JsonResponse
    {
        $structure = $this->generateDreHandler->getStandardDreStructure();

        return response()->json([
            'structure' => $structure,
        ]);
    }

    public function getRatios(string $id): JsonResponse
    {
        $dre = $this->dreRepository->findById($id);
        
        if (!$dre) {
            abort(404, 'DRE não encontrado.');
        }

        $ratios = $this->generateDreHandler->calculateFinancialRatios($dre);

        return response()->json([
            'ratios' => $ratios,
        ]);
    }

    private function formatDreForResponse(Dre $dre): array
    {
        $lines = [];
        foreach ($dre->getLines() as $line) {
            $lines[] = [
                'id' => $line->getId(),
                'code' => $line->getCode(),
                'description' => $line->getDescription(),
                'amount' => $line->getAmount()->getAmount(),
                'formatted_amount' => $line->getAmount()->format(),
                'type' => $line->getType()->value,
                'type_label' => $line->getType()->getLabel(),
                'level' => $line->getLevel(),
                'is_operating' => $line->isOperating(),
                'parent_id' => $line->getParentId(),
                'category_id' => $line->getCategoryId(),
                'category_name' => $line->getCategoryName(),
                'notes' => $line->getNotes(),
            ];
        }

        return [
            'id' => $dre->getId(),
            'title' => $dre->getTitle(),
            'period' => [
                'start' => $dre->getPeriod()->getStartDate()->format('Y-m-d'),
                'end' => $dre->getPeriod()->getEndDate()->format('Y-m-d'),
                'type' => $dre->getPeriod()->getPeriodType(),
                'formatted' => $dre->getPeriod()->getStartDate()->format('d/m/Y') . ' a ' . $dre->getPeriod()->getEndDate()->format('d/m/Y'),
            ],
            'category_id' => $dre->getCategoryId(),
            'scenario' => $dre->getScenario(),
            'lines' => $lines,
            'totals' => [
                'revenue' => [
                    'amount' => $dre->getTotalRevenue()->getAmount(),
                    'formatted' => $dre->getTotalRevenue()->format(),
                ],
                'expenses' => [
                    'amount' => $dre->getTotalExpenses()->getAmount(),
                    'formatted' => $dre->getTotalExpenses()->format(),
                ],
                'net_profit' => [
                    'amount' => $dre->getNetProfit()->getAmount(),
                    'formatted' => $dre->getNetProfit()->format(),
                ],
                'gross_profit' => [
                    'amount' => $dre->getGrossProfit()->getAmount(),
                    'formatted' => $dre->getGrossProfit()->format(),
                ],
                'operating_profit' => [
                    'amount' => $dre->getOperatingProfit()->getAmount(),
                    'formatted' => $dre->getOperatingProfit()->format(),
                ],
                'ebitda' => [
                    'amount' => $dre->getEbitda()->getAmount(),
                    'formatted' => $dre->getEbitda()->format(),
                ],
                'ebit' => [
                    'amount' => $dre->getEbit()->getAmount(),
                    'formatted' => $dre->getEbit()->format(),
                ],
            ],
            'generated_at' => $dre->getGeneratedAt()?->format('Y-m-d H:i:s'),
        ];
    }
}