<?php declare(strict_types=1);

namespace App;

abstract class Account {
    protected Customer $customer;
    protected int $accountNumber;
    protected float $balance = 0.00;

    public function __construct(Customer $customer, AccountRepository $accountRepository) {
        $this->customer = $customer;
        $this->accountNumber = $accountRepository->saveNewAccountAndReturnAccountNumber($customer);
    }

    public function getAccountNumber(): int {
        return $this->accountNumber;
    }

    public function withdraw(float $amount): bool {
        if ($amount <= 0.00) {
            throw new \ValueError("Cannot withdraw zero or negative value");
        }

        if ($amount > $this->balance) {
            throw new \Exception("Insufficient funds");
        }

        $this->balance -= $amount;
        return true;
    }

    public function deposit(float $amount): void {
        $this->balance += $amount;
    }

    public function getBalance(): float {
        return $this->balance;
    }
}
