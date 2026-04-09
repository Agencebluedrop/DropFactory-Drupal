<?php

namespace App\Entity\Remote;

use App\Repository\Remote\SiteRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SiteRepository::class)]
class Site
{
    public const STATUS_ENABLED = 'ENABLED';
    public const STATUS_DISABLED = 'DISABLED';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    private ?string $domain = null;

    #[ORM\Column(length: 255)]
    private ?string $language = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $image = null;

    #[ORM\Column(length: 255)]
    private ?string $status = null;

    /**
     * @var Collection<int, Alias>
     */
    #[ORM\OneToMany(targetEntity: Alias::class, mappedBy: 'site', orphanRemoval: true)]
    private Collection $aliases;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Profile $install_profile = null;

    #[ORM\ManyToOne(inversedBy: 'sites')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Platform $platform = null;

    public function __construct()
    {
        $this->aliases = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getDomain(): ?string
    {
        return $this->domain;
    }

    public function setDomain(string $domain): static
    {
        $this->domain = $domain;

        return $this;
    }

    public function getLanguage(): ?string
    {
        return $this->language;
    }

    public function setLanguage(string $language): static
    {
        $this->language = $language;

        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): static
    {
        $this->image = $image;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @return Collection<int, Alias>
     */
    public function getAliases(): Collection
    {
        return $this->aliases;
    }

    public function addAlias(Alias $alias): static
    {
        if (!$this->aliases->contains($alias)) {
            $this->aliases->add($alias);
            $alias->setSite($this);
        }

        return $this;
    }

    public function removeAlias(Alias $alias): static
    {
        if ($this->aliases->removeElement($alias)) {
            // set the owning side to null (unless already changed)
            if ($alias->getSite() === $this) {
                $alias->setSite(null);
            }
        }

        return $this;
    }

    public function getInstallProfile(): ?Profile
    {
        return $this->install_profile;
    }

    public function setInstallProfile(?Profile $install_profile): static
    {
        $this->install_profile = $install_profile;

        return $this;
    }

    public function getPlatform(): ?Platform
    {
        return $this->platform;
    }

    public function setPlatform(?Platform $platform): static
    {
        $this->platform = $platform;

        return $this;
    }
}
