<?php

declare(strict_types=1);

namespace App\Application\Categories\Handlers;

use App\Domain\Categories\Repositories\CategoryRepositoryInterface; 
use App\Domain\Categories\ValueObjects\CategoryId;
use DateTimeImmutable;

final class DeleteCategoryHandler
{
    public function __construct(
        private readonly CategoryRepositoryInterface $categoryRepository,
    ) {}

    public function handle(CategoryId $id): void
    {
        $category = $this->categoryRepository->findById($id);
        if (!$category) {
            throw new \DomainException('Categoria não encontrada.');
        }

        // Verificar se a categoria tem subcategorias
        if ($this->categoryRepository->hasChildren($id)) {
            throw new \DomainException('Não é possível excluir uma categoria que possui subcategorias.');
        }

        // Verificar se existem transações associadas à categoria
        if ($this->categoryRepository->hasTransactions($id)) {
            throw new \DomainException('Não é possível excluir uma categoria com transações associadas.');
        }

        // Verificar se é uma categoria padrão
        if ($category->isDefault()) {
            throw new \DomainException('Não é possível excluir uma categoria padrão.');
        }

        $this->categoryRepository->delete($category);
    }

    public function archive(CategoryId $id): void
    {
        $category = $this->categoryRepository->findById($id);
        if (!$category) {
            throw new \DomainException('Categoria não encontrada.');
        }

        // Verificar se já está arquivada
        if ($category->isArchived()) {
            throw new \DomainException('A categoria já está arquivada.');
        }

        // Verificar se é uma categoria padrão
        if ($category->isDefault()) {
            throw new \DomainException('Não é possível arquivar uma categoria padrão.');
        }

        $category->archive(new DateTimeImmutable());
        $this->categoryRepository->save($category);
    }

    public function restore(CategoryId $id): void
    {
        $category = $this->categoryRepository->findById($id);
        if (!$category) {
            throw new \DomainException('Categoria não encontrada.');
        }

        // Verificar se não está arquivada
        if (!$category->isArchived()) {
            throw new \DomainException('A categoria não está arquivada.');
        }

        $category->restore(new DateTimeImmutable());
        $this->categoryRepository->save($category);
    }
}