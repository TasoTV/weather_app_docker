<?php

namespace App\Entity;

use App\Repository\WeatherDataRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: WeatherDataRepository::class)]
class WeatherData
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $observedAt = null;

    #[ORM\Column(length: 255)]
    private ?string $parameterId = null;

    #[ORM\Column(length: 255)]
    private ?string $value = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getObservedAt(): ?\DateTimeInterface
    {
        return $this->observedAt;
    }

    public function setObservedAt(\DateTimeInterface $observedAt): static
    {
        $this->observedAt = $observedAt;

        return $this;
    }

    public function getParameterId(): ?string
    {
        return $this->parameterId;
    }

    public function setParameterId(string $parameterId): static
    {
        $this->parameterId = $parameterId;

        return $this;
    }

    public function getValue(): ?string
    {
        return $this->value;
    }

    public function setValue(string $value): static
    {
        $this->value = $value;

        return $this;
    }
}
