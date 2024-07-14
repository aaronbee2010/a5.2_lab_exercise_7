<?php declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\DataProviderExternal;
use App\Customer;
use App\SavingsAccount;
use App\AccountRepository;

class ExternalDataProvider {
    public static function validInterestRateProvider(): array {
        return [
            [0.01],
            [0.10],
            [1.00],
            [10.00],
            [100.00],
            [1000.00],
            [10000.00],
            [100000.00],
            [1000000.00]
        ];
    }

    public static function invalidInterestRateProvider(): array {
        return [
            [0.00],
            [-0.00],
            [-0.01],
            [-0.10],
            [-1.00],
            [-10.00],
            [-100.00],
            [-1000.00],
            [-10000.00],
            [-100000.00],
            [-1000000.00]
        ];
    }
}

#[CoversClass(SavingsAccount::class)]
class SavingsAccountTest extends TestCase {
    private SavingsAccount $savingsAccount;

    #[Before]
    public function setUp(): void {
        $customer = new Customer();
        $accountRepository = $this->createMock(AccountRepository::class);
        $accountRepository->method("saveNewAccountAndReturnAccountNumber")
                          ->willReturn(35);
        $this->savingsAccount = new SavingsAccount($customer, $accountRepository);
    }

    #[Test]
    #[DataProviderExternal(ExternalDataProvider::class, "validInterestRateProvider")]
    public function setInterestRate_addPositiveAmountOfInterest_addedSuccessfully(float $interestRate): void {
        // Act
        $this->savingsAccount->setInterestRate($interestRate);

        // Assert
        $this->assertSame($interestRate, $this->savingsAccount->getInterestRate());
    }

    #[Test]
    #[DataProviderExternal(ExternalDataProvider::class, "invalidInterestRateProvider")]
    public function setInterestRate_ZeroOrNegativeValue_throwsValueError(float $interestRate): void {
        // Arrange / Assert
        $this->expectException(ValueError::class);
        $this->expectExceptionMessage("Cannot set interest rate to zero or negative value");

        // Act
        $this->savingsAccount->setInterestRate($interestRate);
    }

    #[Test]
    #[DataProviderExternal(ExternalDataProvider::class, "validInterestRateProvider")]
    public function addInterestToAccount_addPositiveAmountOfInterest_balanceGetsMultipliedByInterest(float $interestRate): void {
        // Arrange
        $this->savingsAccount->setInterestRate($interestRate);

        $startingBalance = 5000.00;
        $this->savingsAccount->deposit($startingBalance);

        $expected = $startingBalance * ($interestRate + 1);

        // Act
        $this->savingsAccount->addInterestToAccount();

        // Assert
        $this->assertSame($expected, $this->savingsAccount->getBalance());
    }
}
