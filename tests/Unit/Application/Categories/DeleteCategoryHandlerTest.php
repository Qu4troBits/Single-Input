<?php

declare(strict_types=1);

namespace Tests\Unit\Application\Categories;

use App\Application\Categories\Handlers\DeleteCategoryHandler;
use App\Domain\Categories\Entities\Category;
use App\Domain\Categories\Repositories\CategoryRepositoryInterface;
use App\Domain\Categories\ValueObjects\CategoryId;
use App\Domain\Categories\ValueObjects\CategoryType;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

final class DeleteCategoryHandlerTest extends TestCase
{
    private CategoryRepositoryInterface $categoryRepository;
    private DeleteCategoryHandler $handler;

    protected function setUp(): void
    {
        parent::setUp();

        $this->categoryRepository = $this->createMock(CategoryRepositoryInterface::class);
        $this->handler = new DeleteCategoryHandler($this->categoryRepository);
    }

    /** @test */
    public function it_deletes_category_successfully(): void
    {
        $categoryId = CategoryId::fromString('cat_12345678-1234-1234-1234-123456789012');
        $category = new Category(
            id: $categoryId,
            name: 'Categoria Teste',
            type: CategoryType::EXPENSE,
            code: 'EXP001',
            description: null,
            color: null,
            icon: null,
            isOperating: true,
            isTaxDeductible: false,
            includeInReports: true,
            isDefault: false,
            parentId: null,
            createdAt: new DateTimeImmutable('2024-01-01'),
            updatedAt: new DateTimeImmutable('2024-01-01'),
        );

        $this->categoryRepository
            ->expects($this->once())
            ->method('findById')
            ->with($categoryId)
            ->willReturn($category);

        $this->categoryRepository
            ->expects($this->once())
            ->method('hasChildren')
            ->with($categoryId)
            ->willReturn(false);

        $this->categoryRepository
            ->expects($this->once())
            ->method('hasTransactions')
            ->with($categoryId)
            ->willReturn(false);

        $this->categoryRepository
            ->expects($this->once())
            ->method('delete')
            ->with($categoryId);

        $this->handler->handle($categoryId);
    }

    /** @test */
    public function it_throws_exception_when_category_not_found(): void
    {
        $categoryId = CategoryId::fromString('cat_12345678-1234-1234-1234-123456789012');

        $this->categoryRepository
            ->expects($this->once())
            ->method('findById')
            ->with($categoryId)
            ->willReturn(null);

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Categoria não encontrada.');

        $this->handler->handle($categoryId);
    }

    /** @test */
    public function it_throws_exception_when_category_has_children(): void
    {
        $categoryId = CategoryId::fromString('cat_12345678-1234-1234-1234-123456789012');
        $category = new Category(
            id: $categoryId,
            name: 'Categoria Teste',
            type: CategoryType::EXPENSE,
            code: 'EXP001',
            description: null,
            color: null,
            icon: null,
            isOperating: true,
            isTaxDeductible: false,
            includeInReports: true,
            isDefault: false,
            parentId: null,
            createdAt: new DateTimeImmutable('2024-01-01'),
            updatedAt: new DateTimeImmutable('2024-01-01'),
        );

        $this->categoryRepository
            ->expects($this->once())
            ->method('findById')
            ->with($categoryId)
            ->willReturn($category);

        $this->categoryRepository
            ->expects($this->once())
            ->method('hasChildren')
            ->with($categoryId)
            ->willReturn(true);

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Não é possível excluir uma categoria que possui subcategorias.');

        $this->handler->handle($categoryId);
    }

    /** @test */
    public function it_throws_exception_when_category_has_transactions(): void
    {
        $categoryId = CategoryId::fromString('cat_12345678-1234-1234-1234-123456789012');
        $category = new Category(
            id: $categoryId,
            name: 'Categoria Teste',
            type: CategoryType::EXPENSE,
            code: 'EXP001',
            description: null,
            color: null,
            icon: null,
            isOperating: true,
            isTaxDeductible: false,
            includeInReports: true,
            isDefault: false,
            parentId: null,
            createdAt: new DateTimeImmutable('2024-01-01'),
            updatedAt: new DateTimeImmutable('2024-01-01'),
        );

        $this->categoryRepository
            ->expects($this->once())
            ->method('findById')
            ->with($categoryId)
            ->willReturn($category);

        $this->categoryRepository
            ->expects($this->once())
            ->method('hasChildren')
            ->with($categoryId)
            ->willReturn(false);

        $this->categoryRepository
            ->expects($this->once())
            ->method('hasTransactions')
            ->with($categoryId)
            ->willReturn(true);

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Não é possível excluir uma categoria com transações associadas.');

        $this->handler->handle($categoryId);
    }

    /** @test */
    public function it_throws_exception_when_category_is_default(): void
    {
        $categoryId = CategoryId::fromString('cat_12345678-1234-1234-1234-123456789012');
        $category = new Category(
            id: $categoryId,
            name: 'Categoria Teste',
            type: CategoryType::EXPENSE,
            code: 'EXP001',
            description: null,
            color: null,
            icon: null,
            isOperating: true,
            isTaxDeductible: false,
            includeInReports: true,
            isDefault: true,
            parentId: null,
            createdAt: new DateTimeImmutable('2024-01-01'),
            updatedAt: new DateTimeImmutable('2024-01-01'),
        );

        $this->categoryRepository
            ->expects($this->once())
            ->method('findById')
            ->with($categoryId)
            ->willReturn($category);

        $this->categoryRepository
            ->expects($this->once())
            ->method('hasChildren')
            ->with($categoryId)
            ->willReturn(false);

        $this->categoryRepository
            ->expects($this->once())
            ->method('hasTransactions')
            ->with($categoryId)
            ->willReturn(false);

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Não é possível excluir uma categoria padrão.');

        $this->handler->handle($categoryId);
    }

    /** @test */
    public function it_archives_category_successfully(): void
    {
        $categoryId = CategoryId::fromString('cat_12345678-1234-1234-1234-123456789012');
        $category = new Category(
            id: $categoryId,
            name: 'Categoria Teste',
            type: CategoryType::EXPENSE,
            code: 'EXP001',
            description: null,
            color: null,
            icon: null,
            isOperating: true,
            isTaxDeductible: false,
            includeInReports: true,
            isDefault: false,
            parentId: null,
            createdAt: new DateTimeImmutable('2024-01-01'),
            updatedAt: new DateTimeImmutable('2024-01-01'),
        );

        $this->categoryRepository
            ->expects($this->once())
            ->method('findById')
            ->with($categoryId)
            ->willReturn($category);

        // Mock para verificar se já está arquivada
        $this->categoryRepository
            ->expects($this->once())
            ->method('findAll')
            ->willReturn(['data' => []]);

        $this->categoryRepository
            ->expects($this->once())
            ->method('save')
            ->with($this->callback(function (Category $updatedCategory) use ($categoryId) {
                return $updatedCategory->getId()->equals($categoryId);
            }));

        $this->handler->archive($categoryId);
    }

    /** @test */
    public function it_throws_exception_when_archiving_already_archived_category(): void
    {
        $categoryId = CategoryId::fromString('cat_12345678-1234-1234-1234-123456789012');
        $category = new Category(
            id: $categoryId,
            name: 'Categoria Teste',
            type: CategoryType::EXPENSE,
            code: 'EXP001',
            description: null,
            color: null,
            icon: null,
            isOperating: true,
            isTaxDeductible: false,
            includeInReports: true,
            isDefault: false,
            parentId: null,
            createdAt: new DateTimeImmutable('2024-01-01'),
            updatedAt: new DateTimeImmutable('2024-01-01'),
        );

        $this->categoryRepository
            ->expects($this->once())
            ->method('findById')
            ->with($categoryId)
            ->willReturn($category);

        // Mock para simular que a categoria já está arquivada
        // (precisaríamos de um método isArchived na entidade Category)
        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('A categoria já está arquivada.');

        $this->handler->archive($categoryId);
    }

    /** @test */
    public function it_throws_exception_when_archiving_default_category(): void
    {
        $categoryId = CategoryId::fromString('cat_12345678-1234-1234-1234-123456789012');
        $category = new Category(
            id: $categoryId,
            name: 'Categoria Teste',
            type: CategoryType::EXPENSE,
            code: 'EXP001',
            description: null,
            color: null,
            icon: null,
            isOperating: true,
            isTaxDeductible: false,
            includeInReports: true,
            isDefault: true,
            parentId: null,
            createdAt: new DateTimeImmutable('2024-01-01'),
            updatedAt: new DateTimeImmutable('2024-01-01'),
        );

        $this->categoryRepository
            ->expects($this->once())
            ->method('findById')
            ->with($categoryId)
            ->willReturn($category);

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Não é possível arquivar uma categoria padrão.');

        $this->handler->archive($categoryId);
    }

    /** @test */
    public function it_restores_category_successfully(): void
    {
        $categoryId = CategoryId::fromString('cat_12345678-1234-1234-1234-123456789012');
        $category = new Category(
            id: $categoryId,
            name: 'Categoria Teste',
            type: CategoryType::EXPENSE,
            code: 'EXP001',
            description: null,
            color: null,
            icon: null,
            isOperating: true,
            isTaxDeductible: false,
            includeInReports: true,
            isDefault: false,
            parentId: null,
            createdAt: new DateTimeImmutable('2024-01-01'),
            updatedAt: new DateTimeImmutable('2024-01-01'),
        );

        $this->categoryRepository
            ->expects($this->once())
            ->method('findById')
            ->with($categoryId)
            ->willReturn($category);

        // Mock para verificar se não está arquivada
        $this->categoryRepository
            ->expects($this->once())
            ->method('findAll')
            ->willReturn(['data' => []]);

        $this->categoryRepository
            ->expects($this->once())
            ->method('save')
            ->with($this->callback(function (Category $updatedCategory) use ($categoryId) {
                return $updatedCategory->getId()->equals($categoryId);
            }));

        $this->handler->restore($categoryId);
    }

    /** @test */
    public function it_throws_exception_when_restoring_not_archived_category(): void
    {
        $categoryId = CategoryId::fromString('cat_12345678-1234-1234-1234-123456789012');
        $category = new Category(
            id: $categoryId,
            name: 'Categoria Teste',
            type: CategoryType::EXPENSE,
            code: 'EXP001',
            description: null,
            color: null,
            icon: null,
            isOperating: true,
            isTaxDeductible: false,
            includeInReports: true,
            isDefault: false,
            parentId: null,
            createdAt: new DateTimeImmutable('2024-01-01'),
            updatedAt: new DateTimeImmutable('2024-01-01'),
        );

        $this->categoryRepository
            ->expects($this->once())
            ->method('findById')
            ->with($categoryId)
            ->willReturn($category);

        // Mock para simular que a categoria não está arquivada
        // (precisaríamos de um método isArchived na entidade Category)
        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('A categoria não está arquivada.');

        $this->handler->restore($categoryId);
    }
}