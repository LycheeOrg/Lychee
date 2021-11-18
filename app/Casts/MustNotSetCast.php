<?php

namespace App\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsInboundAttributes;
use Illuminate\Database\Eloquent\Model;

/**
 * Class MustNotSetCast.
 *
 * This cast prevents attributes from being set accidentally.
 * If there is an attempt to set the affected attribute, an exception is
 * thrown whose error message optionally indicates an alternative attribute
 * which might be set instead.
 */
class MustNotSetCast implements CastsInboundAttributes
{
	/** @var string|null the name of an alternative attribute */
	private ?string $alternative;

	/**
	 * MustNotSetCast constructor.
	 *
	 * @param string|null $alternative the name of an optional alternative for
	 *                                 the attribute which must not be set;
	 *                                 the name of the alternative is included
	 *                                 into the exception message
	 */
	public function __construct(?string $alternative = null)
	{
		$this->alternative = $alternative;
	}

	/**
	 * The mutator of the attribute.
	 *
	 * This function is called by the framework during an attempt to set the
	 * affected attribute.
	 * This mutator always throws an exception and thus prevents the attribute
	 * from being altered.
	 *
	 * @param Model  $model      the model which owns the attribute
	 * @param string $key        the name of attribute which has been
	 *                           attempted to be set
	 * @param mixed  $value      the value which has been attempted to assign
	 *                           to the attribute
	 * @param array  $attributes all attributes of the model
	 *
	 * @return void
	 */
	public function set($model, string $key, $value, array $attributes): void
	{
		$msg = 'must not set read-only attribute \'' . get_class($model) . '::$' . $key . '\' directly';
		if ($this->alternative) {
			$msg = $msg . ', use \'' . get_class($model) . '::$' . $this->alternative . ' instead';
		}
		throw new \BadMethodCallException($msg);
	}
}
