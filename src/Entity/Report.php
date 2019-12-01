<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ReportRepository")
 */
class Report
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="reports")
     * @ORM\JoinColumn(nullable=false)
     * @Assert\NotBlank
     */
    private $user;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank
     */
    private $edrpou;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank
     */
    private $name;

    /**
     * @ORM\Column(type="integer")
     * @Assert\NotBlank
     * @Assert\Type(type="integer")
     */
    private $nreg;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank
     */
    private $file;

    /**
     * @ORM\Column(type="date")
     * @Assert\NotBlank
     */
    private $public_dt;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getEdrpou(): ?string
    {
        return $this->edrpou;
    }

    public function setEdrpou(string $edrpou): self
    {
        $this->edrpou = $edrpou;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getNreg(): ?int
    {
        return $this->nreg;
    }

    public function setNreg(int $nreg): self
    {
        $this->nreg = $nreg;

        return $this;
    }

    public function getFile(): ?string
    {
        return $this->file;
    }

    public function setFile(string $file): self
    {
        $this->file = $file;

        return $this;
    }

    public function getPublicDt(): ?\DateTimeInterface
    {
        return $this->public_dt;
    }

    public function setPublicDt(\DateTimeInterface $public_dt): self
    {
        $this->public_dt = $public_dt;

        return $this;
    }
}
