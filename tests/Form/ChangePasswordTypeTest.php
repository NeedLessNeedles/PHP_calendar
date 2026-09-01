<?php

/**
 * ChangePasswordType tests.
 */

namespace App\Tests\Form;

use App\Form\ChangePasswordType;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormFactoryInterface;

/**
 * Class ChangePasswordTypeTest.
 */
class ChangePasswordTypeTest extends KernelTestCase
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
    public function testFormSubmitValidData(): void
    {
        $form = $this->formFactory->create(ChangePasswordType::class);
        $form->submit(['currentPassword' => 'oldpass123', 'newPassword' => 'newpass123']);
        self::assertTrue($form->isSynchronized());
        self::assertSame('oldpass123', $form->get('currentPassword')->getData());
        self::assertSame('newpass123', $form->get('newPassword')->getData());
    }

    /**
     * Test form fields.
     */
    public function testFormHasExpectedFields(): void
    {
        $form = $this->formFactory->create(ChangePasswordType::class);
        self::assertTrue($form->has('currentPassword'));
        self::assertTrue($form->has('newPassword'));
    }

    /**
     * Test current password field type.
     */
    public function testCurrentPasswordFieldType(): void
    {
        $form = $this->formFactory->create(ChangePasswordType::class);
        $config = $form->get('currentPassword')->getConfig();
        self::assertSame(PasswordType::class, $config->getType()->getInnerType()::class);
        self::assertFalse($config->getOption('mapped'));
    }

    /**
     * Test new password field configuration.
     */
    public function testNewPasswordFieldConfiguration(): void
    {
        $form = $this->formFactory->create(ChangePasswordType::class);
        $config = $form->get('newPassword')->getConfig();
        self::assertSame(PasswordType::class, $config->getType()->getInnerType()::class);
        self::assertFalse($config->getOption('mapped'));
    }

    /**
     * Test for no data class.
     */
    public function testFormHasNoDataClass(): void
    {
        $form = $this->formFactory->create(ChangePasswordType::class);
        self::assertNull($form->getConfig()->getDataClass());
    }
}
