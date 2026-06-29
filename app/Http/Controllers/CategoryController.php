<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Application\Categories\DTOs\CreateCategoryData;
use App\Application\Categories\DTOs\UpdateCategoryData;
use App\Application\Categories\Handlers\CreateCategoryHandler;
use App\Application\Categories\Handlers\DeleteCategoryHandler;
use App\Application\Categories\Handlers\UpdateCategoryHandler;
use App\Domain\Categories\Repositories\CategoryRepositoryInterface;
use App\Domain\Categories\ValueObjects\CategoryId;
use App\Domain\Categories\ValueObjects\CategoryType;
use App\Http\Requests\Category\CreateCategoryRequest;
use App\Http\Requests\Category\UpdateCategoryRequest;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

final class CategoryController extends Controller
{
    public function __construct(
        private readonly CategoryRepositoryInterface $categoryRepository,
        private readonly CreateCategoryHandler $createCategoryHandler,
        private readonly UpdateCategoryHandler $updateCategoryHandler,
        private readonly DeleteCategoryHandler $deleteCategoryHandler,
    ) {}

    public function index(Request $request): Response
    {
        $type = $request->query('type');
        $parentId = $request->query('parent_id');
        $isOperating = $request->boolean('is_operating', false);
        $isTaxDeductible = $request->boolean('is_tax_deductible', false);
        $includeInReports = $request->boolean('include_in_reports', false);
        $isDefault = $request->boolean('is_default', false);
        $page = (int) $request->query('page', 1);

        $categoryType = $type ? CategoryType::from($type) : null;
        $categoryParentId = $parentId ? CategoryId::fromString($parentId) : null;

        $result = $this->categoryRepository->findAll(
            type: $categoryType,
            parentId: $categoryParentId,
            isOperating: $isOperating,
            isTaxDeductible: $isTaxDeductible,
            includeInReports: $includeInReports,
            isDefault: $isDefault,
            page: $page,
            perPage: 20
        );

        return Inertia::render('Categories/Index', [
            'categories' => $result['data'],
            'meta' => $result['meta'],
            'filters' => [
                'type' => $type,
                'parent_id' => $parentId,
                'is_operating' => $isOperating,
                'is_tax_deductible' => $isTaxDeductible,
                'include_in_reports' => $includeInReports,
                'is_default' => $isDefault,
            ],
            'categoryTypes' => array_map(
                fn (CategoryType $type) => [
                    'value' => $type->value,
                    'label' => $type->label(),
                ],
                CategoryType::cases()
            ),
            'parentCategories' => $this->categoryRepository->findAllRoot(),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Categories/Create', [
            'categoryTypes' => array_map(
                fn (CategoryType $type) => [
                    'value' => $type->value,
                    'label' => $type->label(),
                ],
                CategoryType::cases()
            ),
            'parentCategories' => $this->categoryRepository->findAllRoot(),
        ]);
    }

    public function store(CreateCategoryRequest $request): Response
    {
        $data = new CreateCategoryData(
            name: $request->validated('name'),
            type: CategoryType::from($request->validated('type')),
            code: $request->validated('code'),
            description: $request->validated('description'),
            color: $request->validated('color'),
            icon: $request->validated('icon'),
            isOperating: $request->boolean('is_operating', true),
            isTaxDeductible: $request->boolean('is_tax_deductible', false),
            includeInReports: $request->boolean('include_in_reports', true),
            isDefault: $request->boolean('is_default', false),
            parentId: $request->validated('parent_id') 
                ? CategoryId::fromString($request->validated('parent_id')) 
                : null,
        );

        $category = $this->createCategoryHandler->handle($data);

        return redirect()
            ->route('categories.show', $category->getId()->toString())
            ->with('success', 'Categoria criada com sucesso.');
    }

    public function show(string $id): Response
    {
        $category = $this->categoryRepository->findById(
            CategoryId::fromString($id)
        );

        if (!$category) {
            abort(404, 'Categoria não encontrada.');
        }

        $children = $this->categoryRepository->findAllChildren(
            CategoryId::fromString($id)
        );

        return Inertia::render('Categories/Show', [
            'category' => $category,
            'children' => $children,
        ]);
    }

    public function edit(string $id): Response
    {
        $category = $this->categoryRepository->findById(
            CategoryId::fromString($id)
        );

        if (!$category) {
            abort(404, 'Categoria não encontrada.');
        }

        return Inertia::render('Categories/Edit', [
            'category' => $category,
            'categoryTypes' => array_map(
                fn (CategoryType $type) => [
                    'value' => $type->value,
                    'label' => $type->label(),
                ],
                CategoryType::cases()
            ),
            'parentCategories' => $this->categoryRepository->findAllRoot(),
        ]);
    }

    public function update(UpdateCategoryRequest $request, string $id): Response
    {
        $data = new UpdateCategoryData(
            id: CategoryId::fromString($id),
            name: $request->validated('name'),
            type: CategoryType::from($request->validated('type')),
            code: $request->validated('code'),
            description: $request->validated('description'),
            color: $request->validated('color'),
            icon: $request->validated('icon'),
            isOperating: $request->boolean('is_operating', true),
            isTaxDeductible: $request->boolean('is_tax_deductible', false),
            includeInReports: $request->boolean('include_in_reports', true),
            isDefault: $request->boolean('is_default', false),
            parentId: $request->validated('parent_id') 
                ? CategoryId::fromString($request->validated('parent_id')) 
                : null,
        );

        $category = $this->updateCategoryHandler->handle($data);

        return redirect()
            ->route('categories.show', $category->getId()->toString())
            ->with('success', 'Categoria atualizada com sucesso.');
    }

    public function destroy(string $id): Response
    {
        $this->deleteCategoryHandler->handle(
            CategoryId::fromString($id)
        );

        return redirect()
            ->route('categories.index')
            ->with('success', 'Categoria excluída com sucesso.');
    }

    public function tree(Request $request): Response
    {
        $type = $request->query('type');
        $categoryType = $type ? CategoryType::from($type) : null;

        $tree = $this->categoryRepository->findTree($categoryType);

        return Inertia::render('Categories/Tree', [
            'tree' => $tree,
            'categoryTypes' => array_map(
                fn (CategoryType $type) => [
                    'value' => $type->value,
                    'label' => $type->label(),
                ],
                CategoryType::cases()
            ),
            'selectedType' => $type,
        ]);
    }
}
