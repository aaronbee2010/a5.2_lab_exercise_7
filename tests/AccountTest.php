<?php declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\DataProviderExternal;
use App\Customer;
use App\Account;
use App\AccountRepository;

class ConcreteAccount extends Account {}

class ExternalProvider {
    public static function accountNumberProvider(): array {
        return [
            [1], [10], [100], [1000], [10000], [100000], [1000000]
        ];
    }

    public static function balanceAndPositiveLessThanOrEqualWithdrawAmountProvider(): array {
        return [
            [1.00, 1.00],
            [1.00, 0.99],
            [10.00, 10.00],
            [10.00, 9.99],
            [100.00, 100.00],
            [100.00, 99.99],
            [1000.00, 1000.00],
            [1000.00, 999.99],
        ];
    }

    public static function negativeOrZeroWithdrawAmountProvider(): array {
        return [
            [0.00], [-0.00], [-0.01], [-0.10], [-1.00], [-10.00], [-100.00], [-1000.00], [-10000.00], [-100000.00], [-1000000.00]
        ];
    }

    public static function balanceAndPositiveGreaterWithdrawAmountProvider(): array {
        return [
            [1.00, 1.01],
            [10.00, 10.10],
            [100.00, 101.00],
            [1000.00, 1010.00],
        ];
    }
}

#[CoversClass(Account::class)]
class AccountTest extends TestCase {
    private ConcreteAccount $account;

    #[Before]
    public function setUp(): void {
        $customer = new Customer();
        $accountRepository = $this->createMock(AccountRepository::class);
        $accountRepository->method("saveNewAccountAndReturnAccountNumber")
                          ->willReturn(35);
        $this->account = new ConcreteAccount($customer, $accountRepository);
    }

    #[Test]
    #[DataProviderExternal(ExternalProvider::class, "accountNumberProvider")]
    public function getAccountNumber_returnsCorrectValue(int $accNo): void {
        // Arrange
        $customer = new Customer();
        $accountRepository = $this->createMock(AccountRepository::class);
        $accountRepository->method("saveNewAccountAndReturnAccountNumber")
                          ->willReturn($accNo);
        $this->account = new ConcreteAccount($customer, $accountRepository);

        // Act / Assert
        $this->assertSame($accNo, $this->account->getAccountNumber());
    }

    #[Test]
    #[DataProviderExternal(ExternalProvider::class, "balanceAndPositiveLessThanOrEqualWithdrawAmountProvider")]
    public function withdraw_positiveAmountLessThanOrEqualToBalance_newBalanceReflectsChange(float $initialBalance, float $withdrawAmount): void {
        // Arrange
        $this->account->deposit($initialBalance);
        // Act
        $this->account->withdraw($withdrawAmount);
        // Assert
        $this->assertGreaterThanOrEqual(0.00, $this->account->getBalance());
    }

    #[Test]
    #[DataProviderExternal(ExternalProvider::class, "negativeOrZeroWithdrawAmountProvider")]
    public function withdraw_zeroOrNegativeAmount_throwsValueError(float $withdrawAmount): void {
        // Arrange/Assert
        $this->expectException(ValueError::class);
        $this->expectExceptionMessage("Cannot withdraw zero or negative value");
        // Act
        $this->account->withdraw($withdrawAmount);
    }

    #[Test]
    #[DataProviderExternal(ExternalProvider::class, "balanceAndPositiveGreaterWithdrawAmountProvider")]
    public function withdraw_positiveAmountGreaterThanBalance_throwsException(float $initialBalance, float $withdrawAmount): void {
        // Arrange / Assert
        $this->account->deposit($initialBalance);
        $this->expectException(Exception::class);
        $this->expectExceptionMessage("Insufficient funds");
        // Act
        $this->account->withdraw($withdrawAmount);
    }
}
