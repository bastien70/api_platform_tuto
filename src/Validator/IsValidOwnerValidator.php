<?php

namespace App\Validator;

use App\Entity\User;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class IsValidOwnerValidator extends ConstraintValidator
{
    public function __construct(private Security $security) {}

    public function validate($value, Constraint $constraint)
    {
        /* @var $constraint \App\Validator\IsValidOwner */

        if (null === $value || '' === $value) {
            return;
        }

        if(!$value instanceof User)
        {
            throw new \InvalidArgumentException('@IsValidOwner constraint must be put on a property containing a User object');
        }

        $user = $this->security->getUser();

        if(!$user)
        {
            $this->context->buildViolation($constraint->anonymousMessage)
                ->addViolation();

            return;
        }

        if($this->security->isGranted('ROLE_ADMIN'))
        {
            return;
        }

        if($value->getId() !== $user->getId())
        {
            $this->context->buildViolation($constraint->message)
                ->addViolation();
        }

    }
}
