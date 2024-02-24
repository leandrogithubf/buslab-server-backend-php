<?php echo "<?php\n"; ?>

namespace <?php echo $namespace; ?>;

<?php if ($bounded_full_class_name) { ?>
use <?php echo $bounded_full_class_name; ?>;
<?php } ?>
use Symfony\Component\Form\AbstractType;
<?php foreach ($field_type_use_statements as $className) { ?>
use <?php echo $className; ?>;
<?php } ?>
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\OptionsResolver\OptionsResolver;
<?php foreach ($constraint_use_statements as $className) { ?>
use <?php echo $className; ?>;
<?php } ?>

class <?php echo $class_name; ?> extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->setMethod('GET')
            ->setAction($options['action'])
            ->add('search', TextType::class, [
                'label' => '<?php echo $translation_domain; ?>form.fields.search',
                'attr' => [
                    'placeholder' => '<?php echo $translation_domain; ?>form.fields.search',
                    'data-rule-minlength' => '1',
                    'data-rule-maxlength' => '50',
                    'maxlength' => '50',
                ],
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'action' => '#',
            'csrf_protection' => false,
            'attr' => [
                'name' => 'filter'
            ],
        ]);
    }
}
