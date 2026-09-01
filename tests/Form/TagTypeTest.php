<?php

/**
 * TagType tests.
 */

namespace App\Tests\Form;

use App\Entity\Tag;
use App\Form\TagType;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class TagTypeTest.
 */
class TagTypeTest extends KernelTestCase
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
        $model = new Tag();
        $form = $this->formFactory->create(TagType::class, $model);
        $form->submit(['title' => 'Music']);
        self::assertTrue($form->isSynchronized());
        self::assertSame('Music', $model->getTitle());
    }

    /**
     * Test form fields.
     */
    public function testFormHasTitleField(): void
    {
        $form = $this->formFactory->create(TagType::class);
        self::assertTrue($form->has('title'));
    }

    /**
     * Test title field configuration.
     */
    public function testTitleFieldConfiguration(): void
    {
        $form = $this->formFactory->create(TagType::class);
        $config = $form->get('title')->getConfig();
        self::assertSame(TextType::class, $config->getType()->getInnerType()::class);
        self::assertTrue($config->getRequired());
        self::assertSame('label.title', $config->getOption('label'));
        self::assertSame(['max_length' => 64], $config->getOption('attr'));
    }

    /**
     * Test form data class.
     */
    public function testFormHasTagDataClass(): void
    {
        $form = $this->formFactory->create(TagType::class);
        self::assertSame(Tag::class, $form->getConfig()->getDataClass());
    }

    /**
     * Test configure options.
     */
    public function testConfigureOptions(): void
    {
        $type = new TagType();
        $resolver = new OptionsResolver();
        $type->configureOptions($resolver);
        $options = $resolver->resolve();
        self::assertSame(Tag::class, $options['data_class']);
    }

    /**
     * Test block prefix.
     */
    public function testBlockPrefix(): void
    {
        $type = new TagType();
        self::assertSame('tag', $type->getBlockPrefix());
    }
}
