<?php

/**
 * Tests for TagController.
 */

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use App\Entity\Tag;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Class TagControllerTest.
 */
class TagControllerTest extends WebTestCase
{
    private function loginAdmin(): void
    {
        $client = static::createClient();
        $em = static::getContainer()->get(EntityManagerInterface::class);

        $admin = $em->getRepository(\App\Entity\User::class)->findOneBy([
            'email' => 'admin.first@gmail.com',
        ]);

        $client->loginUser($admin);
    }

    public function testTagIndexPage(): void
    {
        $client = static::createClient();
        $client->request('GET', '/tag');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('body');
    }

    public function testTagShowPage(): void
    {
        $client = static::createClient();
        $em = static::getContainer()->get(EntityManagerInterface::class);
        $tag = $em->getRepository(Tag::class)->findOneBy([]);
        $client->request('GET', '/tag/'.$tag->getId());

        $this->assertResponseIsSuccessful();
    }

    public function testCreateTagAsAdmin(): void
    {
        $client = static::createClient();
        $this->loginAdmin();

        $client->request('POST', '/tag/new', [
            'title' => 'test-tag',
        ]);

        $this->assertResponseRedirects();
        $em = static::getContainer()->get(EntityManagerInterface::class);
        $tag = $em->getRepository(Tag::class)->findOneBy([
            'title' => 'test-tag',
        ]);

        $this->assertNotNull($tag);
    }

    public function testEditTag(): void
    {
        $client = static::createClient();
        $this->loginAdmin();

        $em = static::getContainer()->get(EntityManagerInterface::class);
        $tag = $em->getRepository(Tag::class)->findOneBy([]);

        $client->request('POST', '/tag/'.$tag->getId().'/edit', [
            'title' => 'updated-tag',
            '_token' => 'tag_edit',
        ]);

        $this->assertResponseRedirects();

        $updated = $em->getRepository(Tag::class)->find($tag->getId());
        $this->assertSame('updated-tag', $updated->getTitle());
    }

    public function testDeleteTag(): void
    {
        $client = static::createClient();
        $this->loginAdmin();

        $em = static::getContainer()->get(EntityManagerInterface::class);

        $tag = new Tag();
        $tag->setTitle('to-delete');

        $em->persist($tag);
        $em->flush();

        $client->request('POST', '/tag/'.$tag->getId(), [
            '_token' => 'delete'.$tag->getId(),
        ]);

        $this->assertResponseRedirects();

        $deleted = $em->getRepository(Tag::class)->find($tag->getId());
        $this->assertNull($deleted);
    }
}
