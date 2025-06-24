<?php

namespace App\Entity\Remote;

use App\Repository\Remote\PlatformRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PlatformRepository::class)]
class Platform
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
    private ?string $status = null;

    #[ORM\Column(length: 255)]
    private ?string $gitRepositoryURL = null;

    #[ORM\Column(length: 255)]
    private ?string $gitRepositoryBranch = null;

    /**
     * @var Collection<int, Profile>
     */
    #[ORM\OneToMany(targetEntity: Profile::class, mappedBy: 'platform', orphanRemoval: true)]
    private Collection $profiles;

    /**
     * @var Collection<int, Site>
     */
    #[ORM\OneToMany(targetEntity: Site::class, mappedBy: 'platform')]
    private Collection $sites;

    public function __construct()
    {
        $this->profiles = new ArrayCollection();
        $this->sites = new ArrayCollection();
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

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getGitRepositoryURL(): ?string
    {
        return $this->gitRepositoryURL;
    }

    public function setGitRepositoryURL(string $gitRepositoryURL): static
    {
        $this->gitRepositoryURL = $gitRepositoryURL;

        return $this;
    }

    public function getGitRepositoryBranch(): ?string
    {
        return $this->gitRepositoryBranch;
    }

    public function setGitRepositoryBranch(string $gitRepositoryBranch): static
    {
        $this->gitRepositoryBranch = $gitRepositoryBranch;

        return $this;
    }

    /**
     * @return Collection<int, Profile>
     */
    public function getProfiles(): Collection
    {
        return $this->profiles;
    }

    public function addProfile(Profile $profile): static
    {
        if (!$this->profiles->contains($profile)) {
            $this->profiles->add($profile);
            $profile->setPlatform($this);
        }

        return $this;
    }

    public function removeProfile(Profile $profile): static
    {
        if ($this->profiles->removeElement($profile)) {
            // set the owning side to null (unless already changed)
            if ($profile->getPlatform() === $this) {
                $profile->setPlatform(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Site>
     */
    public function getSites(): Collection
    {
        return $this->sites;
    }

    public function addSite(Site $site): static
    {
        if (!$this->sites->contains($site)) {
            $this->sites->add($site);
            $site->setPlatform($this);
        }

        return $this;
    }

    public function removeSite(Site $site): static
    {
        if ($this->sites->removeElement($site)) {
            // set the owning side to null (unless already changed)
            if ($site->getPlatform() === $this) {
                $site->setPlatform(null);
            }
        }

        return $this;
    }

    public function __toString(): string
    {
        return $this->name ?? '';
    }
}
