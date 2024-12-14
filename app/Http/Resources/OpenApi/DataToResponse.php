<?php

namespace App\Http\Resources\OpenApi;

use App\Exceptions\Internal\LycheeLogicException;
use Dedoc\Scramble\Extensions\TypeToSchemaExtension;
use Dedoc\Scramble\Support\Generator\Combined\AnyOf;
use Dedoc\Scramble\Support\Generator\Types\ArrayType;
use Dedoc\Scramble\Support\Generator\Types\BooleanType;
use Dedoc\Scramble\Support\Generator\Types\IntegerType;
use Dedoc\Scramble\Support\Generator\Types\NullType;
use Dedoc\Scramble\Support\Generator\Types\NumberType;
use Dedoc\Scramble\Support\Generator\Types\ObjectType;
use Dedoc\Scramble\Support\Generator\Types\StringType;
use Dedoc\Scramble\Support\Generator\Types\Type as OpenApiType;
use Dedoc\Scramble\Support\Type\Type;
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
		$classDef = $this->infer->analyzeClass($type->name);

		return $this->toObjectType($classDef->name);
	}

	/**
	 * Given a type we.
	 *
	 * @param \ReflectionNamedType|\ReflectionUnionType|\ReflectionIntersectionType|null $type
	 *
	 * @return OpenApiType
	 *
	 * @throws \InvalidArgumentException
	 * @throws LycheeLogicException
	 */
	private function convertReflected(\ReflectionNamedType|\ReflectionUnionType|\ReflectionIntersectionType|null $type): OpenApiType
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

		if ($type->isBuiltin()) {
			return $this->handleBuiltin($type->getName());
		}

		return $this->toObjectType($type->getName());
	}

	private function handleUnionType(\ReflectionUnionType $union): OpenApiType
	{
		$anyOf = new AnyOf();
		$types = collect($union->getTypes())->map(fn ($type) => $this->convertReflected($type))->all();
		$anyOf->setItems($types);

		return $anyOf;
	}

	private function handleBuiltin(string $type): OpenApiType
	{
		return match ($type) {
			'null' => new NullType(),
			'int' => new IntegerType(),
			'float' => new NumberType(),
			'bool' => new BooleanType(),
			'array' => new ArrayType(),
			'string' => new StringType(),
			default => throw new LycheeLogicException('Unknown type: ' . $type),
		};
	}

	/** @phpstan-ignore-next-line */
	private function handleBackedEnum(\ReflectionClass $enum): OpenApiType
	{
		$ret = new StringType();
		$types = collect($enum->getConstants())->map(fn ($type) => $type->value)->all();
		$ret->enum($types);

		return $ret;
	}

	private function toObjectType(string $name): OpenApiType
	{
		/** @phpstan-ignore-next-line */
		$reflect = new \ReflectionClass($name);
		if ($reflect->implementsInterface(\BackedEnum::class)) {
			return $this->handleBackedEnum($reflect);
		}

		$props = $reflect->getProperties(\ReflectionProperty::IS_PUBLIC);
		if ($props === []) {
			if ($reflect->name === 'Spatie\LaravelData\Data') {
				throw new LycheeLogicException('Spatie\LaravelData\Data should not be used as return type.');
			}
			if ($reflect->name === 'Illuminate\Support\Collection') {
				// Refactor me later.
				return new ArrayType();
			}
		}

		$ret = new ObjectType();
		/** @phpstan-ignore-next-line */
		collect($props)->each(fn ($prop) => $ret->addProperty($prop->name, $this->convertReflected($prop->getType())));

		return $ret;
	}
}