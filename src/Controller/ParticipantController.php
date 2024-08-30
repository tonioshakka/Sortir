<?php

namespace App\Controller;

use App\Entity\Participant;
use App\Form\ParticipantType;
use App\Repository\ParticipantRepository;
use App\Service\EnvoiMail;
use App\Service\GenerateurDeMotDePasse;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/participant')]
class ParticipantController extends AbstractController
{
    #[Route('/', name: 'app_participant_index', methods: ['GET'])]
    #[IsGranted("ROLE_ADMIN")]
    public function index(ParticipantRepository $participantRepository): Response
    {
        return $this->render('participant/index.html.twig', [
            'participants' => $participantRepository->findAll(),
        ]);
    }

    #[Route('/profil', name: 'app_participant_profil')]
    public function profil(Security $security): Response
    {
        $participant = $security->getUser();

        return $this->render('profil.html.twig', [
            'participant' => $participant,
        ]);
    }

    #[Route('/new', name: 'app_participant_new', methods: ['GET', 'POST'])]
    #[IsGranted("ROLE_ADMIN")]
    public function new(Request $request,UserPasswordHasherInterface $passwordHasher,EnvoiMail $envoiMail, GenerateurDeMotDePasse $generateurDeMotDePasse  ,EntityManagerInterface $entityManager): Response
    {
        $participant = new Participant();
        $form = $this->createForm(ParticipantType::class, $participant, [
            'password_field' => false,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $leMotDePasse = $generateurDeMotDePasse->genererUnMotDePasse(8);
            $message = 'Penser a changer votre mot passe l\'or de votre premier connection, votre mot de passe par default est : ';
            $hashedPassword = $passwordHasher->hashPassword($participant, $leMotDePasse);
            $participant->setPassword($hashedPassword);
            $entityManager->persist($participant);
            $entityManager->flush();

            $envoiMail->EnvoiMailCreationCompte($participant->getEmail(),$leMotDePasse,$message);

            return $this->redirectToRoute('app_participant_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('participant/new.html.twig', [
            'participant' => $participant,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_participant_show', methods: ['GET'])]
    #[IsGranted("ROLE_USER")]
    public function show(Participant $participant): Response
    {
        return $this->render('participant/show.html.twig', [
            'participant' => $participant,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_participant_edit', methods: ['GET', 'POST'])]
    #[IsGranted("ROLE_USER")]
    public function edit(Request $request, Participant $participant,UserPasswordHasherInterface $passwordHasher ,EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ParticipantType::class, $participant, [
            'email_field' => false,
            ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $hashedPassword = $passwordHasher->hashPassword($participant, $form->get('password')->getData());
            $participant->setPassword($hashedPassword);
            $entityManager->flush();

            return $this->redirectToRoute('app_participant_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('participant/edit.html.twig', [
            'participant' => $participant,
            'form' => $form,
        ]);
    }

    #[Route('/delete/{id}', name: 'app_participant_delete', methods: ['POST'])]
    #[IsGranted("ROLE_ADMIN")]
    public function delete(Request $request, Participant $participant, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$participant->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($participant);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_participant_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/inactif/{id}', name: 'app_participant_inactif', methods: ['POST'])]
    #[IsGranted("ROLE_USER")]
    public function inactif(Request $request, Participant $participant, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('inactif'.$participant->getId(), $request->getPayload()->getString('_token'))) {
            $participant->setActif(false);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_participant_index', [], Response::HTTP_SEE_OTHER);
    }



}
