<?php

/**
 * Tests for TagController.
 */

namespace App\Tests\Controller;

use App\Entity\Tag;
use App\Entity\User;
use App\Service\TagServiceInterface;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Class TagControllerTest.
 */
class TagControllerTest extends WebTestCase
{
    private KernelBrowser $client;

    private EntityManagerInterface $manager;

    /**
     * Index page can be displayed.
     */
    public function testIndex(): void
    {
        $this->client->request('GET', '/tag');

        self::assertResponseIsSuccessful();
        self::assertSelectorExists('table');
    }

    /**
     * Index supports page parameter.
     */
    public function testIndexWithPage(): void
    {
        $this->client->request('GET', '/tag?page=2');

        self::assertResponseIsSuccessful();
        self::assertSelectorExists('table');
    }

    /**
     * New tag page can be displayed for administrator.
     */
    public function testNewGet(): void
    {
        $this->loginAdmin();
        $this->client->request('GET', '/tag/new');

        self::assertResponseIsSuccessful();
        self::assertSelectorExists('form');
    }

    /**
     * Empty tag title is rejected.
     */
    public function testNewRejectsEmptyTitle(): void
    {
        $service = $this->mockTagService();

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

        $this->client->request('POST', '/tag/new', [
            'tag' => [
                'title' => '',
            ],
        ]);

        self::assertResponseRedirects('/tag/new');
    }

    /**
     * Duplicate tag title is rejected.
     */
    public function testNewRejectsDuplicateTitle(): void
    {
        $service = $this->mockTagService();

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

        $this->client->request('POST', '/tag/new', [
            'tag' => [
                'title' => 'Duplicate tag '.uniqid(),
            ],
        ]);

        self::assertResponseRedirects('/tag/new');
    }

    /**
     * Edit tag page can be displayed.
     */
    public function testEditGet(): void
    {
        $tag = $this->persistTag();
        $this->loginAdmin();

        $this->client->request(
            'GET',
            '/tag/'.$tag->getId().'/edit'
        );

        self::assertResponseIsSuccessful();
        self::assertSelectorExists('form');
    }

    /**
     * Delete page can be displayed.
     */
    public function testDeleteGet(): void
    {
        $tag = $this->persistTag();
        $this->loginAdmin();

        $this->client->request(
            'GET',
            '/tag/'.$tag->getId().'/delete'
        );

        self::assertResponseIsSuccessful();
        self::assertSelectorExists('form');
    }

    /**
     * Create client and entity manager.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->client = static::createClient();

        $this->manager = static::getContainer()
            ->get(EntityManagerInterface::class);
    }

    /**
     * Get tag service mock.
     *
     * @return TagServiceInterface&MockObject
     */
    private function mockTagService(): TagServiceInterface
    {
        $service = $this->createMock(TagServiceInterface::class);

        static::getContainer()->set(
            TagServiceInterface::class,
            $service
        );

        return $service;
    }

    /**
     * Helper for creating admin user.
     *
     * @return User admin user
     */
    private function createAdmin(): User
    {
        $user = new User();
        $user->setEmail('tag-admin-'.uniqid('', true).'@test.com');
        $user->setPassword('password');
        $user->setRoles(['ROLE_ADMIN']);

        return $user;
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
     * Create a unique tag.
     *
     * @param string|null $title tag title
     *
     * @return Tag tag entity
     */
    private function createTag(?string $title = null): Tag
    {
        $tag = new Tag();

        $tag->setTitle(
            $title ?? 'Tag '.uniqid('', true)
        );

        return $tag;
    }

    /**
     * Persist a unique tag.
     *
     * @param string|null $title tag title
     *
     * @return Tag persisted tag
     */
    private function persistTag(?string $title = null): Tag
    {
        $tag = $this->createTag($title);

        $this->manager->persist($tag);
        $this->manager->flush();

        return $tag;
    }
}
