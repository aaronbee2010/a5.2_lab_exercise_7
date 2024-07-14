<?php declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\DataProvider;
use App\Customer;
use App\CurrentAccount;
use App\AccountRepository;

#[CoversClass(CurrentAccount::class)]
class CurrentAccountTest extends TestCase {
    private CurrentAccount $currentAccount;
    private $overdraftLimit = 25.00;

    #[Before]
    public function setUp(): void {
        $customer = new Customer();
        $accountRepository = $this->createMock(AccountRepository::class);
        $accountRepository->method("saveNewAccountAndReturnAccountNumber")
                          ->willReturn(35);
        $this->currentAccount = new CurrentAccount($customer, $accountRepository);
        $this->currentAccount->setOverdraftLimit($this->overdraftLimit);
    }

    public static function provider1(): array {
        return [
            [-0.01], [-0.10], [-1.00], [-10.00], [-25.00], [0.00], [-0.00]
        ];
    }

    #[Test]
    #[DataProvider("provider1")]
    public function withdraw_newCurrentAccount_withdrawNegativeAmountOrZero_throwsValueError(float $input): void {
        $this->expectException(ValueError::class);
        $this->expectExceptionMessage("Cannot withdraw zero or negative amount");
        $this->currentAccount->withdraw($input);
    }

    public static function provider2(): array {
        return [
            [0.01], [0.10], [1.00], [10.00], [25.00]
        ];
    }

    #[Test]
    #[DataProvider("provider2")]
    public function withdraw_newCurrentAccount_withdrawLessThanOverdraftLimit_executesSuccessfully(float $input): void {
        $this->assertTrue( $this->currentAccount->withdraw($input) );
    }

    public static function provider3(): array {
        return [
            [25.01], [50.00], [100.00], [1000.00], [10000.00], [100000.00], [1000000.00]
        ];
    }

    #[Test]
    #[DataProvider("provider3")]
    public function withdraw_newCurrentAccount_withdrawEqualToOrMoreThanOverdraftLimit_throwsException(float $input): void {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage("Cannot withdraw without exceeding overdraft limit");
        $this->currentAccount->withdraw($input);
    }

    #[Test]
    public function isOverDrawn_newCurrentAccount_returnsFalse(): void {
        $this->assertSame(false, $this->currentAccount->isOverdrawn());
    }
}
