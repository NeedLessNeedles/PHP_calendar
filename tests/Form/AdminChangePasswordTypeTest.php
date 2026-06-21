<?php

/**
 * AdminChangePasswordType tests.
 */

namespace App\Tests\Form;

use App\Form\AdminChangePasswordType;
use Symfony\Component\Form\Test\TypeTestCase;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * Class AdminChangePasswordTypeTest.
 */
class AdminChangePasswordTypeTest extends TypeTestCase
{
    /**
     * Data validation test.
     */
    public function testSubmitValidData(): void
    {
        $form = $this->factory->create(AdminChangePasswordType::class);
        $formData = [
            'newPassword' => [
                'first' => 'secret123',
                'second' => 'secret123',
            ],
        ];
        $form->submit($formData);

        $this->assertTrue($form->isSynchronized());
    }
    public function testFormHasExpectedField(): void
    {
        $form = $this->factory->create(AdminChangePasswordType::class);

        $this->assertTrue($form->has('newPassword'));
    }

    public function testFormConfiguration(): void
    {
        $form = $this->factory->create(AdminChangePasswordType::class);
        $config = $form->get('newPassword')->getConfig();

        $this->assertSame('Symfony\Component\Form\Extension\Core\Type\RepeatedType', get_class($config->getType()->getInnerType()));
    }

    public function testRepeatedFieldOptions(): void
    {
        $form = $this->factory->create(AdminChangePasswordType::class);
        $config = $form->get('newPassword')->getConfig();

        $this->assertSame('Symfony\Component\Form\Extension\Core\Type\PasswordType', $config->getOption('type'));
        $constraints = $config->getOption('constraints');
        $this->assertNotEmpty($constraints);

        $this->assertInstanceOf(NotBlank::class, $constraints[0]);
    }

    public function testFormHasNoDataClass(): void
    {
        $form = $this->factory->create(AdminChangePasswordType::class);

        $this->assertNull($form->getConfig()->getDataClass());
    }

}
