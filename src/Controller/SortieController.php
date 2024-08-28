<?php

namespace App\Controller;

use App\Entity\Etat;
use App\Entity\Sortie;
use App\Form\SortieType;
use App\Repository\EtatRepository;
use App\Repository\SortieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\User\UserInterface;

#[Route('/sortie')]
class SortieController extends AbstractController
{
    #[Route('/', name: 'app_sortie_index', methods: ['GET'])]
    public function index(SortieRepository $sortieRepository): Response
    {
        return $this->render('sortie/index.html.twig', [
            'sorties' => $sortieRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_sortie_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $sortie = new Sortie();
        $form = $this->createForm(SortieType::class, $sortie);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($sortie);
            $entityManager->flush();

            return $this->redirectToRoute('app_sortie_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('sortie/new.html.twig', [
            'sortie' => $sortie,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_sortie_show', methods: ['GET'])]
    public function show(Sortie $sortie): Response
    {
        return $this->render('sortie/show.html.twig', [
            'sortie' => $sortie,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_sortie_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Sortie $sortie, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(SortieType::class, $sortie);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_sortie_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('sortie/edit.html.twig', [
            'sortie' => $sortie,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_sortie_delete', methods: ['POST'])]
    public function delete(Request $request, Sortie $sortie, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$sortie->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($sortie);
            $entityManager->flush();
    }

return $this->redirectToRoute('app_sortie_index', [], Response::HTTP_SEE_OTHER);
}

    /**
     * @throws TransportExceptionInterface
     */
    #[Route('/annuler/{id}', name: 'app_sortie_annuler', methods: ['GET']),]
public function annulerSortie(Request $request,
                              Sortie $sortie,
                              EntityManagerInterface $entityManager,
                              EtatRepository $etatRepository,
                              MailerInterface $mailer,
    SortieRepository $sortieRepository,

): Response
{
    //Récupérer l'organisateur
    $organisateur = $sortie->getOrganisateur()->getId();


    //Récupérer utilisateur en cours
    $UtilisateurEnCours = $this->getUser()->getId();


    //Récupérer la sortie qu'on veut annuler
    $sortieAnnuler = $sortie->getId();


    //On a besoin du token
    $token = $request->get('token');


    // Il faut un objet Etat pour le set dans la sortie
    $etat = $etatRepository->find(2);

    //Avoir le nombre de participant a un événement
    $countParticipant = $sortie->getParticipant()->count();


        if($UtilisateurEnCours != $organisateur && $sortie->getEtat()->getId() != 1){
            return $this->redirectToRoute('app_sortie_index', [], Response::HTTP_SEE_OTHER);
        }

        if(!$sortieAnnuler || !$this->isCsrfTokenValid('annuler'.$sortie->getId(), $token)){


            return  $this->redirectToRoute('app_sortie_index',[],Response::HTTP_CONFLICT);
        }

        if($sortie->getDateHeureDebut() == new \DateTime('now')){
           return $this->redirectToRoute('app_sortie_index',[],Response::HTTP_I_AM_A_TEAPOT);
        }

        // Update l'état de la sortie, désinscrit les participants et on leur envoi un mail.
        if($countParticipant >= 1){
            foreach ($sortie->getParticipant() as $gens){
                $sortie->setEtat($etat);
                $sortie->removeParticipant($gens);
                $entityManager->persist($sortie);
                $entityManager->flush();

                $email = (new TemplatedEmail())
                    ->from('test@glandu.com')
                    ->to($gens->getEmail())
                    ->htmlTemplate('sortie/email/sortieAnnuler.html.twig')
                    ->context([
                        'sortie' => $sortie,
                        'participant' => $gens

                    ]);

                $mailer->send($email);
            }

            return $this->redirectToRoute('app_sortie_index',[]);
        }

        return $this->redirectToRoute('app_sortie_index', []);

    }


}

