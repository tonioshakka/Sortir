<?php

namespace App\Entity;

use App\Repository\ImageRepository;
use Doctrine\ORM\Mapping as ORM;

use Symfony\Component\HttpFoundation\File\File;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

#[ORM\Entity(repositoryClass: ImageRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[Vich\Uploadable]
class Image implements \Serializable
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]


    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $imageName = null;

    #[ORM\Column(length: 255)]

    #[Vich\UploadableField(mapping: 'profil_pic', fileNameProperty: 'imageName', size: 'imageSize')]

    private File|string|null $imageFile;

    private ?int $imageSize = null;

    #[ORM\Column(nullable: true)]

    private ?\DateTimeImmutable $updatedAt = null;


    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\OneToOne(inversedBy: 'image', cascade: ['persist', 'remove'])]
    private ?Participant $profil_pic = null;


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getImageName(): ?string
    {
        return $this->imageName;
    }

    public function setImageName(string $imageName): static
    {
        $this->imageName = $imageName;

        return $this;
    }





    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }
    #[ORM\PreUpdate]
    public function setUpdatedAt(?\DateTimeImmutable $updatedAt): void
    {
        $this->updatedAt = new \DateTimeImmutable('now');
    }

    public function getImageSize(): ?int
    {
        return $this->imageSize;
    }

    public function setImageSize(?int $imageSize): void
    {
        $this->imageSize = $imageSize;
    }

    #[ORM\PrePersist]
    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(?\DateTimeImmutable $createdAt): void
    {
        $this->createdAt = new \DateTimeImmutable('now');
    }

    public function getProfilPic(): ?Participant
    {
        return $this->profil_pic;
    }

    public function setProfilPic(?Participant $profil_pic): static
    {
        $this->profil_pic = $profil_pic;

        return $this;
    }
    public function getImageFile(): ?File
    {
        return $this->imageFile;
    }

    public function setImageFile(File|string|null $imageFile): void
    {
        $this->imageFile = $imageFile;

        if (null !== $imageFile) {
            // It is required that at least one field changes if you are using doctrine
            // otherwise the event listeners won't be called and the file is lost
            $this->updatedAt = new \DateTimeImmutable();
        }
    }

    public function serialize()
    {
        $this->imageFile = base64_encode($this->imageFile);
    }
#[ORM\PostLoad]
    public function unserialize($serialized)
{
    $this->imageFile = base64_decode($this->imageFile);


}

    }
