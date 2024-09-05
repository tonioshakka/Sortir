<?php

namespace App\Controller;

use App\Form\CSVUploadType;
use App\Service\ImportCSV;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class ImportCSVController extends AbstractController
{
    #[Route('/import/c/s/v', name: 'app_import_c_s_v')]
    public function index(): Response
    {
        return $this->render('import_csv/index.html.twig', [
            'controller_name' => 'ImportCSVController',
        ]);
    }


    #[Route('/import_csv', name: 'app_participant_csv', methods: ['GET'])]
    #[IsGranted("ROLE_ADMIN")]
    public function showImportForm(): Response
    {

        $form = $this->createForm(CSVUploadType::class);

        return $this->render('participant/import_csv.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/import_csv', name: 'app_participant_csv_process', methods: ['POST'])]
    #[IsGranted("ROLE_ADMIN")]
    public function importCSV(Request $request, importCSV $csvImporter): Response
    {
        dump($request->attributes->all()); // Inspectez les attributs de la requête

        $form = $this->createForm(CSVUploadType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $file = $form->get('csv_file')->getData();

            if ($file) {
                $filePath = $file->getPathname();
                try {
                    $csvImporter->import($filePath);

                    $this->addFlash('success', 'Le fichier CSV a été importé avec succès !');
                } catch (\Exception $e) {
                    $this->addFlash('error', 'Une erreur est survenue lors de l\'importation du fichier CSV.');
                }
                return $this->redirectToRoute('app_participant_csv');
            }
        }

        return $this->render('participant/import_csv.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}