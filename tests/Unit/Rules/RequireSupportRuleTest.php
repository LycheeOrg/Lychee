<?php

/**
 * We don't care for unhandled exceptions in tests.
 * It is the nature of a test to throw an exception.
 * Without this suppression we had 100+ Linter warning in this file which
 * don't help anything.
 *
 * @noinspection PhpDocMissingThrowsInspection
 * @noinspection PhpUnhandledExceptionInspection
 */

namespace Tests\Unit\Rules;

use App\Rules\RequireSupportRule;
use LycheeVerify\Contract\Status;
use LycheeVerify\Contract\VerifyInterface;
use LycheeVerify\Exceptions\SupporterOnlyOperationException;
use Tests\AbstractTestCase;

class RequireSupportRuleTest extends AbstractTestCase
{
	private function getFree(): VerifyInterface
	{
		return new class() implements VerifyInterface {
			public function get_status(): Status
			{
				return Status::FREE_EDITION;
			}

			public function check(Status $required_status = Status::SUPPORTER_EDITION): bool
			{
				return $required_status === Status::FREE_EDITION;
			}

			public function is_supporter(): bool
			{
				return false;
			}

			public function is_plus(): bool
			{
				return false;
			}

			public function authorize(Status $required_status = Status::SUPPORTER_EDITION): void
			{
				if (!$this->check($required_status)) {
					throw new SupporterOnlyOperationException($required_status);
				}
			}

			public function when(mixed $valIfTrue, mixed $valIfFalse, Status $required_status = Status::SUPPORTER_EDITION): mixed
			{
				$retValue = $this->check($required_status) ? $valIfTrue : $valIfFalse;

				return is_callable($retValue) ? $retValue() : $retValue;
			}

			public function validate(): bool
			{
				return true;
			}
		};
	}

	private function getSupporter(): VerifyInterface
	{
		return new class() implements VerifyInterface {
			public function get_status(): Status
			{
				return Status::SUPPORTER_EDITION;
			}

			public function check(Status $required_status = Status::SUPPORTER_EDITION): bool
			{
				return $required_status === Status::FREE_EDITION || $required_status === Status::SUPPORTER_EDITION;
			}

			public function is_supporter(): bool
			{
				return true;
			}

			public function is_plus(): bool
			{
				return false;
			}

			public function authorize(Status $required_status = Status::SUPPORTER_EDITION): void
			{
				if (!$this->check($required_status)) {
					throw new SupporterOnlyOperationException($required_status);
				}
			}

			public function when(mixed $valIfTrue, mixed $valIfFalse, Status $required_status = Status::SUPPORTER_EDITION): mixed
			{
				$retValue = $this->check($required_status) ? $valIfTrue : $valIfFalse;

				return is_callable($retValue) ? $retValue() : $retValue;
			}

			public function validate(): bool
			{
				return true;
			}
		};
	}

	public function testNegative(): void
	{
		$rule = new RequireSupportRule(verify: $this->getFree(), expected: '');
		$msg = "don't worry";
		$rule->validate('', '', function ($message) use (&$msg) { $msg = $message; });
		$expected = "don't worry";
		self::assertEquals($expected, $msg);
	}

	public function testRequireSupport(): void
	{
		$rule = new RequireSupportRule(verify: $this->getFree(), expected: '');
		$msg = "don't worry";
		$rule->validate('', 'value', function ($message) use (&$msg) { $msg = $message; });
		$expected = "don't worry";
		self::assertNotEquals($expected, $msg);
	}

	public function testIsSupportNegative(): void
	{
		$rule = new RequireSupportRule(verify: $this->getSupporter(), expected: '');
		$msg = "don't worry";
		$rule->validate('', '', function ($message) use (&$msg) { $msg = $message; });
		$expected = "don't worry";
		self::assertEquals($expected, $msg);
	}

	public function testIsSupport(): void
	{
		$rule = new RequireSupportRule(verify: $this->getSupporter(), expected: '');
		$msg = "don't worry";
		$rule->validate('', 'value', function ($message) use (&$msg) { $msg = $message; });
		$expected = "don't worry";
		self::assertEquals($expected, $msg);
	}
}