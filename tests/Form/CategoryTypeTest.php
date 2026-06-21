<?php

/**
 * CategoryType tests.
 */

namespace App\Tests\Form;

use App\Entity\Category;
use App\Form\CategoryType;
use Symfony\Component\Form\Test\TypeTestCase;

/**
 * Class CategoryTypeTest.
 */
class CategoryTypeTest extends TypeTestCase
{
    /**
     * Data validation test.
     */
    public function testSubmitValidData(): void
    {
        $formData = ['title' => 'Music'];
        $model = new Category();
        $form = $this->factory->create(CategoryType::class, $model);
        $expected = new Category();
        $expected->setTitle('Music');
        $form->submit($formData);

        $this->assertTrue($form->isSynchronized());
        $this->assertEquals($expected->getTitle(), $model->getTitle());

        $view = $form->createView();
        $children = $view->children;

        $this->assertArrayHasKey('title', $children);
    }
}
