<?php

namespace App\Topnode\BaseBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Money\Currencies\ISOCurrencies;
use Money\Formatter\IntlMoneyFormatter;
use Money\Money;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * This trait has the base and default fields and functions for handling money.
 */
trait MoneyTrait
{
    /**
     * @ORM\Column(type="string", length=3, options={"default":"BRL"})
     * @Assert\Length(
     *      min = 3,
     *      max = 3
     * )
     * @Assert\Currency()
     */
    protected $moneyCurrency = 'BRL';

    /**
     * @ORM\Column(type="string", length=16)
     * @Assert\Length(
     *      min = 0,
     *      max = 16
     * )
     * @Assert\Regex("/^-?[\d]{0,15}$/")
     */
    protected $moneyAmount;

    public function setMoneyCurrency(string $moneyCurrency): self
    {
        $this->moneyCurrency = $moneyCurrency;

        return $this;
    }

    public function getMoneyCurrency(): string
    {
        return $this->moneyCurrency;
    }

    public function setMoneyAmount($moneyAmount): self
    {
        if ($moneyAmount instanceof Money) {
            $this->moneyAmount = $moneyAmount->getAmount();
        } else {
            $this->moneyAmount = $moneyAmount;
        }

        return $this;
    }

    public function getMoneyAmount($asMoneyObject = false)
    {
        if ($asMoneyObject) {
            return Money::{$this->moneyCurrency}((int) $this->moneyAmount);
        }

        return $this->moneyAmount;
    }

    public function moneyAsString()
    {
        $numberFormatter = new \NumberFormatter('pt_BR', \NumberFormatter::CURRENCY);
        $moneyFormatter = new IntlMoneyFormatter($numberFormatter, new ISOCurrencies());

        return $moneyFormatter->format($this->getMoneyAmount(true));
    }
}
