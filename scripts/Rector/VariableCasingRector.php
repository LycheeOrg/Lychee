<?php
declare(strict_types=1);

namespace Scripts\Rector;

use PhpParser\Node;
use PhpParser\Node\Expr\Variable;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

final class VariableCasingRector extends AbstractRector
{
	public function getRuleDefinition(): RuleDefinition
	{
		return new RuleDefinition(
			'Converts variable names to camelCase, excluding fully capitalized ones',
			[
				new CodeSample(
					<<<'CODE_SAMPLE'
						$my_variable = 10;
						$API_KEY = 'secret';
					CODE_SAMPLE,
					<<<'CODE_SAMPLE'
						$myVariable = 10;
						$API_KEY = 'secret';
					CODE_SAMPLE
				),
			]
		);
	}

	public function getNodeTypes(): array
	{
		return [Variable::class];
	}

	public function refactor(Node $node)
	{
		if (!$node instanceof Variable || !is_string($node->name)) {
			return null;
		}

		// Skip variables that are fully capitalized (likely constants)
		if (strtoupper($node->name) === $node->name) {
			return null;
		}

		$camelCasedName = $this->toCamelCase($node->name);

		// Skip if it's already in the correct format
		if ($camelCasedName === $node->name) {
			return null;
		}

		// Rename the variable
		$node->name = $camelCasedName;

		return $node;
	}

	private function toCamelCase(string $name): string
	{
		return lcfirst(str_replace(' ', '', ucwords(str_replace('_', ' ', $name))));
	}
}