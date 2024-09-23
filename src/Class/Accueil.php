<?php

namespace App\Class;

use App\Entity\Campus;
use App\Entity\Sortie;

class Accueil
{
    private ?Campus $campus;
    private ?string $nom;
    private ?\DateTimeInterface $dateDebut;
    private ?\DateTimeInterface $dateFin;
    private bool $organisateur;
    private bool $inscrit;
    private bool $nonInscrit;
    private bool $sortiesPassees;

    public function __construct(?Campus $campus, ?string $nom, ?\DateTimeInterface $dateDebut, ?\DateTimeInterface $dateFin, bool $organisateur, bool $inscrit, bool $nonInscrit, bool $sortiesPassees)
    {
        $this->campus = $campus;
        $this->nom = $nom;
        $this->dateDebut = $dateDebut;
        $this->dateFin = $dateFin;
        $this->organisateur = $organisateur;
        $this->inscrit = $inscrit;
        $this->nonInscrit = $nonInscrit;
        $this->sortiesPassees = $sortiesPassees;
    }

    public function getCampus(): ?Campus
    {
        return $this->campus;
    }
    public function setCampus(?Campus $campus): self
    {
        $this->campus = $campus;
        return $this;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }
    public function setNom(?string $nom): self
    {
        $this->nom = $nom;
        return $this;
    }

    public function getDateDebut(): ?\DateTimeInterface
    {
        return $this->dateDebut;
    }
    public function setDateDebut(?\DateTimeInterface $dateDebut): self
    {
        $this->dateDebut = $dateDebut;
        return $this;
    }

    public function getDateFin(): ?\DateTimeInterface
    {
        return $this->dateFin;
    }
    public function setDateFin(?\DateTimeInterface $dateFin): self
    {
        $this->dateFin = $dateFin;
        return $this;
    }

    public function getOrganisateur(): bool
    {
        return $this->organisateur;
    }
    public function setOrganisateur(bool $organisateur): self
    {
        $this->organisateur = $organisateur;
        return $this;
    }

    public function getInscrit(): bool
    {
        return $this->inscrit;
    }
    public function setInscrit(bool $inscrit): self
    {
        $this->inscrit = $inscrit;
        return $this;
    }

    public function getNonInscrit(): bool
    {
        return $this->nonInscrit;
    }
    public function setNonInscrit(bool $nonInscrit): self
    {
        $this->nonInscrit = $nonInscrit;
        return $this;
    }

    public function getSortiesPassees(): bool
    {
        return $this->sortiesPassees;
    }
    public function setSortiesPassees(bool $sortiesPassees): self
    {
        $this->sortiesPassees = $sortiesPassees;
        return $this;
    }
}