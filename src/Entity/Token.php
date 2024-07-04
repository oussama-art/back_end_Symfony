<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: "App\Repository\TokenRepository")]
class Token
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private ?int $id;

    #[ORM\Column(type: "text")]
    private ?string $tokenValue;

    #[ORM\Column(type: "boolean")]
    private bool $expired;

    #[ORM\ManyToOne(targetEntity: "App\Entity\User", inversedBy: "tokens")]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user;

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

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }
}
