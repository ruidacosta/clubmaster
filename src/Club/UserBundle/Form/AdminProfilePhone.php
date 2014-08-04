<?php

namespace Club\UserBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class AdminProfilePhone extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $attr = array(
            'class' => 'form-control'
        );

        $label_attr = array(
            'class' => 'col-sm-2'
        );

        $builder
            ->add('phone_number', 'text', array(
                'required' => false,
                'attr' => $attr,
                'label_attr' => $label_attr
            ))
            ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Club\UserBundle\Entity\ProfilePhone'
        ));
    }

    public function getName()
    {
        return 'admin_profile_phone';
    }
}
