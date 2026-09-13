<?php

/**
 * Tests for CategoryController.
 */

namespace App\Tests\Controller;

use App\Entity\Category;
use App\Entity\User;
use App\Service\CategoryServiceInterface;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Class CategoryControllerTest.
 */
class CategoryControllerTest extends WebTestCase
{
    private KernelBrowser $client;

    private EntityManagerInterface $manager;

    /**
     * Index page can be displayed.
     */
    public function testIndex(): void
    {
        $this->client->request('GET', '/category');

        self::assertResponseIsSuccessful();
        self::assertPageTitleContains('Categories');
        self::assertSelectorExists('table');
    }

    /**
     * Index supports page parameter.
     */
    public function testIndexWithPage(): void
    {
        $this->client->request('GET', '/category?page=2');

        self::assertResponseIsSuccessful();
        self::assertSelectorExists('table');
    }

    /**
     * New category page can be displayed for administrator.
     */
    public function testNewGet(): void
    {
        $this->loginAdmin();

        $this->client->request('GET', '/category/new');

        self::assertResponseIsSuccessful();
        self::assertSelectorExists('form');
    }

    /**
     * Valid category can be created.
     */
    public function testNewSavesValidCategory(): void
    {
        $service = $this->mockCategoryService();

        $service
            ->expects($this->once())
            ->method('canBeEmpty')
            ->willReturn(true);

        $service
            ->expects($this->once())
            ->method('isTitleUnique')
            ->willReturn(true);

        $service
            ->expects($this->once())
            ->method('save')
            ->with(self::isInstanceOf(Category::class));

        $this->loginAdmin();

        $crawler = $this->client->request('GET', '/category/new');

        self::assertResponseIsSuccessful();

        $form = $crawler
            ->filter('form[name="category"]')
            ->form();

        $form['category[title]'] = 'New category '.uniqid('', true);

        $this->client->submit($form);

        self::assertResponseRedirects('/category');
    }

    /**
     * Empty category title is rejected.
     */
    public function testNewRejectsEmptyTitle(): void
    {
        $service = $this->mockCategoryService();

        $service
            ->expects($this->once())
            ->method('canBeEmpty')
            ->willReturn(false);

        $service
            ->expects($this->never())
            ->method('isTitleUnique');

        $service
            ->expects($this->never())
            ->method('save');

        $this->loginAdmin();

        $this->client->request('POST', '/category/new', [
            'category' => [
                'title' => '',
            ],
        ]);

        self::assertResponseRedirects('/category/new');
    }

    /**
     * Duplicate category title is rejected.
     */
    public function testNewRejectsDuplicateTitle(): void
    {
        $service = $this->mockCategoryService();

        $service
            ->expects($this->once())
            ->method('canBeEmpty')
            ->willReturn(true);

        $service
            ->expects($this->once())
            ->method('isTitleUnique')
            ->willReturn(false);

        $service
            ->expects($this->never())
            ->method('save');

        $this->loginAdmin();

        $this->client->request('POST', '/category/new', [
            'category' => [
                'title' => 'Duplicate category',
            ],
        ]);

        self::assertResponseRedirects('/category/new');
    }

    /**
     * Edit category page can be displayed.
     */
    public function testEditGet(): void
    {
        $category = $this->persistCategory();

        $this->loginAdmin();

        $this->client->request(
            'GET',
            '/category/'.$category->getId().'/edit'
        );

        self::assertResponseIsSuccessful();
        self::assertSelectorExists('form');
    }

    /**
     * Valid category can be edited.
     */
    public function testEditSavesValidCategory(): void
    {
        $category = $this->persistCategory();
        $service = $this->mockCategoryService();

        $service
            ->expects($this->once())
            ->method('canBeEmpty')
            ->willReturn(true);

        $service
            ->expects($this->once())
            ->method('isTitleUnique')
            ->willReturn(true);

        $service
            ->expects($this->once())
            ->method('save')
            ->with(self::isInstanceOf(Category::class));

        $this->loginAdmin();

        $crawler = $this->client->request(
            'GET',
            '/category/'.$category->getId().'/edit'
        );

        self::assertResponseIsSuccessful();

        $form = $crawler
            ->filter('form[name="category"]')
            ->form();

        $form['category[title]'] = 'Updated category '.uniqid('', true);

        $this->client->submit($form);

        self::assertResponseRedirects('/category');
    }

    /**
     * Empty category title is rejected during edit.
     */
    public function testEditRejectsEmptyTitle(): void
    {
        $category = $this->persistCategory();
        $service = $this->mockCategoryService();

        $service
            ->expects($this->once())
            ->method('canBeEmpty')
            ->willReturn(false);

        $service
            ->expects($this->never())
            ->method('isTitleUnique');

        $service
            ->expects($this->never())
            ->method('save');

        $this->loginAdmin();

        $crawler = $this->client->request(
            'GET',
            '/category/'.$category->getId().'/edit'
        );

        self::assertResponseIsSuccessful();

        $form = $crawler
            ->filter('form[name="category"]')
            ->form();

        $form['category[title]'] = '';

        $this->client->submit($form);

        self::assertResponseRedirects(
            '/category/'.$category->getId().'/edit'
        );
    }

    /**
     * Duplicate category title is rejected during edit.
     */
    public function testEditRejectsDuplicateTitle(): void
    {
        $category = $this->persistCategory();
        $service = $this->mockCategoryService();

        $service
            ->expects($this->once())
            ->method('canBeEmpty')
            ->willReturn(true);

        $service
            ->expects($this->once())
            ->method('isTitleUnique')
            ->willReturn(false);

        $service
            ->expects($this->never())
            ->method('save');

        $this->loginAdmin();

        $crawler = $this->client->request(
            'GET',
            '/category/'.$category->getId().'/edit'
        );

        self::assertResponseIsSuccessful();

        $form = $crawler
            ->filter('form[name="category"]')
            ->form();

        $form['category[title]'] = 'Duplicate category';

        $this->client->submit($form);

        self::assertResponseRedirects(
            '/category/'.$category->getId().'/edit'
        );
    }

    /**
     * Delete page can be displayed.
     */
    public function testDeleteGet(): void
    {
        $category = $this->persistCategory();

        $service = $this->mockCategoryService();

        $service
            ->expects($this->once())
            ->method('canBeDeleted')
            ->willReturn(true);

        $this->loginAdmin();

        $this->client->request(
            'GET',
            '/category/'.$category->getId().'/delete'
        );

        self::assertResponseIsSuccessful();
        self::assertSelectorExists('form');
    }

    /**
     * Category can be deleted.
     */
    public function testDelete(): void
    {
        $category = $this->persistCategory();
        $service = $this->mockCategoryService();

        $service
            ->expects($this->exactly(2))
            ->method('canBeDeleted')
            ->willReturn(true);

        $service
            ->expects($this->once())
            ->method('delete')
            ->with(self::isInstanceOf(Category::class));

        $this->loginAdmin();

        $crawler = $this->client->request(
            'GET',
            '/category/'.$category->getId().'/delete'
        );

        self::assertResponseIsSuccessful();

        $form = $crawler
            ->filter('form[name="category"]')
            ->form();

        $this->client->submit($form);

        self::assertResponseRedirects('/category');
    }

    /**
     * Category containing events cannot be deleted.
     */
    public function testDeleteRejectsNotEmptyCategory(): void
    {
        $category = $this->persistCategory();
        $service = $this->mockCategoryService();

        $service
            ->expects($this->once())
            ->method('canBeDeleted')
            ->willReturn(false);

        $service
            ->expects($this->never())
            ->method('delete');

        $this->loginAdmin();

        $this->client->request(
            'GET',
            '/category/'.$category->getId().'/delete'
        );

        self::assertResponseRedirects('/category');
    }

    /**
     * Create client and entity manager.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->client = static::createClient();
        $this->client->disableReboot();

        $this->manager = static::getContainer()
            ->get(EntityManagerInterface::class);
    }

    /**
     * Get category service mock.
     *
     * @return CategoryServiceInterface&MockObject
     */
    private function mockCategoryService(): CategoryServiceInterface
    {
        $service = $this->createMock(CategoryServiceInterface::class);

        static::getContainer()->set(
            CategoryServiceInterface::class,
            $service
        );

        return $service;
    }

    /**
     * Create a unique category.
     *
     * @param string|null $title Category title
     *
     * @return Category category entity
     */
    private function createCategory(?string $title = null): Category
    {
        $category = new Category();

        $category->setTitle(
            $title ?? 'Category '.uniqid('', true)
        );

        return $category;
    }

    /**
     * Persist a unique category.
     *
     * @param string|null $title Category title
     *
     * @return Category persisted category
     */
    private function persistCategory(?string $title = null): Category
    {
        $category = $this->createCategory($title);

        $this->manager->persist($category);
        $this->manager->flush();

        return $category;
    }

    /**
     * Helper for logging as administrator.
     */
    private function loginAdmin(): void
    {
        $user = $this->createAdmin();

        $this->manager->persist($user);
        $this->manager->flush();

        $this->client->loginUser($user);
    }

    /**
     * Helper for creating admin user.
     *
     * @return User admin user
     */
    private function createAdmin(): User
    {
        $user = new User();
        $user->setEmail('category-admin-'.uniqid('', true).'@test.com');
        $user->setPassword('password');
        $user->setRoles(['ROLE_ADMIN']);

        return $user;
    }
}
