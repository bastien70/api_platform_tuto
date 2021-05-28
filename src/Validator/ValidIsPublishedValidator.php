<?php

namespace App\Validator;

use App\Entity\CheeseListing;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class ValidIsPublishedValidator extends ConstraintValidator
{
    public function __construct(private EntityManagerInterface $manager, private Security $security){}

    public function validate($value, Constraint $constraint)
    {
        /* @var $constraint \App\Validator\ValidIsPublished */

        if(!$value instanceof CheeseListing)
        {
            throw new \LogicException('Only CheeseListing object is supported');
        }

        $originalData = $this->manager->getUnitOfWork()->getOriginalEntityData($value);

        $previousIsPublished = $originalData['isPublished'] ?? false;

        if($previousIsPublished === $value->getIsPublished()) {
            //IsPublished didn't change!
            return;
        }

        if($value->getIsPublished()){
            //we are publishing

            //don't allow short description, unless you are an admin
            if(strlen($value->getDescription()) < 100 && !$this->security->isGranted('ROLE_ADMIN'))
            {
                $this->context->buildViolation('Cannot publish: Description is too short!')
                    ->atPath('description')
                    ->addViolation();
            }

            return;
        }

        //We are UNpublished
        if(!$this->security->isGranted('ROLE_ADMIN')) {
//            throw new AccessDeniedException('Only admin users can unpublish');

            $this->context->buildViolation('Only admin users can unpublish')
                ->addViolation();
        }
    }
}
