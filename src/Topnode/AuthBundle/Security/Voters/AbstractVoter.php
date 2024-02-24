<?php

namespace App\Topnode\AuthBundle\Security\Voters;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

abstract class AbstractVoter extends Voter
{
    protected $targetEntity = '';

    protected function supports($attribute, $subject)
    {
        if (!$subject instanceof $this->targetEntity) {
            return false;
        }

        $actions = [];
        foreach (get_class_methods($this) as $method) {
            if (0 === strpos($method, 'can')) {
                $actions[] = lcfirst(substr($method, 3));
            }
        }

        if (!in_array($attribute, $actions)) {
            return false;
        }

        return true;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        $user = $token->getUser();

        if (!$user instanceof UserInterface) {
            return false;
        }

        $method = 'can' . ucfirst($attribute);
        if (method_exists($this, $method)) {
            return $this->$method($subject, $user);
        }

        throw new \LogicException('Método ' . $method . ' não implementado');
    }
}
