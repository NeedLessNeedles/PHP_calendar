<?php

/**
 * Tests for CategoryController.
 */

namespace App\Tests\Controller;

use App\Entity\Category;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class CategoryControllerTest extends WebTestCase
{
    private const CATEGORY_ROUTE = '/category';

    /**
     * Test getUser()
     *
     * @param User $user
     * @param $email
     *
     * @return User
     */
    private function getUser($client, string $email): ?User
    {
        return $client->getContainer()
            ->get('doctrine')
            ->getRepository(User::class)
            ->findOneBy(['email' => $email]);
    }

    private function getCategory($client): ?Category
    {
        return $client->getContainer()
            ->get('doctrine')
            ->getRepository(Category::class)
            ->findOneBy([]);
    }

    /**
     * Test index route.
     *
     */
    public function testIndex(): void
    {
        // given
        $client = static::createClient();

        // when
        $client->request('GET', self::CATEGORY_ROUTE);

        // then
        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('table');
    }

    /**
     * Test show category.
     *
     */
    public function testShowCategory(): void
    {
        // given
        $client = static::createClient();
        $category = $this->getCategory($client);

        $this->assertNotNull($category);

        // when
        $client->request(
            'GET',
            self::CATEGORY_ROUTE.'/'.$category->getId()
        );

        // then
        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('body');
    }

    /**
     * Tests if new category can be created only by admin.
     *
     */
    public function testNewRequiresAdmin(): void
    {
        // given
        $client = static::createClient();

        // when
        $client->request('GET', self::CATEGORY_ROUTE.'/new');

        // then
        $this->assertResponseRedirects();
    }

    /**
     * Tests for regular user case.
     *
     */
    public function testNewRequiresAdminForRegularUser(): void
    {
        // given
        $client = static::createClient();

        $user = $this->getUser(
            $client,
            'user.first@gmail.com'
        );

        $this->assertNotNull($user);
        $client->loginUser($user);

        // when
        $client->request(
            'GET',
            self::CATEGORY_ROUTE.'/new'
        );

        // then
        $this->assertResponseStatusCodeSame(403);
    }

    public function testNewAsAdmin(): void
    {
        // given
        $client = static::createClient();
        $admin = $this->getUser(
            $client,
            'admin.first@gmail.com'
        );
        $this->assertNotNull($admin);
        $client->loginUser($admin);

        // when
        $client->request('POST', self::CATEGORY_ROUTE.'/new', [
            'category' => [
                'title' => 'Test category',
            ],
        ]);

        // then
        $this->assertResponseRedirects(self::CATEGORY_ROUTE);
        $repository = static::getContainer()
            ->get('doctrine')
            ->getRepository(Category::class);
        $category = $repository->findOneBy([
            'title' => 'Test category',
        ]);
        $this->assertNotNull($category);
    }

    public function testEditCategory(): void
    {
        $client = static::createClient();

        $admin = $this->getUser($client, 'admin.first@gmail.com');
        $category = $this->getCategory($client);

        $client->loginUser($admin);

        $crawler = $client->request('GET', '/category/'.$category->getId().'/edit');

        $client->request('POST', '/category/'.$category->getId().'/edit', [
            '_token' => 'category_edit',
            'category' => [
                'title' => 'Updated title',
            ],
        ]);

        $this->assertResponseRedirects('/category');
    }

    /**
     * Test for invalid token.
     *
     */
    public function testEditCategoryWithInvalidCsrfToken(): void
    {
        // given
        $client = static::createClient();
        $admin = $this->getUser(
            $client,
            'admin.first@gmail.com'
        );
        $category = $this->getCategory($client);

        $this->assertNotNull($admin);
        $this->assertNotNull($category);

        $client->loginUser($admin);

        // when
        $client->request(
            'POST',
            self::CATEGORY_ROUTE.'/'.$category->getId().'/edit',
            [
                '_token' => 'invalid-token',
                'category' => [
                    'title' => 'Should not be saved',
                ],
            ]
        );

        // then
        $this->assertResponseStatusCodeSame(403);
    }

    /**
     * Test for category removal.
     *
     */
    public function testDeleteCategoryWithInvalidCsrfToken(): void
    {
        // given
        $client = static::createClient();

        $admin = $this->getUser(
            $client,
            'admin.first@gmail.com'
        );

        $category = $this->getCategory($client);

        $this->assertNotNull($admin);
        $this->assertNotNull($category);

        $client->loginUser($admin);

        $categoryId = $category->getId();

        // when
        $client->request(
            'POST',
            self::CATEGORY_ROUTE.'/'.$categoryId,
            [
                '_token' => 'invalid-token',
            ]
        );

        // then
        $this->assertResponseRedirects(self::CATEGORY_ROUTE);

        $repository = static::getContainer()
            ->get('doctrine')
            ->getRepository(Category::class);

        $this->assertNotNull($repository->find($categoryId));
    }
}
