<?php

/**
 * RegistrationForm type.
 */

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * Class RegistrationFormType.
 */
class RegistrationFormType extends AbstractType
{
    /**
     * Builds the form.
     *
     * @param FormBuilderInterface $builder Builder
     * @param array<string, mixed> $options Options
     *
     * @see FormTypeExtensionInterface::buildForm()
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, [
                'label' => 'label.email',
            ])
            ->add('agreeTerms', CheckboxType::class, [
                'label' => 'label.agree_terms',
                'mapped' => false,
                'constraints' => [
                    new IsTrue(
                        message: 'message.agree_terms',
                    ),
                ],
                'attr' => [
                    'style' => 'transform: scale(1.3); cursor: pointer;',
                ],
                'label_attr' => [
                    'style' => 'cursor: pointer;',
                ],
            ])
            ->add('plainPassword', PasswordType::class, [
                'label' => 'label.password',
                'mapped' => false,
                'attr' => ['autocomplete' => 'new-password'],
                'constraints' => [
                    new NotBlank(
                        message: 'message.enter_password',
                    ),
                    new Length(
                        min: 6,
                        max: 4096,
                        minMessage: 'message.but_at_least',
                    ),
                ],
            ])
        ;
    }

    /**
     * Configures the options for this type.
     *
     * @param OptionsResolver $resolver The resolver for the options
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
