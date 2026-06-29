<?php

declare(strict_types=1);

namespace App\Application\Categories\Handlers;

use App\Application\Categories\DTOs\UpdateCategoryData;
use App\Domain\Categories\Entities\Category;
use App\Domain\Categories\Repositories\CategoryRepositoryInterface;
use App\Domain\Categories\ValueObjects\CategoryId;
use DateTimeImmutable;

final class UpdateCategoryHandler
{
    public function __construct(
        private readonly CategoryRepositoryInterface $categoryRepository,
    ) {}

    public function handle(UpdateCategoryData $data): Category
    {
        $category = $this->categoryRepository->findById($data->id);
        if (!$category) {
            throw new \DomainException('Categoria não encontrada.');
        }

        // Verificar se já existe outra categoria com o mesmo código (excluindo a atual)
        $existingCategoryByCode = $this->categoryRepository->findByCode($data->code);
        if ($existingCategoryByCode && !$existingCategoryByCode->getId()->equals($data->id)) {
            throw new \DomainException('Já existe outra categoria com este código.');
        }

        // Verificar se já existe outra categoria com o mesmo nome (excluindo a atual)
        $existingCategoryByName = $this->categoryRepository->findByName($data->name);
        if ($existingCategoryByName && !$existingCategoryByName->getId()->equals($data->id)) {
            throw new \DomainException('Já existe outra categoria com este nome.');
        }

        // Verificar se a categoria pai existe (se fornecida)
        if ($data->parentId && !$this->categoryRepository->findById($data->parentId)) {
            throw new \DomainException('Categoria pai não encontrada.');
        }

        // Verificar se não está tentando tornar uma categoria pai de si mesma
        if ($data->parentId && $data->parentId->equals($data->id)) {
            throw new \DomainException('Uma categoria não pode ser pai de si mesma.');
        }

        // Verificar se não está criando um loop na hierarquia
        if ($data->parentId) {
            $this->checkHierarchyLoop($data->id, $data->parentId);
        }

        // Se esta categoria for marcada como padrão, desmarcar outras do mesmo tipo
        if ($data->isDefault && !$category->isDefault()) {
            $this->unsetOtherDefaultCategories($data->id, $data->type);
        }

        $updatedCategory = new Category(
            id: $data->id,
            name: $data->name,
            type: $data->type,
            code: $data->code,
            description: $data->description,
            color: $data->color,
            icon: $data->icon,
            isOperating: $data->isOperating,
            isTaxDeductible: $data->isTaxDeductible,
            includeInReports: $data->includeInReports,
            isDefault: $data->isDefault,
            parentId: $data->parentId,
            createdAt: $category->getCreatedAt(),
            updatedAt: new DateTimeImmutable(),
        );

        $this->categoryRepository->save($updatedCategory);

        return $updatedCategory;
    }

    private function checkHierarchyLoop(CategoryId $categoryId, CategoryId $parentId): void
    {
        $currentParentId = $parentId;
        $visited = [$categoryId->toString() => true];

        while ($currentParentId) {
            if (isset($visited[$currentParentId->toString()])) {
                throw new \DomainException('Loop na hierarquia de categorias detectado.');
            }

            $visited[$currentParentId->toString()] = true;
            $parentCategory = $this->categoryRepository->findById($currentParentId);
            
            if (!$parentCategory) {
                break;
            }

            $currentParentId = $parentCategory->getParentId();
        }
    }

    private function unsetOtherDefaultCategories(CategoryId $excludeId, \App\Domain\Categories\ValueObjects\CategoryType $type): void
    {
        $defaultCategories = $this->categoryRepository->findAll(
            type: $type,
            isDefault: true
        );

        foreach ($defaultCategories['data'] as $category) {
            if (!$category->getId()->equals($excludeId)) {
                $updatedCategory = new Category(
                    id: $category->getId(),
                    name: $category->getName(),
                    type: $category->getType(),
                    code: $category->getCode(),
                    description: $category->getDescription(),
                    color: $category->getColor(),
                    icon: $category->getIcon(),
                    isOperating: $category->isOperating(),
                    isTaxDeductible: $category->isTaxDeductible(),
                    includeInReports: $category->isIncludeInReports(),
                    isDefault: false,
                    parentId: $category->getParentId(),
                    createdAt: $category->getCreatedAt(),
                    updatedAt: new DateTimeImmutable(),
                );

                $this->categoryRepository->save($updatedCategory);
            }
        }
    }
}