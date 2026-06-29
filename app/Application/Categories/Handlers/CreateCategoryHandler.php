<?php

declare(strict_types=1);

namespace App\Application\Categories\Handlers;

use App\Application\Categories\DTOs\CreateCategoryData;
use App\Domain\Categories\Entities\Category;
use App\Domain\Categories\Repositories\CategoryRepositoryInterface;
use App\Domain\Categories\ValueObjects\CategoryId;
use DateTimeImmutable;

final class CreateCategoryHandler
{
    public function __construct(
        private readonly CategoryRepositoryInterface $categoryRepository,
    ) {}

    public function handle(CreateCategoryData $data): Category
    {
        // Verificar se já existe uma categoria com o mesmo código
        if ($this->categoryRepository->existsWithCode($data->code)) {
            throw new \DomainException('Já existe uma categoria com este código.');
        }

        // Verificar se já existe uma categoria com o mesmo nome
        if ($this->categoryRepository->existsWithName($data->name)) {
            throw new \DomainException('Já existe uma categoria com este nome.');
        }

        // Verificar se a categoria pai existe (se fornecida)
        if ($data->parentId && !$this->categoryRepository->findById($data->parentId)) {
            throw new \DomainException('Categoria pai não encontrada.');
        }

        $now = new DateTimeImmutable();
        $category = new Category(
            id: CategoryId::generate(),
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
            createdAt: $now,
            updatedAt: $now,
        );

        $this->categoryRepository->save($category);

        return $category;
    }
}
