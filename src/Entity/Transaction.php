<?php

namespace App\Entity;

use App\Entity\Traits\AmountTransformTrait;
use App\Repository\TransactionRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TransactionRepository::class)]
class Transaction
{
    use AmountTransformTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Account $accountFrom = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Account $accountTo = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Currency $currencyFrom = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Currency $currencyTo = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 19, scale: 9)]
    private ?string $rateFrom = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 19, scale: 9)]
    private ?string $rateTo = null;

    #[ORM\Column(type: Types::BIGINT, options: ['unsigned' => true])]
    private ?string $amountFrom = null;

    #[ORM\Column(type: Types::BIGINT, options: ['unsigned' => true])]
    private ?string $amountTo = null;

    #[ORM\Column]
    private ?\DateTime $createdAt = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAccountFrom(): ?Account
    {
        return $this->accountFrom;
    }

    public function setAccountFrom(?Account $accountFrom): static
    {
        $this->accountFrom = $accountFrom;

        return $this;
    }

    public function getAccountTo(): ?Account
    {
        return $this->accountTo;
    }

    public function setAccountTo(?Account $accountTo): static
    {
        $this->accountTo = $accountTo;

        return $this;
    }

    public function getCurrencyFrom(): ?Currency
    {
        return $this->currencyFrom;
    }

    public function setCurrencyFrom(?Currency $currencyFrom): static
    {
        $this->currencyFrom = $currencyFrom;

        return $this;
    }

    public function getCurrencyTo(): ?Currency
    {
        return $this->currencyTo;
    }

    public function setCurrencyTo(?Currency $currencyTo): static
    {
        $this->currencyTo = $currencyTo;

        return $this;
    }

    public function getRateFrom(): ?string
    {
        return $this->rateFrom;
    }

    public function setRateFrom(string $rateFrom): static
    {
        $this->rateFrom = $rateFrom;

        return $this;
    }

    public function getRateTo(): ?string
    {
        return $this->rateTo;
    }

    public function setRateTo(string $rateTo): static
    {
        $this->rateTo = $rateTo;

        return $this;
    }

    public function getAmountFrom(): ?string
    {
        return $this->humanReadable($this->amountFrom);
    }

    public function setAmountFrom(string $amountFrom): static
    {
        $this->amountFrom = $this->toDatabase($amountFrom);

        return $this;
    }

    public function getAmountTo(): ?string
    {
        return $this->humanReadable($this->amountTo);
    }

    public function setAmountTo(string $amountTo): static
    {
        $this->amountTo = $this->toDatabase($amountTo);

        return $this;
    }

    public function getCreatedAt(): ?\DateTime
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTime $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }
}
