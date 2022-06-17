<?php

declare(strict_types=1);

namespace PHPStan\Rules\DisallowedConstructs;

use PhpParser\Node;
use PhpParser\Node\Expr\BinaryOp;
use PhpParser\Node\Expr\BinaryOp\Equal;
use PhpParser\Node\Expr\BinaryOp\NotEqual;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * Rule which forbids loose comparison via `==` or `!=`.
 *
 * This class is only required locally until the next release of
 * phpstan-strict-rules (probably 1.2.4) has been released.
 * This class has already been merged into upstream (see
 * https://github.com/phpstan/phpstan-strict-rules/pull/177).
 *
 * @implements Rule<BinaryOp>
 */
class DisallowedLooseComparisonRule implements Rule
{
	public function getNodeType(): string
	{
		return BinaryOp::class;
	}

	public function processNode(Node $node, Scope $scope): array
	{
		if ($node instanceof Equal) {
			return [
				RuleErrorBuilder::message(
					'Loose comparison via "==" is not allowed.'
				)->tip('Use strict comparison via "===" instead.')->build(),
			];
		}
		if ($node instanceof NotEqual) {
			return [
				RuleErrorBuilder::message(
					'Loose comparison via "!=" is not allowed.'
				)->tip('Use strict comparison via "!==" instead.')->build(),
			];
		}

		return [];
	}
}
