<?php

declare(strict_types=1);

namespace PHPStan\Rules\DisallowedConstructs;

use PhpParser\Node;
use PhpParser\Node\Expr\Cast\Bool_;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * @implements Rule<Bool_>
 */
class DisallowedBooleanCastRule implements Rule
{
	public function getNodeType(): string
	{
		return Bool_::class;
	}

	public function processNode(Node $node, Scope $scope): array
	{
		return [
			RuleErrorBuilder::message('Cast to bool is forbidden.')->build(),
		];
	}
}
