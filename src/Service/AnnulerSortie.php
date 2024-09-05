<?php

namespace App\Service;

use App\Repository\EtatRepository;
use Doctrine\ORM\EntityManagerInterface;
use http\Env\Response;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;

class AnnulerSortie
{
    private MailerInterface $mailer;
    private EntityManagerInterface $entityManager;
    private EtatRepository $etatRepository;

    public function __construct(MailerInterface $mailer, EntityManagerInterface $entityManager, EtatRepository $etatRepository)
    {
        $this->mailer = $mailer;
        $this->entityManager = $entityManager;
        $this->etatRepository = $etatRepository;

    }

    /**
     * @throws TransportExceptionInterface
     */
    public function annuler($sortie):void
    {
        $etat = $this->etatRepository->find(4);
        $sortie->setEtat($etat);
        foreach ($sortie->getParticipant() as $gens){


            $sortie->removeParticipant($gens);


            $email = (new TemplatedEmail())
                ->from('test@glandu.com')
                ->to($gens->getEmail())
                ->htmlTemplate('sortie/email/sortieAnnuler.html.twig')
                ->context([
                    'sortie' => $sortie,
                    'participant' => $gens

                ]);


            $this->mailer->send($email);
            $this->entityManager->persist($sortie);
            $this->entityManager->flush();

    }

}
}