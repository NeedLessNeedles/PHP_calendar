<?php

/**
 * ProfileType tests.
 */

namespace App\Tests\Form;

use App\Entity\User;
use App\Form\ProfileType;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Form\FormFactoryInterface;

/**
 * Class ProfileTypeTest.
 */
class ProfileTypeTest extends KernelTestCase
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
        $user = new User();
        $form = $this->formFactory->create(ProfileType::class, $user);
        $form->submit(['email' => 'test@example.com']);
        self::assertTrue($form->isSynchronized());
        self::assertSame('test@example.com', $user->getEmail());
    }

    /**
     * Test form email field.
     */
    public function testFormHasEmailField(): void
    {
        $form = $this->formFactory->create(ProfileType::class);
        self::assertTrue($form->has('email'));
    }

    /**
     * Test form data class.
     */
    public function testFormDataClassIsUser(): void
    {
        $form = $this->formFactory->create(ProfileType::class);
        self::assertSame(User::class, $form->getConfig()->getDataClass());
    }
}
