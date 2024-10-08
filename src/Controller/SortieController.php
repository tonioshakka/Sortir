<?php

namespace App\Controller;

use App\Entity\Participant;
use App\Entity\Sortie;
use App\Form\AnnulationType;
use App\Form\SortieType;
use App\Form\TriSortieType;
use App\Repository\EtatRepository;
use App\Repository\SiteRepository;
use App\Repository\SortieRepository;
use App\Service\LieuService;
use App\Service\AnnulerSortie;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;



#[IsGranted("ROLE_USER")]
class SortieController extends AbstractController
{
    #[Route('/', name: 'app_sortie_index', methods: ['GET', 'POST'])]
    public function triSorties(Request $request, SortieRepository $sortieRepository, SiteRepository $siteRepository): Response
    {
        $user = $this->getUser();
        $userSite = $user?->getSite();
        $form = $this->createForm(TriSortieType::class, null, [
            'default_site' => $userSite,
        ]);
        $form->handleRequest($request);

        $sorties = [];

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            // Effectuer la recherche/tri des sorties basées sur les critères du formulaire
            $sorties = $sortieRepository->findByCriteria($data, $user);
        } else {
            // Si aucun tri n'est effectué, on récupère toutes les sorties
            $sorties = $sortieRepository->findNonArchived();
        }


        return $this->render('sortie/index.html.twig', [
            'sorties' => $sorties,
            'form' => $form->createView(),
        ]);
    }

    #[Route('sortie/new', name: 'app_sortie_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, LieuService $lieuService): Response
    {
        $sortie = new Sortie();
        $form = $this->createForm(SortieType::class, $sortie);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $entityManager->persist($sortie);
            $entityManager->flush();

            return $this->redirectToRoute('app_sortie_index', [], Response::HTTP_SEE_OTHER);

        }

        $map = $lieuService->getMap(47.239367, -1.555335);



        return $this->render('sortie/new.html.twig', [
            'sortie' => $sortie,
            'form' => $form,
            'map' => $map,
        ]);
    }

    #[Route('/sortie/{id}', name: 'app_sortie_show', methods: ['GET'])]
    public function show(Sortie $sortie): Response
    {
        // Récupération de l'utilisateur connecté
        $participant = $this->getUser();

        // Vérifie si l'utilisateur connecté est l'organisateur de la sortie
        $isOrganisateur = $sortie->getOrganisateur() === $participant;

        return $this->render('sortie/show.html.twig', [
            'sortie' => $sortie,
            'participant' => $participant,
            'isOrganisateur' => $isOrganisateur,
        ]);
    }


    #[Route('/sortie/edit/{id}', name: 'app_sortie_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Sortie $sortie, EntityManagerInterface $entityManager, lieuService $lieuService): Response
    {
        if($this->getUser() !== $sortie->getOrganisateur() && !in_array('ROLE_ADMIN', $this->getUser()->getRoles())){
            return $this->redirectToRoute('app_sortie_index', [], Response::HTTP_FORBIDDEN);
        }
        $form = $this->createForm(SortieType::class, $sortie);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $entityManager->persist($sortie);
            $entityManager->flush();

            return $this->redirectToRoute('app_sortie_index', [], Response::HTTP_SEE_OTHER);
        }

        $lat = $form->getData()->getLieu()->getLatitude();
        $lng =  $form->getData()->getLieu()->getLongitude();
        $map = $lieuService->getMap($lat, $lng);

        return $this->render('sortie/edit.html.twig', [
            'sortie' => $sortie,
            'form' => $form,
            'map' => $map,
        ]);
    }

    #[Route('/sortie/delete/{id}', name: 'app_sortie_delete', methods: ['GET'])]
    public function delete(Request $request, Sortie $sortie, EntityManagerInterface $entityManager, CsrfTokenManagerInterface $csrfTokenManager): Response
    {
        $token = $request->query->get('token');

        if ($this->isCsrfTokenValid('supprimerSortie' . $sortie->getId(), $token)) {
            $entityManager->remove($sortie);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_sortie_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/sortie/inscription/{id}', name: 'app_sortie_inscription', methods: ['GET', 'POST'])]
    public function inscription(Request $request, Sortie $sortie, EntityManagerInterface $entityManager, CsrfTokenManagerInterface $csrfTokenManager): Response
    {
        $token = $request->query->get('token');

        // Vérifier le token CSRF
        if (!$this->isCsrfTokenValid('inscription' . $sortie->getId(), $token)) {
            $this->addFlash('error', 'Token CSRF invalide.');
            return $this->redirectToRoute('app_sortie_index', ['id' => $sortie->getId()]);
        }

        //Vérifier le status de la sortie
        if ($sortie->getEtat()->getId() !== 2) {
            $this->addFlash('error', 'Les inscriptions ne sont pas ouvertes pour cette sortie.');
            return $this->redirectToRoute('app_sortie_index', ['id' => $sortie->getId()]);
        }

        // Vérifier si la date limite d'inscription est dépassée
        if ($sortie->getDateLimiteInscription() < new \DateTime() or $sortie->getDateHeureDebut() < new \DateTime()) {
            $this->addFlash('error', 'La date limite d\'inscription est dépassée, dommage.');
            return $this->redirectToRoute('app_sortie_index', ['id' => $sortie->getId()]);
        }

        // Vérifier si il y a encore de la place
        if ($sortie->getNbInscriptionsMax() && count($sortie->getParticipant()) >= $sortie->getNbInscriptionsMax()) {

            $this->addFlash('error', 'Le nombre maximal de participants est atteint, désolé.');
            return $this->redirectToRoute('app_sortie_index', ['id' => $sortie->getId()]);
        }

        // Ajouter le participant
        $participant = $this->getUser();
        if ($participant instanceof Participant) {
            $sortie->addParticipant($participant);
            $entityManager->persist($sortie);
            $entityManager->flush();

            $this->addFlash('success', 'Inscription réussie, passez un bon moment ;) .');
        } else {
            $this->addFlash('error', 'Erreur lors de l\'inscription, vous n\'avez pas été ajouté(e) à la sortie.');
        }

        return $this->redirectToRoute('app_sortie_index', ['id' => $sortie->getId()], Response::HTTP_SEE_OTHER);
    }

    #[Route('/sortie/desistement/{id}', name: 'app_sortie_desistement', methods: ['GET', 'POST'])]
    public function desistement(Request $request, Sortie $sortie, EntityManagerInterface $entityManager, CsrfTokenManagerInterface $csrfTokenManager): Response
    {
        $token = $request->query->get('token');

        // Vérifier le token CSRF
        if (!$this->isCsrfTokenValid('desistement' . $sortie->getId(), $token)) {
            $this->addFlash('error', 'Token CSRF invalide.');
            return $this->redirectToRoute('app_sortie_index', ['id' => $sortie->getId()]);
        }

        if ($sortie->getEtat()->getId() !== 2) {
            $this->addFlash('error', 'Les désinscriptions ne sont pas possibles pour cette sortie.');
            return $this->redirectToRoute('app_sortie_index', ['id' => $sortie->getId()]);
        }

        // Vérifier si la date limite d'inscription est dépassée
        if ($sortie->getDateLimiteInscription() < new \DateTime()) {
            $this->addFlash('error', 'Impossible de se désinscrire lorsque la date limite d\'inscription est dépassée.');
            return $this->redirectToRoute('app_sortie_index', ['id' => $sortie->getId()]);
        }

        // Retirer le participant
        $participant = $this->getUser();
        if ($participant instanceof Participant) {
            $sortie->removeParticipant($participant);
            $entityManager->persist($sortie);
            $entityManager->flush();

            $this->addFlash('success', 'Désinscription réussie !');
        } else {
            $this->addFlash('error', 'Erreur lors de la désinscription.');
        }

        return $this->redirectToRoute('app_sortie_index', ['id' => $sortie->getId()], Response::HTTP_SEE_OTHER);
    }





    /**
     * @throws TransportExceptionInterface
     */
    #[Route('/sortie/annuler/{id}', name: 'app_sortie_annuler', methods: ['GET']),]
    public function annulerSortie(Request $request,
                                  Sortie $sortie,
                                  EntityManagerInterface $entityManager,
                                  EtatRepository $etatRepository,
                                  MailerInterface $mailer,
                                  SortieRepository $sortieRepository,
                                    AnnulerSortie $annuler,

    ): Response
    {

        $form = $this->createForm(SortieType::class, $sortie);
        $form->handleRequest($request);

        //Récupérer l'organisateur
        $organisateur = $sortie->getOrganisateur()->getId();



        //Récupérer utilisateur en cours
        $UtilisateurEnCours = $this->getUser()->getId();


        //Récupérer la sortie qu'on veut annuler
        $sortieAnnuler = $sortie->getId();


        //On a besoin du token
        $token = $request->get('token');


        // Il faut un objet Etat pour le set dans la sortie
        $etat = $etatRepository->find(7);

        if($UtilisateurEnCours != $organisateur && $sortie->getEtat()->getId() != 2){
            return $this->redirectToRoute('app_sortie_index', [], Response::HTTP_SEE_OTHER);
        }

        if(!$sortieAnnuler || !$this->isCsrfTokenValid('annuler'.$sortie->getId(), $token)){


            return  $this->redirectToRoute('app_sortie_index',[],Response::HTTP_CONFLICT);
        }

        if($sortie->getDateHeureDebut() == new \DateTime('now')){
            return $this->redirectToRoute('app_sortie_index',[],Response::HTTP_I_AM_A_TEAPOT);
        }

        // Update l'état de la sortie, désinscrit les participants et on leur envoi un mail.
        $annuler->annuler($sortie);

//            $sortie->setEtat($etat);
//            foreach ($sortie->getParticipant() as $gens){
//
//                $sortie->removeParticipant($gens);
//
//
//                $email = (new TemplatedEmail())
//                    ->from('test@glandu.com')
//                    ->to($gens->getEmail())
//                    ->htmlTemplate('sortie/email/sortieAnnuler.html.twig')
//                    ->context([
//                        'sortie' => $sortie,
//                        'participant' => $gens
//
//                    ]);
//
//                $mailer->send($email);
//
//            $entityManager->persist($sortie);
//            $entityManager->flush();

//            return $this->redirectToRoute('app_sortie_index',[]);
//        }

        return $this->redirectToRoute('app_sortie_index', []);

    }


#[Route('/motif/{id}', name: 'app_sortie_form_annuler', methods: ['GET','POST'])]
    public function formAnnuler(Request $request, Sortie $sortie, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(AnnulationType::class, $sortie, [
            'method' => 'POST',
        ]);
        $form->handleRequest($request);
        $token = $request->get('token');
        if ($form->isSubmitted()) {
            $entityManager->persist($sortie);
            $entityManager->flush();

            $this->addFlash('success', 'votre sortie a été annulée');




            return $this->redirectToRoute('app_sortie_annuler', [
                'id' => $sortie->getId(),
                'token' => $request->get('token'),
            ]);
        }
//        $this->addFlash('error', 'Echec annulation ');
//
        return $this->render('sortie/annuler.html.twig', [
            'form'=> $form
        ]);
    }
}
