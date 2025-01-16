<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\DTO;

use Illuminate\Support\Str;

/**
 * @extends AbstractDTO<string>
 */
class BacktraceRecord extends AbstractDTO
{
	public const UNKNOWN_PLACEHOLDER = '<unknown>';
	public const NAMESPACE_SEPARATOR = '::';

	protected string $basePath;
	protected string $file;
	protected int $line;
	protected string $class;
	protected string $function;

	/**
	 * Constructor.
	 *
	 * @param string $file     the filename
	 * @param int    $line     the line (0 indicates "unknown")
	 * @param string $class    the class name
	 * @param string $function the function name
	 */
	public function __construct(string $file = '', int $line = 0, string $class = '', string $function = '')
	{
		$this->basePath = base_path();
		$this->file = $file;
		$this->line = $line;
		$this->class = $class;
		$this->function = $function;
	}

	/**
	 * Gets the file name.
	 *
	 * @return string the file name
	 */
	public function getFile(): string
	{
		return $this->file;
	}

	/**
	 * Gets the beautified file name.
	 *
	 * Beautification means two things:
	 *
	 *  - The base path is stripped off from the prefix of the file name.
	 *    The installation directory depends on the setup and does not provide
	 *    any helpful information.
	 *    Moreover, the log limits this attribute to 100 characters.
	 *  - An empty file name (this may happen for low-level function inside
	 *    the PHP engine) is replaced by the special string
	 *    {@link self::UNKNOWN_PLACEHOLDER} to avoid the wrong impression that
	 *    logging might have failed, if the value was empty.
	 *
	 * @return string the beautified file name
	 */
	public function getFileBeautified(): string
	{
		return $this->file !== '' ?
			Str::replaceFirst($this->basePath, '', $this->file) :
			self::UNKNOWN_PLACEHOLDER;
	}

	/**
	 * Gets the line number.
	 *
	 * @return int the line number
	 */
	public function getLine(): int
	{
		return $this->line;
	}

	/**
	 * Gets the class name.
	 *
	 * @return string the class name
	 */
	public function getClass(): string
	{
		return $this->class;
	}

	/**
	 * Gets the function name.
	 *
	 * Note: In PHP terminology, the function name is the bare name of
	 * function without any namespace indication.
	 * Hence, the function name does not indicate if it is a global function
	 * or a method inside a class.
	 * For most practical use cases, you want to use
	 * {@link BacktraceRecord::getMethodBeautified()}.
	 *
	 * @return string the function name
	 */
	public function getFunction(): string
	{
		return $this->function;
	}

	/**
	 * Gets the beautified function name.
	 *
	 * See {@link BacktraceRecord::getFunction} for a definition of the term
	 * "function".
	 *
	 * Beautification means that an empty function name is replaced by the
	 * special string {@link self::UNKNOWN_PLACEHOLDER} to avoid the wrong
	 * impression that logging might have failed, if the value was empty.
	 *
	 * The function name is empty (or unknown), if the error has occurred
	 * in the global namespace, i.e. in a top level script.
	 *
	 * @return string the function name
	 */
	public function getFunctionBeautified(): string
	{
		return $this->function !== '' ? $this->function : self::UNKNOWN_PLACEHOLDER;
	}

	/**
	 * Gets the beautified method name.
	 *
	 * Note: In PHP terminology, the method name includes a namespace
	 * indication.
	 *
	 * The return value can have one of the following three patterns:
	 *
	 *  - `'::<unknown>'`, outside any function in the global name space
	 *  - `'::foo'`, global method `foo`
	 *  - `'bar::foo'`, method `foo` of class or trait `bar`
	 *
	 * Note that the fourth option `bar::<unknown>` is impossible, because
	 * no code can exist inside a class, but outside a method.
	 *
	 * @return string
	 */
	public function getMethodBeautified(): string
	{
		return $this->class . self::NAMESPACE_SEPARATOR . $this->getFunctionBeautified();
	}

	/**
	 * {@inheritDoc}
	 */
	public function toArray(): array
	{
		return [
			'file' => $this->getFileBeautified(),
			'line' => $this->line,
			'method' => $this->getMethodBeautified(),
		];
	}
}
