<?php

declare(strict_types=1);

namespace App\Http\Controllers; 

use App\Application\Categories\DTOs\CreateCategoryData;
use App\Application\Categories\DTOs\UpdateCategoryData;
use App\Application\Categories\Handlers\CreateCategoryHandler;
use App\Application\Categories\Handlers\DeleteCategoryHandler;
use App\Application\Categories\Handlers\UpdateCategoryHandler;
use App\Domain\Categories\ValueObjects\CategoryId;
use App\Domain\Categories\Repositories\CategoryRepositoryInterface;
use App\Domain\Categories\ValueObjects\CategoryStatus;
use App\Domain\Categories\ValueObjects\CategoryType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CategoriesController extends Controller
{
    public function index(CategoryRepositoryInterface $repository): Response
    {
        $categories = $repository->findAll();

        return Inertia::render('Categories/Index', [
            'categories' => array_map(fn ($category) => [
                'id' => $category->getId()->toString(),
                'name' => $category->getName(),
                'type' => $category->getType()->value,
                'code' => $category->getCode(),
                'description' => $category->getDescription(),
                'color' => $category->getColor(),
                'icon' => $category->getIcon(),
                'isOperating' => $category->isOperating(),
                'isTaxDeductible' => $category->isTaxDeductible(),
                'includeInReports' => $category->isIncludeInReports(),
                'isDefault' => $category->isDefault(),
                'parentId' => $category->getParentId()?->toString(),
                'createdAt' => $category->getCreatedAt()->format('Y-m-d H:i:s'),
                'updatedAt' => $category->getUpdatedAt()->format('Y-m-d H:i:s'),
                'archivedAt' => $category->getArchivedAt()?->format('Y-m-d H:i:s'),
            ], $categories),
            'meta' => [
                'total' => count($categories),
                'per_page' => 15,
                'current_page' => 1,
                'last_page' => 1,
                'from' => 1,
                'to' => count($categories),
            ],
            'filters' => [],
            'categoryTypes' => array_map(fn ($type) => [
                'value' => $type->value,
                'label' => $type->label(),
            ], CategoryType::cases()),
            'categoryStatuses' => array_map(fn ($status) => [
                'value' => $status->value,
                'label' => $status->label(),
            ], CategoryStatus::cases()),
        ]);
    }

    public function create(CategoryRepositoryInterface $repository): Response
    {
        $categories = $repository->findAll();

        return Inertia::render('Categories/Create', [
            'types' => array_map(fn ($type) => $type->value, CategoryType::cases()),
            'categories' => array_map(fn ($category) => [
                'id' => $category->getId()->toString(),
                'name' => $category->getName(),
                'type' => $category->getType()->value,
            ], $categories),
        ]);
    }

    public function store(Request $request, CreateCategoryHandler $handler): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:' . implode(',', array_map(fn ($type) => $type->value, CategoryType::cases())),
            'code' => 'required|string|max:20|unique:categories,code',
            'description' => 'nullable|string|max:1000',
            'color' => 'nullable|string|max:7',
            'icon' => 'nullable|string|max:50',
            'is_operating' => 'boolean',
            'is_tax_deductible' => 'boolean',
            'include_in_reports' => 'boolean',
            'is_default' => 'boolean',
            'parent_id' => 'nullable|uuid|exists:categories,id',
        ]);

        $type = CategoryType::tryFrom($validated['type']);
        if ($type === null) {
            throw new \InvalidArgumentException('Invalid category type.');
        }
        
        $categoryId = $handler->handle(new CreateCategoryData(
            name: $validated['name'],
            type: $type,
            code: $validated['code'],
            description: $validated['description'] ?? null,
            color: $validated['color'] ?? null,
            icon: $validated['icon'] ?? null,
            isOperating: $validated['is_operating'] ?? true,
            isTaxDeductible: $validated['is_tax_deductible'] ?? false,
            includeInReports: $validated['include_in_reports'] ?? true,
            isDefault: $validated['is_default'] ?? false,
            parentId: $validated['parent_id'] ? CategoryId::fromString($validated['parent_id']) : null,
        ));

        return redirect()->route('categories.index')
            ->with('success', 'Categoria criada com sucesso.');
    }

    public function edit(string $id, CategoryRepositoryInterface $repository): Response
    {
        $category = $repository->findById(CategoryId::fromString($id));

        if ($category === null) {
            abort(404);
        }

        $categories = $repository->findAll();

        return Inertia::render('Categories/Edit', [
            'category' => [
                'id' => $category->getId()->toString(),
                'name' => $category->getName(),
                'type' => $category->getType()->value,
                'status' => $category->getStatus()->value,
                'code' => $category->getCode(),
                'description' => $category->getDescription(),
                'color' => $category->getColor(),
                'icon' => $category->getIcon(),
                'is_operating' => $category->isOperating(),
                'is_tax_deductible' => $category->isTaxDeductible(),
                'include_in_reports' => $category->isIncludeInReports(),
                'is_default' => $category->isDefault(),
                'parent_id' => $category->getParentId()?->toString(),
            ],
            'types' => array_map(fn ($type) => $type->value, CategoryType::cases()),
            'statuses' => array_map(fn ($status) => $status->value, CategoryStatus::cases()),
            'categories' => array_map(fn ($cat) => [
                'id' => $cat->getId()->toString(),
                'name' => $cat->getName(),
                'type' => $cat->getType()->value,
            ], $categories),
        ]);
    }

    public function update(Request $request, string $id, UpdateCategoryHandler $handler): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:' . implode(',', array_map(fn ($type) => $type->value, CategoryType::cases())),
            'status' => 'required|in:' . implode(',', array_map(fn ($status) => $status->value, CategoryStatus::cases())),
            'code' => 'required|string|max:20|unique:categories,code,' . $id . ',id',
            'description' => 'nullable|string|max:1000',
            'color' => 'nullable|string|max:7',
            'icon' => 'nullable|string|max:50',
            'is_operating' => 'boolean',
            'is_tax_deductible' => 'boolean',
            'include_in_reports' => 'boolean',
            'is_default' => 'boolean',
            'parent_id' => 'nullable|uuid|exists:categories,id',
        ]);

        $type = CategoryType::tryFrom($validated['type']);
        if ($type === null) {
            throw new \InvalidArgumentException('Invalid category type.');
        }
        
        $status = CategoryStatus::tryFrom($validated['status']);
        if ($status === null) {
            throw new \InvalidArgumentException('Invalid category status.');
        }
        
        $handler->handle(
            CategoryId::fromString($id),
            new UpdateCategoryData(
                name: $validated['name'],
                type: $type,
                status: $status,
                code: $validated['code'],
                description: $validated['description'] ?? null,
                color: $validated['color'] ?? null,
                icon: $validated['icon'] ?? null,
                isOperating: $validated['is_operating'] ?? true,
                isTaxDeductible: $validated['is_tax_deductible'] ?? false,
                includeInReports: $validated['include_in_reports'] ?? true,
                isDefault: $validated['is_default'] ?? false,
                parentId: $validated['parent_id'] ? CategoryId::fromString($validated['parent_id']) : null,
            )
        );

        return redirect()->route('categories.index')
            ->with('success', 'Categoria atualizada com sucesso.');
    }

    public function destroy(string $id, DeleteCategoryHandler $handler): RedirectResponse
    {
        $handler->handle(CategoryId::fromString($id));

        return redirect()->route('categories.index')
            ->with('success', 'Categoria excluída com sucesso.');
    }

    public function archive(string $id, DeleteCategoryHandler $handler): RedirectResponse
    {
        $handler->archive(CategoryId::fromString($id));

        return redirect()->route('categories.index')
            ->with('success', 'Categoria arquivada com sucesso.');
    }

    public function restore(string $id, DeleteCategoryHandler $handler): RedirectResponse
    {
        $handler->restore(CategoryId::fromString($id));

        return redirect()->route('categories.index')
            ->with('success', 'Categoria restaurada com sucesso.');
    }
}