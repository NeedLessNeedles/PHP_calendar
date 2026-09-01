<?php

/**
 * ChangeEmailType tests.
 */

namespace App\Tests\Form;

use App\Form\ChangeEmailType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Test\TypeTestCase;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;

/**
 * Class ChangeEmailTypeTest.
 */
#[AllowMockObjectsWithoutExpectations]
class ChangeEmailTypeTest extends TypeTestCase
{
    /**
     * Test valid form submission.
     */
    public function testFormSubmitValidData(): void
    {
        $form = $this->factory->create(ChangeEmailType::class);
        $form->submit(['email' => 'test@example.com']);
        $this->assertTrue($form->isSynchronized());
    }

    /**
     * Test form fields.
     */
    public function testFormHasExpectedFields(): void
    {
        $form = $this->factory->create(ChangeEmailType::class);
        $this->assertTrue($form->has('email'));
    }

    /**
     * Test email field type.
     */
    public function testEmailFieldType(): void
    {
        $form = $this->factory->create(ChangeEmailType::class);
        $config = $form->get('email')->getConfig();
        $this->assertEquals(EmailType::class, get_class($config->getType()->getInnerType()));
    }

    /**
     * Test email field mapping.
     */
    public function testEmailFieldIsNotMapped(): void
    {
        $form = $this->factory->create(ChangeEmailType::class);
        $config = $form->get('email')->getConfig();
        $this->assertFalse($config->getOption('mapped'));
    }

    /**
     * Test form data class.
     */
    public function testFormHasNoDataClass(): void
    {
        $form = $this->factory->create(ChangeEmailType::class);
        $this->assertNull($form->getConfig()->getDataClass());
    }
}
