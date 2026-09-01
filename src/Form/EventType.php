<?php

/**
 * Event type.
 */

namespace App\Form;

use App\Entity\Event;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use App\Entity\Category;
use App\Entity\Tag;

/**
 * Class EventType.
 */
class EventType extends AbstractType
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
            ->add('title', null, [
                'label' => 'th.title',
                'required' => true,
                'attr' => ['max_length' => 64],
            ])
            ->add('description', null, [
                'label' => 'th.description',
                'required' => false,
                'attr' => ['max_length' => 256],
            ])
            ->add('location', null, [
                'label' => 'th.location',
                'attr' => ['max_length' => 64],
            ])
            ->add('startDate', DateTimeType::class, [
                'label' => 'th.start_date',
                'widget' => 'single_text',
            ])
            ->add('endDate', DateTimeType::class, [
                'label' => 'th.end_date',
                'widget' => 'single_text',
                'required' => false,
            ])
            ->add('category', EntityType::class, [
                'label' => 'th.category',
                'class' => Category::class,
                'choice_label' => function (Category $category) {
                    return $category->getTitle();
                },
                'required' => true,
            ])
            ->add('tags', EntityType::class, [
                'label' => 'option.tags',
                'class' => Tag::class,
                'choice_label' => function (Tag $tag) {
                    return '#'.$tag->getTitle();
                },
                'multiple' => true,
                'expanded' => false,
                'required' => false,
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
            'data_class' => Event::class,
        ]);
    }

    /**
     * Returns the prefix of the template block name for this type.
     *
     * The block prefix defaults to the underscored short class name with
     * the "Type" suffix removed (e.g. "UserProfileType" => "user_profile").
     *
     * @return string The prefix of the template block name
     *
     * @psalm-return 'event'
     */
    public function getBlockPrefix(): string
    {
        return 'event';
    }
}
