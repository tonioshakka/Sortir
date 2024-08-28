<?php

namespace App\Entity;

use App\EventListener\SortieEntityListener;
use App\Repository\SortieRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: SortieRepository::class)]
#[ORM\EntityListeners([SortieEntityListener::class])]
class Sortie
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Vous devez nommer votre sortie')]
    private ?string $nom = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Assert\NotBlank(message: 'La date de début est requise')]
    #[Assert\Type(Types::DATETIME_MUTABLE, message: "Le format de la date est invalide")]
    #[Assert\GreaterThan('now', message: 'Le début de votre sortie doit se situer dabs le futur')]
    private ?\DateTimeInterface $dateHeureDebut = null;

    #[ORM\Column(nullable: true)]
    #[Assert\Type(Types::INTEGER, message: 'format de durée invalide')]
    #[Assert\Positive(message: 'La durée doit être supérieure à 0')]
    private ?int $duree = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    #[Assert\Type(Types::DATETIME_MUTABLE, message: 'Format de date invalide')]
    #[Assert\LessThan(propertyPath: 'dateHeureDebut', message: 'La fin des inscriptions doit être anterieur à la date de début de la sortie')]
    #[Assert\GreaterThan('now', message: 'La date limite d\'inscription doit se situer dabs le futur')]
    private ?\DateTimeInterface $dateLimiteInscription = null;

    #[ORM\Column(nullable: true)]
    #[Assert\Type(Types::INTEGER)]
    #[Assert\Positive(message: 'Le nombre limite de participant doit être supérieur à 0')]
    private ?int $nbInscriptionsMax = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $infosSortie = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(message: 'Vous devez renseigner l\'état')]
    private ?Etat $etat = null;

    /**
     * @var Collection<int, Participant>
     */
    #[ORM\ManyToMany(targetEntity: Participant::class, inversedBy: 'sorties')]
    private Collection $participant;

    #[ORM\ManyToOne(inversedBy: 'mesSorties')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Participant $organisateur = null;

    #[ORM\ManyToOne(targetEntity: Lieu::class, cascade: ['persist'], inversedBy: 'sorties')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\Valid]
    private ?Lieu $lieu = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $motifAnnuler = null;



    public function __construct()
    {
        $this->participant = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): static
    {
        $this->nom = $nom;

        return $this;
    }

    public function getDateHeureDebut(): ?\DateTimeInterface
    {
        return $this->dateHeureDebut;
    }

    public function setDateHeureDebut(\DateTimeInterface $dateHeureDebut): static
    {
        $this->dateHeureDebut = $dateHeureDebut;

        return $this;
    }

    public function getDuree(): ?int
    {
        return $this->duree;
    }

    public function setDuree(?int $duree): static
    {
        $this->duree = $duree;

        return $this;
    }

    public function getDateLimiteInscription(): ?\DateTimeInterface
    {
        return $this->dateLimiteInscription;
    }

    public function setDateLimiteInscription(?\DateTimeInterface $dateLimiteInscription): static
    {
        $this->dateLimiteInscription = $dateLimiteInscription;

        return $this;
    }

    public function getNbInscriptionsMax(): ?int
    {
        return $this->nbInscriptionsMax;
    }

    public function setNbInscriptionsMax(?int $nbInscriptionsMax): static
    {
        $this->nbInscriptionsMax = $nbInscriptionsMax;

        return $this;
    }

    public function getInfosSortie(): ?string
    {
        return $this->infosSortie;
    }

    public function setInfosSortie(string $infosSortie): static
    {
        $this->infosSortie = $infosSortie;

        return $this;
    }

    public function getEtat(): ?Etat
    {
        return $this->etat;
    }

    public function setEtat(?Etat $etat): static
    {
        $this->etat = $etat;

        return $this;
    }

    /**
     * @return Collection<int, Participant>
     */
    public function getParticipant(): Collection
    {
        return $this->participant;
    }

    public function addParticipant(Participant $participant): static
    {
        if (!$this->participant->contains($participant)) {
            $this->participant->add($participant);
        }

        return $this;
    }

    public function removeParticipant(Participant $participant): static
    {
        $this->participant->removeElement($participant);

        return $this;
    }

    public function getOrganisateur(): ?Participant
    {
        return $this->organisateur;
    }

    public function setOrganisateur(Participant $organisateur): static
    {
        $this->organisateur = $organisateur;

        return $this;
    }

    public function getLieu(): ?Lieu
    {
        return $this->lieu;
    }

    public function setLieu(?Lieu $lieu): static
    {
        $this->lieu = $lieu;

        return $this;
    }

    public function getMotifAnnuler(): ?string
    {
        return $this->motifAnnuler;
    }

    public function setMotifAnnuler(?string $motifAnnuler): static
    {
        $this->motifAnnuler = $motifAnnuler;

        return $this;
    }


}
