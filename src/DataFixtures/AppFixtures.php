<?php

namespace App\DataFixtures;

use App\Entity\Role;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class AppFixtures extends Fixture
{
    private $passwordEncoder;

    public function __construct(UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->passwordEncoder = $passwordEncoder;
    }

    public function load(ObjectManager $manager)
    {
        $roles = [];

        $reflectionRoleClass = new \ReflectionClass(Role::class);
        foreach ($reflectionRoleClass->getConstants() as $constant => $value) {
            if (0 === strpos($constant, 'ROLE_')) {
                $roleFromDatabase = $manager->getRepository(Role::class)->find($value);
                if ($roleFromDatabase instanceof Role) {
                    continue;
                }

                $description = ucwords(strtolower(str_replace(['ROLE_', '_'], ['', ' '], $constant)));

                $roles[$value] = (new Role())
                    ->setRole($constant)
                    ->setDescription($description)
                ;
            }
        }

        ksort($roles);

        if (count($roles) > 0) {
            foreach ($roles as $role) {
                $manager->persist($role);
            }
            $manager->flush();
        }
        $roles = $manager->getRepository(Role::class)->findAll();

        foreach ($roles as $role) {
            $email = 'dev+' . strtolower($role->getRole()) . '@topnode.com.br';
            $password = '123123123';
            $user = $manager->getRepository(User::class)->loadUserByUsername($email);

            if (!$user) {
                $user = (new User())
                    ->setEmail($email)
                    ->setName('DEV ' . $role->getDescription())
                    ->setRole($role)
                ;
            }

            $user->setPassword(
                $this->passwordEncoder->encodePassword($user, $password)
            );

            $manager->persist($user);

            echo "\n";
            echo 'Usuário: ' . $email . "\n";
            echo 'Senha: ' . $password . "\n";
        }

        echo "\n";

        $manager->flush();
    }
}
