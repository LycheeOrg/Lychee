<?php

namespace App\LDAP;

/**
 * Class FixedArray.
 *
 * This class contain defines an array with fixed entries which can be
 * accessed either as array members or as object attributes.
 */
class FixedArray implements \ArrayAccess, \Iterator, \Countable
{
	private $_valid_keys = [];
	private $_data = [];
	private $_pos = 0;

	/**
	 * Basic constructor.
	 *
	 * @param array $attribute_names
	 *
	 * @return void
	 */
	public function __construct(array $attributes)
	{
		$this->_valid_keys = $attributes;
		foreach ($attributes as $varname) {
			$this->offsetSet($varname, null);
		}
		$this->_pos = 0;
	}

	public function offsetSet(mixed $prop, mixed $value): void
	{
		if (!in_array($prop, $this->_valid_keys)) {
			throw new \ErrorException('Attribute "' . $prop . '" is unknown');
		}
		$this->_data[$prop] = $value;
	}

	public function offsetExists(mixed $prop): bool
	{
		return in_array($prop, $this->_valid_keys);
	}

	public function offsetGet(mixed $prop): mixed
	{
		if (!in_array($prop, $this->_valid_keys)) {
			throw new \ErrorException('Attribute "' . $prop . '" is unknown');
		} else {
			return $this->_data[$prop];
		}
	}

	public function offsetUnset(mixed $prop): void
	{
		if (!in_array($prop, $this->_valid_keys)) {
			throw new \ErrorException('Attribute "' . $prop . '" is unknown');
		} else {
			$this->_data[$prop] = null;
		}
	}

	public function count(): int
	{
		return count($this->_valid_keys);
	}

	public function count_set(): int
	{
		$ret = 0;
		foreach ($this->_valid_keys as $prop) {
			if (!is_null($this->offsetGet($prop))) {
				$ret++;
			}
		}

		return $ret;
	}

	public function rewind()
	{
		$this->_pos = 0;
	}

	public function current()
	{
		return $this->_data[$this->_valid_keys[$this->_pos]];
	}

	public function key()
	{
		return $this->_valid_keys[$this->_pos];
	}

	public function next()
	{
		$this->_pos++;
	}

	public function valid()
	{
		return isset($this->_valid_keys[$this->_pos]);
	}

	public function get_properties(): array
	{
		return $this->_valid_keys;
	}

	public function __get($prop)
	{
		return $this->offsetGet($prop);
	}

	public function property_exists($prop): bool
	{
		return $this->offsetExists($prop);
	}

	public function __set($prop, $value)
	{
		if (!in_array($prop, $this->_valid_keys)) {
			throw new \ErrorException('Attribute "' . $prop . '" is unknown');
		} else {
			$this->_data[$prop] = $value;
		}
	}

	public function __isset(string $prop): bool
	{
		if (!in_array($prop, $this->_valid_keys)) {
			return false;
		}

		return $this->_data[$prop] != null;
	}

	/**
	 * Convert object to Array.
	 *
	 * @return array
	 */
	public function toArray(): array
	{
		$ret = [];
		foreach ($this->_valid_keys as $prop) {
			if (!is_null($this->offsetGet($prop))) {
				$ret[$prop] = $this->offsetGet($prop);
			}
		}

		return $ret;
	}

	/**
	 * Convert object to Array.
	 *
	 * @return array
	 */
	public function fromArray(array $data): void
	{
		foreach ($data as $prop => $value) {
			if (!in_array($prop, $this->_valid_keys)) {
				throw new \ErrorException('Attribute "' . $prop . '" is unknown');
			}
		}
		foreach ($data as $prop => $value) {
			if (in_array($prop, $this->_valid_keys)) {
				$this->offsetSet($prop, $value);
			}
		}
	}
}
