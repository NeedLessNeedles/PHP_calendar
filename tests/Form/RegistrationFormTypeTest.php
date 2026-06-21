<?php

/**
 * Tests for RegistrationFormType.
 */

namespace App\Tests\Form;

use App\Form\RegistrationFormType;
use Symfony\Component\Form\Test\TypeTestCase;
use App\Entity\User;

/**
 * Class RegistrationFormTypeTest.
 */
class RegistrationFormTypeTest extends TypeTestCase
{
    /**
     * Data validation test.
     */
    public function testSubmitValidData(): void
    {
        $formData = [
            'email' => 'test@example.com',
            'agreeTerms' => true,
            'plainPassword' => 'secret123',
        ];
        $model = new User();
        $form = $this->factory->create(RegistrationFormType::class, $model);
        $form->submit($formData);

        $this->assertTrue($form->isSynchronized());
        $this->assertEquals('test@example.com', $model->getEmail());
    }

    public function testFormHasAllFields(): void
    {
        $form = $this->factory->create(RegistrationFormType::class);

        $this->assertTrue($form->has('email'));
        $this->assertTrue($form->has('agreeTerms'));
        $this->assertTrue($form->has('plainPassword'));
    }

    public function testAgreeTermsIsNotMapped(): void
    {
        $form = $this->factory->create(RegistrationFormType::class);
        $form->submit([
            'email' => 'test@example.com',
            'agreeTerms' => true,
            'plainPassword' => 'secret123',
        ]);

        $this->assertTrue($form->get('agreeTerms')->getConfig()->getMapped() === false);
    }

    public function testPlainPasswordIsNotMapped(): void
    {
        $form = $this->factory->create(RegistrationFormType::class);
        $form->submit([
            'email' => 'test@example.com',
            'agreeTerms' => true,
            'plainPassword' => 'secret123',
        ]);

        $this->assertTrue($form->get('plainPassword')->getConfig()->getMapped() === false);
    }

}
