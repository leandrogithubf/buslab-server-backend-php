<?php echo "<?php\n"; ?>

namespace <?php echo $namespace; ?>;

<?php if ($api_resource) { ?>use ApiPlatform\Core\Annotation\ApiResource;
<?php } ?>
use Doctrine\ORM\Mapping as ORM;

use App\Topnode\BaseBundle\Entity as BaseEntity;

/**
<?php if ($api_resource) { ?> * @ApiResource()
<?php } ?>
 * @ORM\Entity(repositoryClass="<?php echo $repository_full_class_name; ?>")
 */
class <?php echo $class_name; ?> extends BaseEntity\AbstractBaseEntity
{
    use BaseEntity\IdTrait<?php if ($crud_fields) { ?>, BaseEntity\IsActiveTrait, BaseEntity\TimestampsTrait<?php } ?><?php if ($identifier) { ?>, BaseEntity\IdentifierTrait<?php } ?>;
}
