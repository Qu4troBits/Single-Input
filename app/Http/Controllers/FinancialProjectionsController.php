<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Application\FinancialProjections\DTOs\GenerateProjectionData;
use App\Application\FinancialProjections\DTOs\SaveProjectionData;
use App\Application\FinancialProjections\Handlers\GenerateProjectionHandler;
use App\Application\FinancialProjections\Handlers\SaveProjectionHandler;
use App\Domain\FinancialProjections\FinancialProjectionRepositoryInterface;
use App\Domain\FinancialProjections\ProjectionType;
use App\Http\Requests\FinancialProjections\GenerateProjectionRequest;
use App\Http\Requests\FinancialProjections\SaveProjectionRequest;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

final class FinancialProjectionsController extends Controller
{
    public function index(FinancialProjectionRepositoryInterface $repository): Response
    {
        $currentYear = date('Y');
        $currentMonth = date('Y-m');
        
        // Obter projeções recentes
        $recentProjections = $repository->findProjectionsByPeriod(
            \App\Domain\FinancialProjections\ProjectionPeriod::createMonthly($currentMonth)
        );

        // Obter cenários disponíveis
        $availableScenarios = $repository->getAvailableScenarios();

        // Obter categorias para filtro
        $categories = \App\Infrastructure\Persistence\Eloquent\Models\CategoryModel::query()
            ->where('active', true)
            ->orderBy('name')
            ->get();

        return Inertia::render('FinancialProjections/Index', [
            'recent_projections' => array_map(fn($proj) => $proj->toArray(), $recentProjections),
            'available_scenarios' => $availableScenarios,
            'categories' => $categories,
            'current_year' => $currentYear,
            'current_month' => $currentMonth,
        ]);
    }

    public function show(
        string $projectionId,
        FinancialProjectionRepositoryInterface $repository
    ): Response {
        $projection = $this->findProjectionOrFail($projectionId, $repository);

        return Inertia::render('FinancialProjections/Show', [
            'projection' => $projection->toArray(),
        ]);
    }

    public function create(): Response
    {
        $categories = \App\Infrastructure\Persistence\Eloquent\Models\CategoryModel::query()
            ->where('active', true)
            ->orderBy('name')
            ->get();

        $availableScenarios = ['base', 'optimistic', 'pessimistic', 'custom'];

        return Inertia::render('FinancialProjections/Create', [
            'categories' => $categories,
            'available_scenarios' => $availableScenarios,
            'projection_types' => [
                ['value' => 'revenue', 'label' => 'Receita'],
                ['value' => 'expense', 'label' => 'Despesa'],
                ['value' => 'profit', 'label' => 'Lucro'],
                ['value' => 'cash_flow', 'label' => 'Fluxo de Caixa'],
                ['value' => 'balance_sheet', 'label' => 'Balanço Patrimonial'],
            ],
            'period_types' => [
                ['value' => 'monthly', 'label' => 'Mensal'],
                ['value' => 'quarterly', 'label' => 'Trimestral'],
                ['value' => 'yearly', 'label' => 'Anual'],
            ],
        ]);
    }

    public function store(
        SaveProjectionRequest $request,
        SaveProjectionHandler $handler
    ): RedirectResponse {
        $validated = $request->validated();

        $items = [];
        foreach ($validated['items'] as $item) {
            $items[] = new \App\Application\FinancialProjections\Data\ProjectionItemData(
                id: $item['id'],
                date: $item['date'],
                description: $item['description'],
                amount: $item['amount'],
                type: ProjectionType::from($item['type']),
                categoryId: $item['category_id'] ?? null,
                categoryName: $item['category_name'] ?? null,
                notes: $item['notes'] ?? null,
                source: $item['source'] ?? null,
            );
        }

        $data = new SaveProjectionData(
            id: $validated['id'] ?? uniqid('proj-'),
            type: ProjectionType::from($validated['type']),
            periodType: $validated['period_type'],
            yearMonth: $validated['year_month'] ?? '',
            year: $validated['year'] ?? '',
            quarter: $validated['quarter'] ?? 0,
            categoryId: $validated['category_id'] ?? null,
            scenario: $validated['scenario'] ?? 'base',
            title: $validated['title'],
            items: $items,
            notes: $validated['notes'] ?? null,
        );

        $handler->handle($data);

        return redirect()->route('financial-projections.index')
            ->with('success', 'Projeção salva com sucesso.');
    }

    public function edit(
        string $projectionId,
        FinancialProjectionRepositoryInterface $repository
    ): Response {
        $projection = $this->findProjectionOrFail($projectionId, $repository);

        $categories = \App\Infrastructure\Persistence\Eloquent\Models\CategoryModel::query()
            ->where('active', true)
            ->orderBy('name')
            ->get();

        $availableScenarios = ['base', 'optimistic', 'pessimistic', 'custom'];

        return Inertia::render('FinancialProjections/Edit', [
            'projection' => $projection->toArray(),
            'categories' => $categories,
            'available_scenarios' => $availableScenarios,
        ]);
    }

    public function update(
        SaveProjectionRequest $request,
        SaveProjectionHandler $handler,
        string $projectionId
    ): RedirectResponse {
        $validated = $request->validated();
        $validated['id'] = $projectionId;

        $items = [];
        foreach ($validated['items'] as $item) {
            $items[] = new \App\Application\FinancialProjections\Data\ProjectionItemData(
                id: $item['id'],
                date: $item['date'],
                description: $item['description'],
                amount: $item['amount'],
                type: ProjectionType::from($item['type']),
                categoryId: $item['category_id'] ?? null,
                categoryName: $item['category_name'] ?? null,
                notes: $item['notes'] ?? null,
                source: $item['source'] ?? null,
            );
        }

        $data = new SaveProjectionData(
            id: $projectionId,
            type: ProjectionType::from($validated['type']),
            periodType: $validated['period_type'],
            yearMonth: $validated['year_month'] ?? '',
            year: $validated['year'] ?? '',
            quarter: $validated['quarter'] ?? 0,
            categoryId: $validated['category_id'] ?? null,
            scenario: $validated['scenario'] ?? 'base',
            title: $validated['title'],
            items: $items,
            notes: $validated['notes'] ?? null,
        );

        $handler->handle($data);

        return redirect()->route('financial-projections.show', $projectionId)
            ->with('success', 'Projeção atualizada com sucesso.');
    }

    public function destroy(
        string $projectionId,
        FinancialProjectionRepositoryInterface $repository
    ): RedirectResponse {
        $repository->delete($projectionId);

        return redirect()->route('financial-projections.index')
            ->with('success', 'Projeção excluída com sucesso.');
    }

    public function generate(
        GenerateProjectionRequest $request,
        GenerateProjectionHandler $handler
    ): Response {
        $validated = $request->validated();

        $data = new GenerateProjectionData(
            type: ProjectionType::from($validated['type']),
            periodType: $validated['period_type'],
            yearMonth: $validated['year_month'] ?? '',
            year: $validated['year'] ?? '',
            quarter: $validated['quarter'] ?? 0,
            categoryId: $validated['category_id'] ?? null,
            scenario: $validated['scenario'] ?? 'base',
            notes: $validated['notes'] ?? null,
        );

        $projection = $handler->handle($data);

        return Inertia::render('FinancialProjections/Generated', [
            'projection' => $projection->toArray(),
            'input_data' => $validated,
        ]);
    }

    private function findProjectionOrFail(
        string $projectionId,
        FinancialProjectionRepositoryInterface $repository
    ): FinancialProjection {
        $projections = $repository->findProjectionsByPeriod(
            \App\Domain\FinancialProjections\ProjectionPeriod::createMonthly(date('Y-m'))
        );

        foreach ($projections as $projection) {
            $data = $projection->toArray();
            if (($data['id'] ?? '') === $projectionId) {
                return $projection;
            }
        }

        abort(404, 'Projeção não encontrada.');
    }
}