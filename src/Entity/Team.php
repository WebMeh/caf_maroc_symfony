<?php

namespace App\Entity;

use App\Repository\TeamRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TeamRepository::class)]
class Team
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 20)]
    private ?string $name = null;

    #[ORM\Column(length: 20)]
    private ?string $country = null;

    #[ORM\Column]
    private ?int $ranking = null;

    #[ORM\Column(type: 'integer')]
    private int $points = 0;

    #[ORM\Column(type: 'integer')]
    private int $matchesPlayed = 0;

    #[ORM\Column(type: 'integer')]
    private int $wins = 0;

    #[ORM\Column(type: 'integer')]
    private int $draws = 0;

    #[ORM\Column(type: 'integer')]
    private int $losses = 0;

    #[ORM\Column(type: 'integer')]
    private int $goalsFor = 0;

    #[ORM\Column(type: 'integer')]
    private int $goalsAgainst = 0;


    /**
     * @var Collection<int, Player>
     */
    #[ORM\OneToMany(targetEntity: Player::class, mappedBy: 'team')]
    private Collection $players;

    /**
     * @var Collection<int, Matche>
     */
    #[ORM\OneToMany(targetEntity: Matche::class, mappedBy: 'team1')]
    private Collection $matches;

    #[ORM\Column(nullable: true)]
    private ?int $goalDifference = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $logo = null;

    public function __construct()
    {
        $this->players = new ArrayCollection();
        $this->matches = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPoints(): ?int
    {
        return $this->points;
    }

    public function getMatchesPlayed(): ?int
    {
        return $this->matchesPlayed;
    }

    public function getWins(): ?int
    {
        return $this->wins;
    }

    public function getDraws(): ?int
    {
        return $this->draws;
    }

    public function getLosses(): ?int
    {
        return $this->losses;
    }

    public function getGoalsFor(): ?int
    {
        return $this->goalsFor;
    }

    public function getGoalsAgainst(): ?int
    {
        return $this->goalsAgainst;
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

    public function getCountry(): ?string
    {
        return $this->country;
    }

    public function setCountry(string $country): static
    {
        $this->country = $country;

        return $this;
    }

    public function getRanking(): ?int
    {
        return $this->ranking;
    }

    public function setRanking(int $ranking): static
    {
        $this->ranking = $ranking;

        return $this;
    }

    public function setPoints(int $points): static
    {
        $this->points = $points;

        return $this;
    }

    public function setGoalsFor(int $goalsFor): static
    {
        $this->goalsFor = $goalsFor;

        return $this;
    }

    public function setGoalsAgainst(int $goalsAgainst): static
    {
        $this->goalsAgainst = $goalsAgainst;

        return $this;
    }


    /**
     * @return Collection<int, Player>
     */
    public function getPlayers(): Collection
    {
        return $this->players;
    }

    public function addPlayer(Player $player): static
    {
        if (!$this->players->contains($player)) {
            $this->players->add($player);
            $player->setTeam($this);
        }

        return $this;
    }

    public function removePlayer(Player $player): static
    {
        if ($this->players->removeElement($player)) {
            // set the owning side to null (unless already changed)
            if ($player->getTeam() === $this) {
                $player->setTeam(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Matche>
     */
    public function getMatches(): Collection
    {
        return $this->matches;
    }

    public function addMatche(Matche $team2): static
    {
        if (!$this->matches->contains($team2)) {
            $this->matches->add($team2);
            $team2->setTeam1($this);
        }

        return $this;
    }

    public function removeMatche(Matche $team2): static
    {
        if ($this->matches->removeElement($team2)) {
            // set the owning side to null (unless already changed)
            if ($team2->getTeam1() === $this) {
                $team2->setTeam1(null);
            }
        }

        return $this;
    }

    public function getGoalDifference(): ?int
    {
        return $this->goalDifference;
    }

    public function setGoalDifference(?int $goalDifference): static
    {
        $this->goalDifference = $goalDifference;

        return $this;
    }

    public function getLogo(): ?string
    {
        return $this->logo;
    }

    public function setLogo(?string $logo): static
    {
        $this->logo = $logo;

        return $this;
    }
}
