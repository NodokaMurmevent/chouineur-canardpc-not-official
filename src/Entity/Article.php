<?php

namespace App\Entity;

use App\Repository\ArticleRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

#[ORM\Entity(repositoryClass: ArticleRepository::class)]
class Article
{
    #[ORM\Id]
    #[ORM\Column(type:"uuid_binary_ordered_time", unique:true)]
    #[ORM\GeneratedValue(strategy:"CUSTOM")]
    #[ORM\CustomIdGenerator(class:"Ramsey\Uuid\Doctrine\UuidOrderedTimeGenerator")]
    private $id;

    #[ORM\Column(type: Types::STRING, length: 255)]
    private $title;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private $excerpt;

    #[ORM\Column(type: Types::STRING, length: 255)]
    private $link;

    #[ORM\Column(type: Types::STRING, length: 255,unique:true)]
    private $guid;

    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    private $chouineurs;

    #[Gedmo\Timestampable(on:"create")]
    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private $createdAt;

    #[Gedmo\Timestampable(on:"update")]
    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private $updatedAt;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    #[Gedmo\Timestampable(on: 'change', field: ['title', 'excerpt'])]
    private $contentChangedAt;

    #[ORM\Column(type: Types::BOOLEAN, nullable: true)]
    private $isFreeContent;

    #[ORM\Column(type: Types::BOOLEAN, nullable: true)]
    private $is404;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    private $imageUrl;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    private $imageALaUne;

    #[ORM\Column(type: 'datetime_immutable')]
    private $realUpdatedAt;

    #[ORM\Column(type: 'datetime_immutable')]
    private $realCreatedAt;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private $lastCheckedAt;

    public function getId()
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

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function getContentChangedAt(): ?\DateTimeImmutable
    {
        return $this->contentChangedAt;
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

    public function getRealUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->realUpdatedAt;
    }

    public function setRealUpdatedAt(\DateTimeImmutable $realUpdatedAt): self
    {
        $this->realUpdatedAt = $realUpdatedAt;

        return $this;
    }

    public function getRealCreatedAt(): ?\DateTimeImmutable
    {
        return $this->realCreatedAt;
    }

    public function setRealCreatedAt(\DateTimeImmutable $realCreatedAt): self
    {
        $this->realCreatedAt = $realCreatedAt;

        return $this;
    }

    public function getLastCheckedAt(): ?\DateTimeImmutable
    {
        return $this->lastCheckedAt;
    }

    public function setLastCheckedAt(?\DateTimeImmutable $lastCheckedAt): self
    {
        $this->lastCheckedAt = $lastCheckedAt;

        return $this;
    }
}
