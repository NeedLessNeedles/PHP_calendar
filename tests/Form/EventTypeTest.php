<?php

/**
 * EventType tests.
 */

namespace App\Tests\Form;

use App\Entity\Category;
use App\Entity\Event;
use App\Entity\Tag;
use App\Form\EventType;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\PreloadedExtension;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Form\FormFactoryInterface;

/**
 * Class EventTypeTest.
 */
class EventTypeTest extends KernelTestCase
{
    private FormFactoryInterface $formFactory;

    /**
     * Test setup.
     */
    protected function setUp(): void
    {
        self::bootKernel();
        $this->formFactory = self::getContainer()->get(FormFactoryInterface::class);
    }

    /**
     * Data validation test.
     */
    public function testSubmitValidData(): void
    {
        $formData = ['title' => 'Test event', 'description' => 'Some description', 'location' => 'Warsaw', 'startDate' => '2025-01-01 10:00:00', 'endDate' => '2025-01-02 10:00:00'];
        $model = new Event();
        $form = $this->formFactory->create(EventType::class, $model);
        $form->submit($formData);

        self::assertTrue($form->isSynchronized());
        self::assertSame('Test event', $model->getTitle());
        self::assertSame('Some description', $model->getDescription());
        self::assertSame('Warsaw', $model->getLocation());
        self::assertSame('2025-01-01 10:00', $model->getStartDate()->format('Y-m-d H:i'));
        self::assertSame('2025-01-02 10:00', $model->getEndDate()->format('Y-m-d H:i'));
    }

    /**
     * Test form fields.
     */
    public function testFormHasFields(): void
    {
        $form = $this->formFactory->create(EventType::class);
        $fields = [
            'title',
            'description',
            'location',
            'startDate',
            'endDate',
            'category',
            'tags',
        ];

        foreach ($fields as $field) {
            self::assertTrue($form->has($field));
        }
    }

    /**
     * Test tags field configuration.
     */
    public function testTagsFieldIsConfiguredCorrectly(): void
    {
        $form = $this->formFactory->create(EventType::class);
        $config = $form->get('tags')->getConfig();

        self::assertSame(Tag::class, $config->getOption('class'));
        self::assertFalse($config->getOption('required'));
        self::assertTrue($config->getOption('multiple'));
        self::assertFalse($config->getOption('expanded'));
    }

    /**
     * Test category field configuration.
     */
    public function testCategoryFieldIsConfiguredCorrectly(): void
    {
        $form = $this->formFactory->create(EventType::class);
        $config = $form->get('category')->getConfig();

        self::assertSame(Category::class, $config->getOption('class'));
        self::assertTrue($config->getOption('required'));
    }

    /**
     * Test form data class.
     */
    public function testFormHasEventDataClass(): void
    {
        $form = $this->formFactory->create(EventType::class);
        self::assertSame(Event::class, $form->getConfig()->getDataClass());
    }

    /**
     * Test form default options.
     */
    public function testFormDefaultOptions(): void
    {
        $form = $this->formFactory->create(EventType::class);
        self::assertSame(Event::class, $form->getConfig()->getDataClass());
    }

    /**
     * Test block prefix.
     */
    public function testBlockPrefix(): void
    {
        $type = new EventType();
        self::assertSame('event', $type->getBlockPrefix());
    }

    /**
     * Test category choice label.
     */
    public function testCategoryChoiceLabel(): void
    {
        $form = $this->formFactory->create(EventType::class);
        $config = $form->get('category')->getConfig();

        $choiceLabel = $config->getOption('choice_label');
        self::assertIsCallable($choiceLabel);

        $category = new Category();
        $category->setTitle('Music');

        self::assertSame('Music', $choiceLabel($category));
    }

    /**
     * Test tag choice label.
     */
    public function testTagChoiceLabel(): void
    {
        $form = $this->formFactory->create(EventType::class);
        $config = $form->get('tags')->getConfig();

        $choiceLabel = $config->getOption('choice_label');

        self::assertIsCallable($choiceLabel);

        $tag = new Tag();
        $tag->setTitle('Symfony');

        self::assertSame('#Symfony', $choiceLabel($tag));
    }

    /**
     * Register extensions required by EntityType.
     *
     * @return array<int, PreloadedExtension>
     */
    protected function getExtensions(): array
    {
        $registry = $this->createStub(ManagerRegistry::class);

        return [new PreloadedExtension([EntityType::class => new EntityType($registry)], [])];
    }
}
