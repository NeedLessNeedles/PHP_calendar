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
    private function getUser($client, string $email): User
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

    public function testIndex(): void
    {
        $client = static::createClient();

        $client->request('GET', '/category');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('body');
    }

    public function testShowCategory(): void
    {
        $client = static::createClient();

        $category = $this->getCategory($client);
        $this->assertNotNull($category);

        $client->request('GET', '/category/' . $category->getId());

        $this->assertResponseIsSuccessful();
    }

    public function testNewRequiresAdmin(): void
    {
        $client = static::createClient();

        $client->request('GET', '/category/new');

        $this->assertResponseRedirects();
    }

    public function testNewAsAdmin(): void
    {
        $client = static::createClient();

        $admin = $this->getUser($client, 'admin.first@gmail.com');
        $client->loginUser($admin);

        $client->request('POST', '/category/new', [
            'category' => [
                'title' => 'Test category',
            ],
        ]);

        $this->assertResponseRedirects('/category');
    }

    public function testEditCategory(): void
    {
        $client = static::createClient();

        $admin = $this->getUser($client, 'admin.first@gmail.com');
        $category = $this->getCategory($client);

        $client->loginUser($admin);

        $crawler = $client->request('GET', '/category/' . $category->getId() . '/edit');

        $client->request('POST', '/category/' . $category->getId() . '/edit', [
            '_token' => 'category_edit',
            'category' => [
                'title' => 'Updated title',
            ],
        ]);

        $this->assertResponseRedirects('/category');
    }

    public function testDeleteCategory(): void
    {
        $client = static::createClient();

        $admin = $this->getUser($client, 'admin.first@gmail.com');
        $category = $this->getCategory($client);

        $client->loginUser($admin);

        $client->request('POST', '/category/' . $category->getId(), [
            '_token' => 'delete' . $category->getId(),
        ]);

        $this->assertResponseRedirects('/category');
    }
}
