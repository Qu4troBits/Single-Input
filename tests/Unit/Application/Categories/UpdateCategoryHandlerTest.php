<?php

declare(strict_types=1);

namespace Tests\Unit\Application\Categories;

use App\Application\Categories\DTOs\UpdateCategoryData;
use App\Application\Categories\Handlers\UpdateCategoryHandler;
use App\Domain\Categories\Entities\Category;
use App\Domain\Categories\Repositories\CategoryRepositoryInterface;
use App\Domain\Categories\ValueObjects\CategoryId;
use App\Domain\Categories\ValueObjects\CategoryStatus;
use App\Domain\Categories\ValueObjects\CategoryType;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

final class UpdateCategoryHandlerTest extends TestCase
{
    private CategoryRepositoryInterface&MockObject $categoryRepository;
    private UpdateCategoryHandler $handler;

    protected function setUp(): void
    { 
        parent::setUp();

        $this->categoryRepository = $this->createMock(CategoryRepositoryInterface::class);
        $this->handler = new UpdateCategoryHandler($this->categoryRepository);
    }

    public function it_updates_category_successfully(): void
    {
        $categoryId = CategoryId::generate();
        $existingCategory = new Category(
            id: $categoryId,
            name: 'Categoria Antiga',
            type: CategoryType::EXPENSE,
            code: 'EXP001',
            description: 'Descrição antiga',
            color: '#FF0000',
            icon: '💰',
            isOperating: true,
            isTaxDeductible: false,
            includeInReports: true,
            isDefault: false,
            parentId: null,
            createdAt: new DateTimeImmutable('2024-01-01'),
            updatedAt: new DateTimeImmutable('2024-01-01'),
        );

        $updateData = new UpdateCategoryData(
            id: $categoryId,
            name: 'Categoria Nova',
            type: CategoryType::REVENUE,
            status: CategoryStatus::ACTIVE,
            code: 'REV001',
            description: 'Descrição nova',
            color: '#00FF00',
            icon: '📈',
            isOperating: false,
            isTaxDeductible: true,
            includeInReports: false,
            isDefault: true,
            parentId: null,
        );

        $this->categoryRepository
            ->expects($this->once())
            ->method('findById')
            ->with($this->callback(fn ($id) => $id instanceof CategoryId))
            ->willReturn($existingCategory);

        $this->categoryRepository
            ->expects($this->once())
            ->method('findByCode')
            ->with('REV001')
            ->willReturn(null);

        $this->categoryRepository
            ->expects($this->once())
            ->method('findByName')
            ->with('Categoria Nova')
            ->willReturn(null);

        $this->categoryRepository
            ->expects($this->any())
            ->method('findAll')
            ->willReturn([]);

        $this->categoryRepository
            ->expects($this->once())
            ->method('save')
            ->with($this->callback(function (Category $category) use ($categoryId) {
                return $category->getId()->equals($categoryId);
            }));

        $result = $this->handler->handle($categoryId, $updateData);

        $this->assertInstanceOf(Category::class, $result);
        $this->assertTrue($result->getId()->equals($categoryId));
    }

    public function it_throws_exception_when_category_not_found(): void
    {
        $categoryId = CategoryId::generate();
        $updateData = new UpdateCategoryData(
            id: $categoryId,
            name: 'Categoria Nova',
            type: CategoryType::REVENUE,
            status: CategoryStatus::ACTIVE,
            code: 'REV001',
        );

        $this->categoryRepository
            ->expects($this->once())
            ->method('findById')
            ->with($this->callback(fn ($id) => $id instanceof CategoryId))
            ->willReturn(null);

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Categoria não encontrada.');

        $this->handler->handle($categoryId, $updateData);
    }

    public function it_throws_exception_when_code_already_exists(): void
    {
        $categoryId = CategoryId::generate();
        
        $existingCategory = new Category(
            id: $categoryId,
            name: 'Categoria Antiga',
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

        $updateData = new UpdateCategoryData(
            id: $categoryId,
            name: 'Categoria Nova',
            type: CategoryType::REVENUE,
            status: CategoryStatus::ACTIVE,
            code: 'REV001',
        );

        $this->categoryRepository
            ->expects($this->once())
            ->method('findById')
            ->with($this->callback(fn ($id) => $id instanceof CategoryId))
            ->willReturn($existingCategory);

        $otherCategory = new Category(
            id: CategoryId::generate(),
            name: 'Outra Categoria',
            type: CategoryType::REVENUE,
            code: 'REV001',
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
            ->method('findByCode')
            ->with('REV001')
            ->willReturn($otherCategory);

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Já existe outra categoria com este código.');

        $this->handler->handle($categoryId, $updateData);
    }

    public function it_throws_exception_when_name_already_exists(): void
    {
        $categoryId = CategoryId::generate();
        
        $existingCategory = new Category(
            id: $categoryId,
            name: 'Categoria Antiga',
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

        $updateData = new UpdateCategoryData(
            id: $categoryId,
            name: 'Categoria Nova',
            type: CategoryType::REVENUE,
            status: CategoryStatus::ACTIVE,
            code: 'REV001',
        );

        $otherCategory = new Category(
            id: CategoryId::generate(),
            name: 'Categoria Nova',
            type: CategoryType::REVENUE,
            code: 'REV002',
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
            ->with($this->callback(fn ($id) => $id instanceof CategoryId))
            ->willReturn($existingCategory);

        $this->categoryRepository
            ->expects($this->once())
            ->method('findByCode')
            ->with('REV001')
            ->willReturn(null);

        $this->categoryRepository
            ->expects($this->once())
            ->method('findByName')
            ->with('Categoria Nova')
            ->willReturn($otherCategory);

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Já existe outra categoria com este nome.');

        $this->handler->handle($categoryId, $updateData);
    }

    public function it_throws_exception_when_parent_not_found(): void
    {
        $categoryId = CategoryId::generate();
        
        $existingCategory = new Category(
            id: $categoryId,
            name: 'Categoria Antiga',
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

        $updateData = new UpdateCategoryData(
            id: $categoryId,
            name: 'Categoria Nova',
            type: CategoryType::REVENUE,
            status: CategoryStatus::ACTIVE,
            code: 'REV001',
            parentId: CategoryId::generate(),
        );

        $this->categoryRepository
            ->expects($this->any())
            ->method('findById')
            ->willReturnOnConsecutiveCalls($existingCategory, null);

        $this->categoryRepository
            ->expects($this->once())
            ->method('findByCode')
            ->with('REV001')
            ->willReturn(null);

        $this->categoryRepository
            ->expects($this->once())
            ->method('findByName')
            ->with('Categoria Nova')
            ->willReturn(null);

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Categoria pai não encontrada.');

        $this->handler->handle($categoryId, $updateData);
    }

    public function it_throws_exception_when_trying_to_be_parent_of_itself(): void
    {
        $categoryId = CategoryId::generate();
        
        $existingCategory = new Category(
            id: $categoryId,
            name: 'Categoria Antiga',
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

        $updateData = new UpdateCategoryData(
            id: $categoryId,
            name: 'Categoria Nova',
            type: CategoryType::REVENUE,
            status: CategoryStatus::ACTIVE,
            code: 'REV001',
            parentId: $categoryId,
        );

        $this->categoryRepository
            ->expects($this->once())
            ->method('findById')
            ->with($this->callback(fn ($id) => $id instanceof CategoryId))
            ->willReturn($existingCategory);

        $this->categoryRepository
            ->expects($this->once())
            ->method('findByCode')
            ->with('REV001')
            ->willReturn(null);

        $this->categoryRepository
            ->expects($this->once())
            ->method('findByName')
            ->with('Categoria Nova')
            ->willReturn(null);

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Uma categoria não pode ser pai de si mesma.');

        $this->handler->handle($categoryId, $updateData);
    }

    public function it_unsets_other_default_categories_when_marking_as_default(): void
    {
        $categoryId = CategoryId::generate();
        
        $existingCategory = new Category(
            id: $categoryId,
            name: 'Categoria Antiga',
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

        $otherCategory = new Category(
            id: CategoryId::generate(),
            name: 'Outra Categoria',
            type: CategoryType::EXPENSE,
            code: 'EXP002',
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

        $updateData = new UpdateCategoryData(
            id: $categoryId,
            name: 'Categoria Nova',
            type: CategoryType::EXPENSE,
            status: CategoryStatus::ACTIVE,
            code: 'REV001',
            isDefault: true,
        );

        $this->categoryRepository
            ->expects($this->once())
            ->method('findById')
            ->with($this->callback(fn ($id) => $id instanceof CategoryId))
            ->willReturn($existingCategory);

        $this->categoryRepository
            ->expects($this->once())
            ->method('findByCode')
            ->with('REV001')
            ->willReturn(null);

        $this->categoryRepository
            ->expects($this->once())
            ->method('findByName')
            ->with('Categoria Nova')
            ->willReturn(null);

        $this->categoryRepository
            ->expects($this->any())
            ->method('findAll')
            ->willReturn([$otherCategory]);

        // Expect two saves: one for unsetting the other default category, one for updating the current category
        $this->categoryRepository
            ->expects($this->exactly(2))
            ->method('save');

        $result = $this->handler->handle($categoryId, $updateData);

        $this->assertInstanceOf(Category::class, $result);
        $this->assertTrue($result->isDefault());
    }
}
