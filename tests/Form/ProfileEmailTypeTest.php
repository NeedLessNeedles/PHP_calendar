<?php

/**
 * ProfileType tests.
 */

namespace App\Tests\Form;

use App\Entity\User;
use App\Form\ProfileType;
use Symfony\Component\Form\Test\TypeTestCase;

/**
 * Class ProfileEmailTypeTest.
 */
class ProfileEmailTypeTest extends TypeTestCase
{
    /**
     * Data validation test.
     */
    public function testSubmitValidData(): void
    {
        $form = $this->factory->create(ProfileType::class);
        $user = new User();
        $formData = ['email' => 'test@example.com'];
        $form->setData($user);
        $form->submit($formData);

        $this->assertTrue($form->isSynchronized());
        $this->assertEquals('test@example.com', $user->getEmail());
    }

    public function testFormHasEmailField(): void
    {
        $form = $this->factory->create(ProfileType::class);

        $this->assertTrue($form->has('email'));
    }

    public function testFormDataClassIsUser(): void
    {
        $form = $this->factory->create(ProfileType::class);

        $this->assertEquals(User::class, $form->getConfig()->getDataClass());
    }
}
