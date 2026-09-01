<?php

/**
 * Tests for RegistrationFormType.
 */

namespace App\Tests\Form;

use App\Entity\User;
use App\Form\RegistrationFormType;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Form\FormFactoryInterface;

/**
 * Class RegistrationFormTypeTest.
 */
class RegistrationFormTypeTest extends KernelTestCase
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
        $model = new User();
        $form = $this->formFactory->create(RegistrationFormType::class, $model);
        $form->submit(['email' => 'test@example.com', 'agreeTerms' => true, 'plainPassword' => 'secret123']);
        self::assertTrue($form->isSynchronized());
        self::assertSame('test@example.com', $model->getEmail());
    }

    /**
     * Test form fields.
     */
    public function testFormHasAllFields(): void
    {
        $form = $this->formFactory->create(RegistrationFormType::class);
        self::assertTrue($form->has('email'));
        self::assertTrue($form->has('agreeTerms'));
        self::assertTrue($form->has('plainPassword'));
    }

    /**
     * Test agree terms field mapping.
     */
    public function testAgreeTermsIsNotMapped(): void
    {
        $form = $this->formFactory->create(RegistrationFormType::class);
        self::assertFalse($form->get('agreeTerms')->getConfig()->getMapped());
    }

    /**
     * Test plain password field mapping.
     */
    public function testPlainPasswordIsNotMapped(): void
    {
        $form = $this->formFactory->create(RegistrationFormType::class);
        self::assertFalse($form->get('plainPassword')->getConfig()->getMapped());
    }
}
