<?php declare(strict_types=1);

namespace App;

class SavingsAccount extends Account {
    protected float $interestRate;

    public function getInterestRate(): float {
        return $this->interestRate;
    }

    public function setInterestRate(float $interestRate): void {
        if ($interestRate <= 0.00) {
            throw new \ValueError("Cannot set interest rate to zero or negative value");
        }

        $this->interestRate = $interestRate;
    }

    public function addInterestToAccount(): void {
        $this->balance = $this->balance * ($this->interestRate + 1);
    }
}
