<?php

namespace App\Entity;

use App\Repository\ArticleRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ArticleRepository::class)]
class Article
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string', length: 255)]
    private $title;

    #[ORM\Column(type: 'text', nullable: true)]
    private $excerpt;

    #[ORM\Column(type: 'string', length: 255)]
    private $link;

    #[ORM\Column(type: 'string', length: 255,unique:true)]
    private $guid;

    #[ORM\Column(type: 'integer', nullable: true)]
    private $chouineurs;

    #[ORM\Column(type: 'datetime_immutable')]
    private $createdAt;

    #[ORM\Column(type: 'datetime_immutable')]
    private $updatedAt;

    #[ORM\Column(type: 'datetime_immutable')]
    private $modifiedAt;

    #[ORM\Column(type: 'boolean', nullable: true)]
    private $isFreeContent;

    #[ORM\Column(type: 'boolean')]
    private $is404 = false;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $imageUrl;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $imageALaUne;

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

    public function getExcerpt(): ?string
    {
        return $this->excerpt;
    }

    public function setExcerpt(?string $excerpt): self
    {
        $this->excerpt = $excerpt;

        return $this;
    }

    public function getLink(): ?string
    {
        return $this->link;
    }

    public function setLink(string $link): self
    {
        $this->link = $link;

        return $this;
    }

    public function getGuid(): ?string
    {
        return $this->guid;
    }

    public function setGuid(string $guid): self
    {
        $this->guid = $guid;

        return $this;
    }

    public function getChouineurs(): ?int
    {
        return $this->chouineurs;
    }

    public function setChouineurs(int $chouineurs): self
    {
        $this->chouineurs = $chouineurs;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeImmutable $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getModifiedAt(): ?\DateTimeImmutable
    {
        return $this->modifiedAt;
    }

    public function setModifiedAt(\DateTimeImmutable $modifiedAt): self
    {
        $this->modifiedAt = $modifiedAt;

        return $this;
    }

    public function getIsFreeContent(): ?bool
    {
        return $this->isFreeContent;
    }

    public function setIsFreeContent(bool $isFreeContent): self
    {
        $this->isFreeContent = $isFreeContent;

        return $this;
    }

    public function getIs404(): ?bool
    {
        return $this->is404;
    }

    public function setIs404(bool $is404): self
    {
        $this->is404 = $is404;

        return $this;
    }

    public function getImageUrl(): ?string
    {
        return $this->imageUrl;
    }

    public function setImageUrl(?string $imageUrl): self
    {
        $this->imageUrl = $imageUrl;

        return $this;
    }

    public function getImageALaUne(): ?string
    {
        return $this->imageALaUne;
    }

    public function setImageALaUne(?string $imageALaUne): self
    {
        $this->imageALaUne = $imageALaUne;

        return $this;
    }
}
