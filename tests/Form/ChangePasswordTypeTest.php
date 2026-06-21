<?php

/**
 * ChangePasswordType tests.
 */

namespace App\Tests\Form;

use App\Form\ChangePasswordType;
use Symfony\Component\Form\Test\TypeTestCase;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * Class ChangePasswordTypeTest.
 */
class ChangePasswordTypeTest extends TypeTestCase
{
    /**
     * Data validation test.
     */
    public function testFormSubmitValidData(): void
    {
        $form = $this->factory->create(ChangePasswordType::class);
        $formData = [
            'currentPassword' => 'oldpass123',
            'newPassword' => [
                'first' => 'newpass123',
                'second' => 'newpass123',
            ],
        ];
        $form->submit($formData);

        $this->assertTrue($form->isSynchronized());
    }

    public function testFormHasExpectedFields(): void
    {
        $form = $this->factory->create(ChangePasswordType::class);

        $this->assertTrue($form->has('currentPassword'));
        $this->assertTrue($form->has('newPassword'));
    }

    public function testCurrentPasswordFieldType(): void
    {
        $form = $this->factory->create(ChangePasswordType::class);
        $config = $form->get('currentPassword')->getConfig();

        $this->assertEquals('Symfony\Component\Form\Extension\Core\Type\PasswordType', get_class($config->getType()->getInnerType()));
    }

    public function testNewPasswordFieldConfiguration(): void
    {
        $form = $this->factory->create(ChangePasswordType::class);
        $config = $form->get('newPassword')->getConfig();

        $this->assertEquals('Symfony\Component\Form\Extension\Core\Type\RepeatedType', get_class($config->getType()->getInnerType()));
        $this->assertEquals('Symfony\Component\Form\Extension\Core\Type\PasswordType', $config->getOption('type'));

        $constraints = $config->getOption('constraints');

        $this->assertNotEmpty($constraints);
        $this->assertInstanceOf(NotBlank::class, $constraints[0]);
    }

    public function testFormHasNoDataClass(): void
    {
        $form = $this->factory->create(ChangePasswordType::class);

        $this->assertNull($form->getConfig()->getDataClass());
    }

}
