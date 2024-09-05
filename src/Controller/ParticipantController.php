<?php

namespace App\Controller;

use App\Entity\Participant;
use App\Entity\Sortie;
use App\Form\CSVUploadType;
use App\Form\ParticipantType;
use App\Repository\ParticipantRepository;
use App\Repository\SortieRepository;
use App\Service\AnnulerSortie;
use App\Service\importCSV;
use App\Service\EnvoiMail;
use App\Service\GenerateurDeMotDePasse;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
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


            $image = $participant->getImage();



            if ($image) {
                $participant->setImage($image);
                $image->setProfilPic($participant);
                $entityManager->persist($image);
            }
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

//dd($participant);
        return $this->render('participant/show.html.twig', [
            'participant' => $participant,

        ]);
    }

    #[Route('/edit/{id}', name: 'app_participant_edit', methods: ['GET', 'POST'])]
    #[IsGranted("ROLE_USER")]
    public function edit(Request $request, Participant $participant,UserPasswordHasherInterface $passwordHasher ,EntityManagerInterface $entityManager): Response
    {
        if($this->getUser() !== $participant && $this->isCsrfTokenValid('editer' . $participant->getId(), $request->request->get('_token'))) {
            return $this->redirectToRoute('app_sortie_index', [], Response::HTTP_FORBIDDEN);
        }
        $form = $this->createForm(ParticipantType::class, $participant, [
            'email_field' => false,
            ]);
        $currentPassword = $participant->getPassword();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $password = $form->get('password')->getData() ? $passwordHasher->hashPassword($participant, $form->get('password')->getData()) : $currentPassword ;
            $participant->setPassword($password);


            $image = $participant->getImage();
            $image->setProfilPic($participant);



            $entityManager->flush();

            return $this->redirectToRoute('app_participant_edit', [
                'id' => $participant->getId(),

            ], Response::HTTP_SEE_OTHER);
        }

        return $this->render('participant/edit.html.twig', [
            'participant' => $participant,
            'form' => $form,
        ]);
    }

    #[Route('/delete/{id}', name: 'app_participant_delete', methods: ['GET'])]
    #[IsGranted("ROLE_ADMIN")]
    public function delete(Request $request, Participant $participant, EntityManagerInterface $entityManager): Response
    {
        $token = $request->query->get('token');

        if ($this->isCsrfTokenValid('delete' . $participant->getId(), $token)) {

            $entityManager->remove($participant);
            $entityManager->flush();
            return $this->redirectToRoute('app_participant_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->redirectToRoute('app_participant_edit', [
            'id' => $participant->getId(),
        ], Response::HTTP_SEE_OTHER);


    }

    /**
     * @throws TransportExceptionInterface
     */
    #[Route('/inactif/{id}', name: 'app_participant_inactif', methods: ['GET'])]
    #[IsGranted("ROLE_USER")]
    public function inactif(Request $request, Participant $participant, EntityManagerInterface $entityManager, SortieRepository $sortieRepository, AnnulerSortie $annulerSortie): Response
    {


        if ($this-> isCsrfTokenValid('inactif'.$participant->getId(), $request->query->get('_token'))) {

            $sortiesEnTantQuOrganisateur= $sortieRepository->find($participant->getId());



            // Si le participant est organisateur de sorties, on les annule et les supprime
            if (!empty($sortiesEnTantQuOrganisateur)) {
                foreach ($sortiesEnTantQuOrganisateur as $sortie) {
                    $annulerSortie->annuler($sortie);
                    $entityManager->remove($sortie);
                }
            }

            $sortieRepository->removeParticipantFromSorties($participant);
            $participant->setActif(false);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_participant_index', [], Response::HTTP_SEE_OTHER);
    }


}
