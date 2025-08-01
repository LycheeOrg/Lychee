<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Http\Resources\OpenApi;

use App\Exceptions\Internal\LycheeLogicException;
use Dedoc\Scramble\Extensions\TypeToSchemaExtension;
use Dedoc\Scramble\Support\Generator\Reference;
use Dedoc\Scramble\Support\Generator\Types\ObjectType as OpenApiObjectType;
use Dedoc\Scramble\Support\Generator\Types\Type as OpenApiType;
use Dedoc\Scramble\Support\Type\ArrayType;
use Dedoc\Scramble\Support\Type\BooleanType;
use Dedoc\Scramble\Support\Type\FloatType;
use Dedoc\Scramble\Support\Type\Generic;
use Dedoc\Scramble\Support\Type\IntegerType;
use Dedoc\Scramble\Support\Type\NullType;
use Dedoc\Scramble\Support\Type\ObjectType;
use Dedoc\Scramble\Support\Type\StringType;
use Dedoc\Scramble\Support\Type\Type;
use Dedoc\Scramble\Support\Type\Union;
use Spatie\LaravelData\Data;

class DataToResponse extends TypeToSchemaExtension
{
	/**
	 * We establish that we handle here all the Spatie\LaravelData\Data classes.
	 * This is because it is a pro feature of scramble and we do not have that kind of money.
	 */
	public function shouldHandle(Type $type): bool
	{
		return $type->isInstanceOf(Data::class);
	}

	/**
	 * @param Type $type the type being transformed to schema
	 */
	public function toSchema(Type $type): ?OpenApiType
	{
		/** @var Generic $type */
		$reflect = new \ReflectionClass($type->name);
		$props = $reflect->getProperties(\ReflectionProperty::IS_PUBLIC);

		$ret = new OpenApiObjectType();
		collect($props)->each(function ($prop) use ($ret): void {
			$to_convert_type = $this->convertReflected($prop->getType());
			$ret->addProperty($prop->name, $this->openApiTransformer->transform($to_convert_type));
		});

		return $ret;
	}

	/**
	 * Set a reference to that object in the return.
	 */
	public function reference(ObjectType $type): Reference
	{
		return new Reference('schemas', $type->name, $this->components);
	}

	/**
	 * Given a pure reflected PHP type, we return the corresponding Scramble type equivalent before Generator conversion.
	 *
	 * @throws \InvalidArgumentException
	 * @throws LycheeLogicException
	 */
	private function convertReflected(\ReflectionNamedType|\ReflectionUnionType|\ReflectionType|null $type): Type
	{
		if ($type === null) {
			// @codeCoverageIgnoreStart
			return new NullType();
			// @codeCoverageIgnoreEnd
		}

		if ($type instanceof \ReflectionUnionType) {
			return $this->handleUnionType($type);
		}

		if ($type instanceof \ReflectionIntersectionType) {
			// @codeCoverageIgnoreStart
			throw new LycheeLogicException('Intersection types are not supported.');
			// @codeCoverageIgnoreEnd
		}

		if (!$type instanceof \ReflectionNamedType) {
			// @codeCoverageIgnoreStart
			throw new LycheeLogicException('Unexpected reflection type.');
			// @codeCoverageIgnoreEnd
		}

		$name = $type->getName();
		if ($type->isBuiltin()) {
			return $this->handleBuiltin($name);
		}

		return match ($name) {
			// @codeCoverageIgnoreStart
			'Spatie\LaravelData\Data' => throw new LycheeLogicException('Spatie\LaravelData\Data should not be used as return type.'),
			// @codeCoverageIgnoreEnd
			'Illuminate\Support\Collection' => new ArrayType(), // refactor me later.
			default => new ObjectType($name),
		};
	}

	private function handleUnionType(\ReflectionUnionType $union): Type
	{
		$types = collect($union->getTypes())->map(fn ($type) => $this->convertReflected($type))->all();
		$union_type = new Union($types);

		return $union_type;
	}

	private function handleBuiltin(string $type): Type
	{
		return match ($type) {
			'null' => new NullType(),
			'int' => new IntegerType(),
			'float' => new FloatType(),
			'bool' => new BooleanType(),
			'array' => new ArrayType(),
			'string' => new StringType(),
			default => throw new LycheeLogicException('Unknown type: ' . $type),
		};
	}
}