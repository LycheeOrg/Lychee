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
	 *
	 * @param Type $type
	 *
	 * @return bool
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
		/** @phpstan-ignore-next-line */
		$reflect = new \ReflectionClass($type->name);
		$props = $reflect->getProperties(\ReflectionProperty::IS_PUBLIC);

		$ret = new OpenApiObjectType();
		collect($props)->each(function ($prop) use ($ret) {
			$toConvertType = $this->convertReflected($prop->getType());
			$ret->addProperty($prop->name, $this->openApiTransformer->transform($toConvertType));
		});

		return $ret;
	}

	/**
	 * Set a reference to that object in the return.
	 *
	 * @param ObjectType $type
	 *
	 * @return Reference
	 */
	public function reference(ObjectType $type): Reference
	{
		return new Reference('schemas', $type->name, $this->components);
	}

	/**
	 * Given a pure reflected PHP type, we return the corresponding Scramble type equivalent before Generator conversion.
	 *
	 * @return Type
	 *
	 * @throws \InvalidArgumentException
	 * @throws LycheeLogicException
	 */
	private function convertReflected(\ReflectionNamedType|\ReflectionUnionType|\ReflectionType|null $type): Type
	{
		if ($type === null) {
			return new NullType();
		}

		if ($type instanceof \ReflectionUnionType) {
			return $this->handleUnionType($type);
		}

		if ($type instanceof \ReflectionIntersectionType) {
			throw new LycheeLogicException('Intersection types are not supported.');
		}

		if (!$type instanceof \ReflectionNamedType) {
			throw new LycheeLogicException('Unexpected reflection type.');
		}

		$name = $type->getName();
		if ($type->isBuiltin()) {
			return $this->handleBuiltin($name);
		}

		return match ($name) {
			'Spatie\LaravelData\Data' => throw new LycheeLogicException('Spatie\LaravelData\Data should not be used as return type.'),
			'Illuminate\Support\Collection' => new ArrayType(), // refactor me later.
			default => new ObjectType($name),
		};
	}

	private function handleUnionType(\ReflectionUnionType $union): Type
	{
		$types = collect($union->getTypes())->map(fn ($type) => $this->convertReflected($type))->all();
		$unionType = new Union($types);

		return $unionType;
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