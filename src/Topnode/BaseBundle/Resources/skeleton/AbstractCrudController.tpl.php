<?php echo "<?php\n"; ?>

namespace <?php echo $namespace; ?>;

use <?php echo $entity_full_class_name; ?>;
use <?php echo $form_full_class_name; ?>;
<?php if (isset($repository_full_class_name)) { ?>
use <?php echo $repository_full_class_name; ?>;
<?php } ?>

use Symfony\Bundle\FrameworkBundle\Controller\<?php echo $parent_class_name; ?>;
use Symfony\Component\HttpFoundation\Request;
use App\Topnode\BaseBundle\Controller\AbstractCrudController;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("<?php echo $route_path; ?>", name="<?php echo $route_name; ?>_")
 */
class <?php echo $class_name; ?> extends AbstractCrudController
{
    protected $entityClass = '<?php echo $entity_full_class_name; ?>';
    protected $formClass = '<?php echo $form_full_class_name; ?>';
    protected $formFilterClass = '<?php echo $form_filter_full_class_name; ?>';
    protected $entityTranslationDomain = '<?php echo $entity_translation_domain; ?>';
    protected $defaultSearchFields = [<?php foreach ($entity_default_search_fields as $search_field) { ?> '<?php echo $search_field; ?>', <?php } ?>];
}
