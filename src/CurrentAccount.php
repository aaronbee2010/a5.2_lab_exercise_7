<?php declare(strict_types=1);

namespace App;

class CurrentAccount extends Account {
    protected float $overdraftLimit;
    protected array $directDebits = [];
    protected array $standingOrders = [];

    public function setOverdraftLimit(float $overdraftLimit): void {
        $this->overdraftLimit = $overdraftLimit;
    }
    
    public function withdraw(float $amount): bool {
        if ($amount <= 0.00) {
            throw new \ValueError("Cannot withdraw zero or negative amount");
        }

        if (-$this->overdraftLimit <= $this->balance - $amount) {
            $this->balance -= $amount;

            return true;
        }

        throw new \Exception("Cannot withdraw without exceeding overdraft limit");
    }

    public function isOverDrawn(): bool {
        return $this->balance < 0.00;
    }
    
    public function processDirectDebits(): void {
        foreach ($this->directDebits as $payee) {
            throw new \Exception("Not yet implemented");
        }
    }
    
    public function processStandingOrders(): void {
        foreach ($this->standingOrders as $payee) {
            throw new \Exception("Not yet implemented");
        }
    }
}
