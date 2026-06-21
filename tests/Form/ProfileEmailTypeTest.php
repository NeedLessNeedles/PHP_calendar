<?php

/**
 * ProfileEmailType tests.
 */

namespace App\Tests\Form;

use App\Entity\User;
use App\Form\ProfileEmailType;
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
        $form = $this->factory->create(ProfileEmailType::class);
        $user = new User();
        $formData = ['email' => 'test@example.com'];
        $form->setData($user);
        $form->submit($formData);

        $this->assertTrue($form->isSynchronized());
        $this->assertEquals('test@example.com', $user->getEmail());
    }

    public function testFormHasEmailField(): void
    {
        $form = $this->factory->create(ProfileEmailType::class);

        $this->assertTrue($form->has('email'));
    }

    public function testFormDataClassIsUser(): void
    {
        $form = $this->factory->create(ProfileEmailType::class);

        $this->assertEquals(User::class, $form->getConfig()->getDataClass());
    }
}
