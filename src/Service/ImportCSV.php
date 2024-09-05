<?php

namespace App\Service;


//use App\Entity\Participant;
use App\Entity\Participant;
use App\Entity\Site;
use App\Repository\SiteRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityNotFoundException;

class ImportCSV
{
    private EntityManagerInterface $entityManager;
    private SiteRepository $siteRepository;

    public function __construct(EntityManagerInterface $entityManager, SiteRepository $siteRepository)
    {
        $this->entityManager = $entityManager;
        $this->siteRepository = $siteRepository;
    }

    public function import(string $filePath): void
    {
        if (($handle = fopen($filePath, "r")) !== FALSE) {
            $header = true; // Pour ignorer les en-têtes, si présents
            while (($data = fgetcsv($handle, 1000, ";")) !== FALSE) {
                if ($header) {
                    $header = false; // Ignorer la première ligne si elle contient des en-têtes
                    continue;
                }

                // Validation des données
//                if (count($data) < 5) {
//                    continue; // Ignore les lignes mal formées
//                }

                $participant = new Participant();
                $participant->setEmail(filter_var($data[0], FILTER_VALIDATE_EMAIL)); // Validation de l'email
                $participant->setRoles([$data[1]]);
                $participant->setPassword(trim($data[2]));
                $participant->setNom(trim($data[3]));
                $participant->setPrenom(trim($data[4]));
                $participant->setTelephone(trim($data[5]));
                $participant->setActif((int) $data[6]);


                $siteId = (int)$data[7];
                $site = $this->siteRepository->find($siteId);

                if (!$site) {
                    // Si le site avec l'ID spécifié n'est pas trouvé, utilisez un site par défaut (ID 1 par exemple)
                    $site = $this->siteRepository->find(1);
                }

                $participant->setSite($site);

                $this->entityManager->persist($participant);

            }
            fclose($handle);

            $this->entityManager->flush();
        }
    }

}
