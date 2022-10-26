<?php

namespace App\Entity;

use App\Repository\TrickRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TrickRepository::class)]
class Trick
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $title = null;

    #[ORM\Column(length: 255)]
    private ?string $slug = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $description = null;

    #[ORM\ManyToOne(inversedBy: 'tricks')]
    private ?User $user = null;

//    #[ORM\OneToMany(mappedBy: 'trick', targetEntity: Media::class, orphanRemoval: true)]
//    private Collection $medias;

    #[ORM\OneToMany(mappedBy: 'trick', targetEntity: Image::class)]
    private Collection $images;

    #[ORM\OneToMany(mappedBy: 'trick', targetEntity: Video::class)]
    private Collection $videos;

    private \DateTimeInterface $createdAt;
    private \DateTimeInterface $updatedAt;
//
//    #[ORM\ManyToOne(inversedBy: 'tricks')]
//    #[ORM\JoinColumn(nullable: false)]
//    private ?Category $category = null;


    public function __construct()
    {
//        $this->medias = new ArrayCollection();
        $this->images = new ArrayCollection();
        $this->videos = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }
    public function __toString(): string
    {
        return $this->title;
    }
    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): self
    {
        $this->slug = $slug;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeInterface $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

//    /**
//     * @return Collection<int, Media>
//     */
//    public function getMedias(): Collection
//    {
//        return $this->medias;
//    }
//    public function setMedia($media): self
//    {
//
//        foreach ($medias as $media) {
//            $media = new Media();
//            $media->setImage((string)$media);
//            $this->addMedia($media);
//        }
//        $this->medias = $media;
//        return $this;
//    }
//
//    public function addMedia(Media $media): self
//    {
//        if (!$this->medias->contains($media)) {
//            $this->medias->add($media);
//            $media->setTrick($this);
//        }
//
//        return $this;
//    }
//
//
//
//    public function removeMedia(Media $media): self
//    {
//        if ($this->medias->removeElement($media)) {
//            // set the owning side to null (unless already changed)
//            if ($media->getTrick() === $this) {
//                $media->setTrick(null);
//            }
//        }
//
//        return $this;
//    }

    /**
     * @return Collection<int, Image>
     */

    public function getImages(): Collection
    {
        return $this->images;
    }

    public function addImage(Image $image): self
    {
        if (!$this->images->contains($image)) {
            $this->images->add($image);
            $image->setTrick($this);
        }
        return $this;
    }
    public function removeImage(Image $image): self
    {
        if ($this->images->removeElement($image)) {
            // set the owning side to null (unless already changed)
            if ($image->getTrick() === $this) {
                $image->setTrick(null);
            }
        }
        return $this;
    }

    /**
     * @return Collection<int, Video>
     */
    public function getVideos(): Collection
    {
        return $this->videos;
    }

    public function addVideo(Video $video): self
    {
        if (!$this->videos->contains($video)) {
            $this->videos->add($video);
            $video->setTrick($this);
        }

        return $this;
    }
    public function removeVideo(Video $video): self
    {
        if ($this->videos->removeElement($video)) {
            // set the owning side to null (unless already changed)
            if ($video->getTrick() === $this) {
                $video->setTrick(null);
            }
        }
        return $this;
    }

    public function getFirstImage() : ?Image
    {
        return $this->images->first() ?: null;
    }

//    public function getCategory(): ?Category
//    {
//        return $this->category;
//    }
//
//    public function setCategory(?Category $category): self
//    {
//        $this->category = $category;
//
//        return $this;
//    }




}
