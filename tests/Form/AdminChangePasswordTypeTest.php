<?php

/**
 * AdminChangePasswordType tests.
 */

namespace App\Tests\Form;

use App\Form\AdminChangePasswordType;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormFactoryInterface;

/**
 * Class AdminChangePasswordTypeTest.
 */
class AdminChangePasswordTypeTest extends KernelTestCase
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
        $form = $this->formFactory->create(AdminChangePasswordType::class);

        $form->submit([
            'newPassword' => 'secret123',
        ]);

        self::assertTrue($form->isSynchronized());
        self::assertSame('secret123', $form->get('newPassword')->getData());
    }

    /**
     * Test form fields.
     */
    public function testFormHasExpectedField(): void
    {
        $form = $this->formFactory->create(AdminChangePasswordType::class);

        self::assertTrue($form->has('newPassword'));
    }

    /**
     * Test new password field configuration.
     */
    public function testNewPasswordFieldConfiguration(): void
    {
        $form = $this->formFactory->create(AdminChangePasswordType::class);
        $config = $form->get('newPassword')->getConfig();

        self::assertSame(
            PasswordType::class,
            $config->getType()->getInnerType()::class
        );
        self::assertFalse($config->getOption('mapped'));
    }

    /**
     * Test for no data class.
     */
    public function testFormHasNoDataClass(): void
    {
        $form = $this->formFactory->create(AdminChangePasswordType::class);

        self::assertNull($form->getConfig()->getDataClass());
    }
}
