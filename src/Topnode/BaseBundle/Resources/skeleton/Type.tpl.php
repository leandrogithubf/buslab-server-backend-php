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
use Symfony\Component\OptionsResolver\OptionsResolver;
<?php foreach ($constraint_use_statements as $className) { ?>
use <?php echo $className; ?>;
<?php } ?>

<?php foreach ($field_use_entities as $className) { ?>
use <?php echo $className; ?>;
<?php } ?>

class <?php echo $class_name; ?> extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
<?php foreach ($form_fields as $form_field => $fieldOptions) { ?>
                ->add('<?php echo $form_field; ?>', <?php echo $fieldOptions['type']; ?>::class, [
                    'label' => '<?php echo $translation_domain; ?>form.fields.<?php echo $fieldOptions['snakeCaseName']; ?>',
<?php if ('DateTimeType' == $fieldOptions['type']) { ?>
                    'widget' => 'single_text',
                    'format' => 'dd/MM/yyyy hh:mm:ss',
<?php } ?>
<?php if ('DateType' == $fieldOptions['type']) { ?>
                'widget' => 'single_text',
                'format' => 'dd/MM/yyyy',
<?php } ?>
<?php if ('TimeType' == $fieldOptions['type']) { ?>
                    'widget' => 'single_text',
                    'with_seconds' => true,
<?php } ?>
<?php if ('EntityType' == $fieldOptions['type']) { ?>
                    'class' => <?php echo $fieldOptions['targetEntityName']; ?>::class,
                    'choice_label' => '<?php echo $fieldOptions['choiceLabel']; ?>',
<?php if ('manyToMany' == $fieldOptions['relationType']) { ?>
                'multiple' => true,
<?php } ?>
<?php } ?>
<?php if ('ChoiceType' == $fieldOptions['type']) { ?>
<?php if (array_key_exists('boolean', $fieldOptions) && $fieldOptions['boolean']) { ?>
                    'choices' => [
                        '<?php echo $translation_domain; ?>form.fields.<?php echo $fieldOptions['snakeCaseName'] . '_choices.true'; ?>' => true,
                        '<?php echo $translation_domain; ?>form.fields.<?php echo $fieldOptions['snakeCaseName'] . '_choices.false'; ?>' => false,
                    ],
<?php } ?>
<?php } ?>
                    'attr' => [
<?php if ('DateTimeType' == $fieldOptions['type']) { ?>
                        'placeholder' => '__/__/____ __:__:__',
                        'class' => 'date-time-full single-daterange',
<?php } ?>
<?php if ('DateType' == $fieldOptions['type']) { ?>
                    'placeholder' => '__/__/____',
                    'class' => 'date single-daterange',
<?php } ?>
<?php if ('TimeType' == $fieldOptions['type']) { ?>
                        'placeholder' => '__:__:__',
                        'class' => 'time-full single-daterange',
<?php } ?>
<?php if (array_key_exists('maxlength', $fieldOptions) && $fieldOptions['maxlength']) { ?>
                    'maxlength' => <?php echo $fieldOptions['maxlength']; ?>,
                    'data-rule-maxlength' => <?php echo $fieldOptions['maxlength']; ?>,
<?php } ?>
<?php if (array_key_exists('minlength', $fieldOptions) && $fieldOptions['minlength']) { ?>
                    'minlength' => <?php echo $fieldOptions['minlength']; ?>,
                    'data-rule-minlength' => <?php echo $fieldOptions['minlength']; ?>,
<?php } ?>
<?php if (array_key_exists('required', $fieldOptions) && $fieldOptions['required']) { ?>
                    'required' => <?php echo $fieldOptions['required']; ?>,
<?php } ?>
                ],
            ])
<?php } ?>
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
<?php if ($bounded_full_class_name) { ?>
            'data_class' => <?php echo $bounded_class_name; ?>::class,
<?php } else { ?>
            // Configure your form options here
<?php } ?>
        ]);
    }
}
