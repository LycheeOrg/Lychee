<?php

namespace App\Assets;

use App\DTO\ArrayableDTO;
use Dedoc\Scramble\Extensions\TypeToSchemaExtension;
use Dedoc\Scramble\Support\Type\ArrayItemType_;
use Dedoc\Scramble\Support\Type\ArrayType;
use Dedoc\Scramble\Support\Type\ObjectType;
use Dedoc\Scramble\Support\Type\Type;

class DTOResponseTypeScrambleExtension extends TypeToSchemaExtension
{
	public function shouldHandle(Type $type)
	{
		return $type instanceof ObjectType &&
			$type->isInstanceOf(ArrayableDTO::class);
	}

	public function toSchema(Type $type)
	{
		if ($type->isInstanceOf(ArrayableDTO::class)) {
			$type = $this->infer->analyzeClass($type->name);
			$array = new ArrayType([]);
			foreach ($type->methods['__construct']->arguments as $key => $value) {
				$array->items[] = new ArrayItemType_($key, $value);
			}

			return $this->openApiTransformer->transform($array);
		}
		$type = $this->infer->analyzeClass($type->name);
		$array = $type->getMethodCallType('toArray');

		return $this->openApiTransformer->transform($array);
	}
}