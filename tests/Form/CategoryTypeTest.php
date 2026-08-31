<?php

/**
 * CategoryType tests.
 */

namespace App\Tests\Form;

use App\Entity\Category;
use App\Form\CategoryType;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Form\FormFactoryInterface;

/**
 * Class CategoryTypeTest.
 */
class CategoryTypeTest extends KernelTestCase
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
        $formData = ['title' => 'Music'];
        $model = new Category();
        $form = $this->formFactory->create(CategoryType::class, $model);
        $expected = new Category();
        $expected->setTitle('Music');
        $form->submit($formData);
        self::assertTrue($form->isSynchronized());
        self::assertEquals($expected->getTitle(), $model->getTitle());
        $view = $form->createView();
        $children = $view->children;
        self::assertArrayHasKey('title', $children);
    }
}
