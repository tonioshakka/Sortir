<?php

namespace App\Controller;

use App\Entity\Lieu;
use App\Form\LieuType;
use App\Repository\LieuRepository;
use App\Service\LieuService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\UX\Map\Bridge\Leaflet\LeafletOptions;
use Symfony\UX\Map\Bridge\Leaflet\Option\TileLayer;
use Symfony\UX\Map\Map;
use Symfony\UX\Map\Marker;
use Symfony\UX\Map\Point;

#[Route('/lieu')]
#[IsGranted("ROLE_USER")]
class LieuController extends AbstractController
{
    #[Route('/', name: 'app_lieu_index', methods: ['GET'])]
    public function index(LieuRepository $lieuRepository): Response
    {
        return $this->render('lieu/index.html.twig', [
            'lieus' => $lieuRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_lieu_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $lieu = new Lieu();
        $form = $this->createForm(LieuType::class, $lieu);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($lieu);
            $entityManager->flush();

            return $this->redirectToRoute('app_lieu_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('lieu/new.html.twig', [
            'lieu' => $lieu,
            'form' => $form,
        ]);
    }
    #[Route('/getCoordinate', name: 'app_lieu_getCoordinate')]
    public function getCoordinate(HttpClientInterface $http, Request $request): JsonResponse {
        $adresse = $request->query->get('adresse');
        $response = $http->request('GET', 'https://api-adresse.data.gouv.fr/search/?q=' . $adresse);
        $data = json_decode($response->getContent(), true)['features'][0]['geometry']['coordinates'];
        if (!$data) {
            return new JsonResponse(['message' => 'Lieu not found'], Response::HTTP_NOT_FOUND);
        }
        return new JsonResponse($data, Response::HTTP_OK);
    }

    #[Route('/getMap', name: 'app_map')]
    public function getMap(LieuService $lieuService, Request $req): Response
    {
        $longitude = $req->query->get('longitude');
        $latitude = $req->query->get('latitude');

        $responseBody = $this->renderView('ux_packages/map.html.twig', ['map' => $lieuService->getMap($latitude, $longitude),]);
        return new JsonResponse($responseBody, Response::HTTP_OK);
    }

    #[Route('/{id}', name: 'app_lieu_show', methods: ['GET'])]
    public function show(Lieu $lieu): Response
    {
        return $this->render('lieu/show.html.twig', [
            'lieu' => $lieu,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_lieu_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Lieu $lieu, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(LieuType::class, $lieu);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_lieu_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('lieu/edit.html.twig', [
            'lieu' => $lieu,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_lieu_delete', methods: ['POST'])]
    public function delete(Request $request, Lieu $lieu, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$lieu->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($lieu);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_lieu_index', [], Response::HTTP_SEE_OTHER);
    }


}
