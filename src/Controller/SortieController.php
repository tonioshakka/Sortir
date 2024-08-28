<?php

namespace App\Controller;

use App\Entity\Participant;
use App\Entity\Sortie;
use App\Form\InscriptionType;
use App\Form\SortieType;
use App\Repository\SortieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

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
    public function new(Request $request, EntityManagerInterface $entityManager, ValidatorInterface $validator): Response
    {
        $sortie = new Sortie();
        $form = $this->createForm(SortieType::class, $sortie);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if (!$form->getdata()->getLieu()) {
                $lieu = $form->get('lieuNew')->getData();
                $errors = $validator->validate($lieu);
                if (count($errors) > 0) {
                    foreach ($errors as $error) {
                        $form->addError(new FormError($error->getMessage()));
                    }
                    return $this->render('sortie/new.html.twig', [
                        'sortie' => $sortie,
                        'form' => $form,
                    ]);
                }
                $sortie->setLieu($lieu);
            }

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
    public function edit(Request $request, Sortie $sortie, EntityManagerInterface $entityManager, ValidatorInterface $validator): Response
    {
        $form = $this->createForm(SortieType::class, $sortie);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if (!$form->getdata()->getLieu()) {
                $lieu = $form->get('lieuNew')->getData();
                $errors = $validator->validate($lieu);
                if (count($errors) > 0) {
                    foreach ($errors as $error) {
                        $form->addError(new FormError($error->getMessage()));
                    }
                    return $this->render('sortie/edit.html.twig', [
                        'sortie' => $sortie,
                        'form' => $form,
                    ]);
                }
                $sortie->setLieu($lieu);
            }

            $entityManager->persist($sortie);
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
        if ($this->isCsrfTokenValid('delete' . $sortie->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($sortie);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_sortie_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}/inscription', name: 'app_sortie_inscription', methods: ['GET', 'POST'])]
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
        if ($sortie->getDateLimiteInscription() < new \DateTime()) {
            $this->addFlash('error', 'La date limite d\'inscription est dépassée, dommage.');
            return $this->redirectToRoute('app_sortie_index', ['id' => $sortie->getId()]);
        }

        // Vérifier si il y a encore de la place
        if (count($sortie->getParticipant()) >= $sortie->getNbInscriptionsMax()) {
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

    #[Route('/{id}/desistement', name: 'app_sortie_desistement', methods: ['GET', 'POST'])]
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
}
