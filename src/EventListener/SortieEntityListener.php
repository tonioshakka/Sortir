<?php

namespace App\EventListener;

use App\Entity\Participant;
use App\Entity\Sortie;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Symfony\Bundle\SecurityBundle\Security;

class SortieEntityListener
{


    public function __construct(Security $security) {
        $this->security = $security;
    }

    public function prePersist(Sortie $sortie, LifecycleEventArgs $event):void
    {
        $user = $this->security->getUser();
        if ($user instanceof Participant)
        $sortie->setOrganisateur($user);
    }

}