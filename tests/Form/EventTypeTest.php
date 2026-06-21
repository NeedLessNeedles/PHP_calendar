<?php

/**
 * EventType tests.
 */

namespace App\Tests\Form;

use App\Entity\Category;
use App\Entity\Event;
use App\Entity\Tag;
use App\Form\EventType;
use Symfony\Component\Form\Test\TypeTestCase;

/**
 * Class EventTypeTest.
 */
class EventTypeTest extends TypeTestCase
{
    /**
     * Data validation test.
     */
    public function testSubmitValidData(): void
    {
        $formData = [
            'title' => 'Test event',
            'description' => 'Some description',
            'location' => 'Warsaw',
            'startDate' => '2025-01-01 10:00:00',
            'endDate' => '2025-01-02 10:00:00',
        ];

        $model = new Event();
        $form = $this->factory->create(EventType::class, $model);

        $expected = new Event();
        $expected->setTitle('Test event');
        $expected->setDescription('Some description');
        $expected->setLocation('Warsaw');
        $expected->setStartDate(new \DateTime('2025-01-01 10:00:00'));
        $expected->setEndDate(new \DateTime('2025-01-02 10:00:00'));

        $form->submit($formData);

        self::assertTrue($form->isSynchronized());
        self::assertEquals($expected->getTitle(), $model->getTitle());
        self::assertEquals($expected->getDescription(), $model->getDescription());
        self::assertEquals($expected->getLocation(), $model->getLocation());
        self::assertEquals($expected->getStartDate()->format('Y-m-d H:i'), $model->getStartDate()->format('Y-m-d H:i'));
    }

    public function testFormHasFields(): void
    {
        $form = $this->factory->create(EventType::class);
        $fields = [
            'title',
            'description',
            'location',
            'startDate',
            'endDate',
            'category',
            'tags',
        ];

        foreach ($fields as $field)
        {
            self::assertTrue($form->has($field));
        }
    }

    public function testTagsFieldIsConfiguredCorrectly(): void
    {
        $form = $this->factory->create(EventType::class);
        $config = $form->get('tags')->getConfig();

        self::assertSame(Tag::class, $config->getOption('class'));
        self::assertFalse($config->getOption('required'));
        self::assertTrue($config->getOption('multiple'));
        self::assertFalse($config->getOption('expanded'));
    }

    public function testCategoryFieldIsConfiguredCorrectly(): void
    {
        $form = $this->factory->create(EventType::class);
        $config = $form->get('category')->getConfig();

        self::assertSame(Category::class, $config->getOption('class'));
    }

}
