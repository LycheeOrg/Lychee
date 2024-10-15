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

use App\Rules\BooleanRequireSupportRule;
use App\Rules\IntegerRequireSupportRule;
use App\Rules\StringRequireSupportRule;
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
		$rule = new BooleanRequireSupportRule(verify: $this->getFree(), expected: true);
		$msg = "don't worry";
		$rule->validate('', true, function ($message) use (&$msg) { $msg = $message; });
		$expected = "don't worry";
		self::assertEquals($expected, $msg);

		$rule = new StringRequireSupportRule(verify: $this->getFree(), expected: 'something');
		$msg = "don't worry";
		$rule->validate('', 'something', function ($message) use (&$msg) { $msg = $message; });
		$expected = "don't worry";
		self::assertEquals($expected, $msg);

		$rule = new IntegerRequireSupportRule(verify: $this->getFree(), expected: 1);
		$msg = "don't worry";
		$rule->validate('', 1, function ($message) use (&$msg) { $msg = $message; });
		$expected = "don't worry";
		self::assertEquals($expected, $msg);
	}

	public function testRequireSupport(): void
	{
		$rule = new BooleanRequireSupportRule(verify: $this->getFree(), expected: true);
		$msg = "don't worry";
		$rule->validate('', 'value', function ($message) use (&$msg) { $msg = $message; });
		$expected = "don't worry";
		self::assertNotEquals($expected, $msg);

		$rule = new StringRequireSupportRule(verify: $this->getFree(), expected: 'something');
		$msg = "don't worry";
		$rule->validate('', 'value', function ($message) use (&$msg) { $msg = $message; });
		$expected = "don't worry";
		self::assertNotEquals($expected, $msg);

		$rule = new IntegerRequireSupportRule(verify: $this->getFree(), expected: 1);
		$msg = "don't worry";
		$rule->validate('', 3, function ($message) use (&$msg) { $msg = $message; });
		$expected = "don't worry";
		self::assertNotEquals($expected, $msg);
	}

	public function testIsSupportNegative(): void
	{
		$rule = new BooleanRequireSupportRule(verify: $this->getSupporter(), expected: true);
		$msg = "don't worry";
		$rule->validate('', '', function ($message) use (&$msg) { $msg = $message; });
		$expected = "don't worry";
		self::assertEquals($expected, $msg);

		$rule = new StringRequireSupportRule(verify: $this->getSupporter(), expected: 'something');
		$msg = "don't worry";
		$rule->validate('', '', function ($message) use (&$msg) { $msg = $message; });
		$expected = "don't worry";
		self::assertEquals($expected, $msg);

		$rule = new IntegerRequireSupportRule(verify: $this->getSupporter(), expected: 1);
		$msg = "don't worry";
		$rule->validate('', '', function ($message) use (&$msg) { $msg = $message; });
		$expected = "don't worry";
		self::assertEquals($expected, $msg);
	}

	public function testIsSupport(): void
	{
		$rule = new BooleanRequireSupportRule(verify: $this->getSupporter(), expected: true);
		$msg = "don't worry";
		$rule->validate('', 'value', function ($message) use (&$msg) { $msg = $message; });
		$expected = "don't worry";
		self::assertEquals($expected, $msg);

		$rule = new StringRequireSupportRule(verify: $this->getSupporter(), expected: 'something');
		$msg = "don't worry";
		$rule->validate('', 'value', function ($message) use (&$msg) { $msg = $message; });
		$expected = "don't worry";
		self::assertEquals($expected, $msg);

		$rule = new IntegerRequireSupportRule(verify: $this->getSupporter(), expected: 1);
		$msg = "don't worry";
		$rule->validate('', 3, function ($message) use (&$msg) { $msg = $message; });
		$expected = "don't worry";
		self::assertEquals($expected, $msg);
	}
}