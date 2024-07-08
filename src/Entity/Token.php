<?php

namespace App\Entity;

use App\Repository\TokenRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: TokenRepository::class)]
class Token
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 1024)]
    private ?string $tokenValue = null;

    #[ORM\Column(type: 'boolean')]
    private bool $expired = false;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'tokens')]
    #[ORM\JoinColumn(nullable: false)]
    private ?UserInterface $user = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTokenValue(): ?string
    {
        return $this->tokenValue;
    }

    public function setTokenValue(string $tokenValue): self
    {
        $this->tokenValue = $tokenValue;

        return $this;
    }

    public function isExpired(): bool
    {
        return $this->expired;
    }

    public function setExpired(bool $expired): self
    {
        $this->expired = $expired;

        return $this;
    }

    public function getUser(): ?UserInterface
    {
        return $this->user;
    }

    public function setUser(UserInterface $user): self
    {
        $this->user = $user;

        return $this;
    }
}
