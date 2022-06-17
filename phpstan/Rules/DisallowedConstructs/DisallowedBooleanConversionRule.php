<?php

declare(strict_types=1);

namespace PHPStan\Rules\DisallowedConstructs;

use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * @implements Rule<FuncCall>
 */
class DisallowedBooleanConversionRule implements Rule
{
	public function getNodeType(): string
	{
		return FuncCall::class;
	}

	public function processNode(Node $node, Scope $scope): array
	{
		if (
			$node->name->parts !== null &&
			count($node->name->parts) === 1 && (
				$node->name->parts[0] === 'boolval' ||
				$node->name->parts[0] === '\boolval'
			)
		) {
			return [
				RuleErrorBuilder::message(
					'Conversion to boolean is forbidden.'
				)->build(),
			];
		} else {
			return [];
		}
	}
}
