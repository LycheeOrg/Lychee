<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

/**
 * ArrayToTextTable.
 *
 * Display arrays in terminal
 *
 * @author      Mathieu Viossat <mathieu@viossat.fr>
 * @copyright   Copyright (c) 2015 Mathieu Viossat
 * @license     http://opensource.org/licenses/MIT
 *
 * @see        https://github.com/MathieuViossat/arraytotexttable
 */

namespace App\Assets;

use App\Contracts\Laminas\DecoratorInterface;
use App\Metadata\Laminas\Unicode;
use Safe\Exceptions\MbstringException;
use Safe\Exceptions\PcreException;
use function Safe\mb_internal_encoding;
use function Safe\preg_match_all;

class ArrayToTextTable
{
	public const ALIGNLEFT = STR_PAD_RIGHT;
	public const ALIGNCENTER = STR_PAD_BOTH;
	public const ALIGNRIGHT = STR_PAD_LEFT;

	/** @var array<int,array<string>> */
	protected array $data;
	/** @var array<int,string> */
	protected array $keys;
	/** @var array<string,int> */
	protected array $widths;
	protected DecoratorInterface $decorator;
	protected string $indentation;
	protected bool|string $displayKeys;
	/** @var array<int,string> */
	protected array $ignoredKeys;
	protected bool $upperKeys;
	protected int $keysAlignment;
	protected int $valuesAlignment;
	protected ?\Closure $formatter;

	/**
	 * Create a table.
	 *
	 * @param array<int,object|array<string|null>> $rawData
	 *
	 * @return void
	 */
	public function __construct(array $rawData = [])
	{
		$this->setData($rawData)
			->setDecorator(new Unicode())
			->setIgnoredKeys([])
			->setIndentation('')
			->setDisplayKeys('auto')
			->setUpperKeys(true)
			->setKeysAlignment(self::ALIGNCENTER)
			->setValuesAlignment(self::ALIGNLEFT)
			->setFormatter(null);
	}

	public function __toString(): string
	{
		return $this->getTable();
	}

	/**
	 * return the table.
	 *
	 * @param array<int,array<string|int>|object>|null $rawData
	 *
	 * @return string
	 *
	 * @throws PcreException
	 * @throws MbstringException
	 */
	public function getTable(?array $rawData = null): string
	{
		if (!is_null($rawData)) {
			$this->setData($rawData);
		}

		$data = $this->prepare();
		$i = $this->indentation;
		$d = $this->decorator;

		$displayKeys = $this->displayKeys;
		if ($displayKeys === 'auto') {
			$displayKeys = false;
			foreach ($this->keys as $key) {
				if (!is_int($key)) {
					$displayKeys = true;
					break;
				}
			}
		}

		$table = $i . $this->line($d->getTopLeft(), $d->getHorizontal(), $d->getHorizontalDown(), $d->getTopRight()) . PHP_EOL;

		if ($displayKeys === true || $displayKeys === 'auto') {
			$keysRow = array_combine($this->keys, $this->keys);
			if ($this->upperKeys) {
				$keysRow = array_map('mb_strtoupper', $keysRow);
			}
			$table .= $i . implode(PHP_EOL, $this->row($keysRow, $this->keysAlignment)) . PHP_EOL;

			$table .= $i . $this->line($d->getVerticalRight(), $d->getHorizontal(), $d->getCross(), $d->getVerticalLeft()) . PHP_EOL;
		}

		foreach ($data as $row) {
			$table .= $i . implode(PHP_EOL, $this->row($row, $this->valuesAlignment)) . PHP_EOL;
		}

		$table .= $i . $this->line($d->getBottomLeft(), $d->getHorizontal(), $d->getHorizontalUp(), $d->getBottomRight()) . PHP_EOL;

		return $table;
	}

	/**
	 * @param array<int,array<string|int|null>|object>|null $data
	 *
	 * @return self
	 */
	public function setData(array|null $data): self
	{
		if (!is_array($data)) {
			$data = [];
		}

		$arrayData = [];
		foreach ($data as $row) {
			if (is_array($row)) {
				$arrayData[] = $row;
			} elseif (is_object($row)) {
				$arrayData[] = get_object_vars($row);
			}
		}

		$this->data = $arrayData;

		return $this;
	}

	public function setDecorator(DecoratorInterface $decorator): self
	{
		$this->decorator = $decorator;

		return $this;
	}

	public function setIndentation(string $indentation): self
	{
		$this->indentation = $indentation;

		return $this;
	}

	public function setDisplayKeys(string|bool $displayKeys): self
	{
		$this->displayKeys = $displayKeys;

		return $this;
	}

	public function setUpperKeys(bool $upperKeys): self
	{
		$this->upperKeys = $upperKeys;

		return $this;
	}

	public function setKeysAlignment(int $keysAlignment): self
	{
		$this->keysAlignment = $keysAlignment;

		return $this;
	}

	public function setValuesAlignment(int $valuesAlignment): self
	{
		$this->valuesAlignment = $valuesAlignment;

		return $this;
	}

	public function setFormatter(?\Closure $formatter): self
	{
		$this->formatter = $formatter;

		return $this;
	}

	/**
	 * @param array<int,string> $ignoredKeys
	 *
	 * @return ArrayToTextTable
	 */
	public function setIgnoredKeys(array $ignoredKeys): self
	{
		$this->ignoredKeys = $ignoredKeys;

		return $this;
	}

	protected function line(string $left, string $horizontal, string $link, string $right): string
	{
		$line = $left;
		foreach ($this->keys as $key) {
			if (!in_array($key, $this->ignoredKeys, true)) {
				$line .= str_repeat($horizontal, $this->widths[$key] + 2) . $link;
			}
		}

		if (mb_strlen($line) > mb_strlen($left)) {
			$line = mb_substr($line, 0, -mb_strlen($horizontal));
		}

		return $line . $right;
	}

	/**
	 * @param array<string|null> $row
	 * @param int                $alignment
	 *
	 * @return array<string>
	 *
	 * @throws MbstringException
	 * @throws PcreException
	 */
	protected function row(array $row, int $alignment): array
	{
		$data = [];
		$height = 1;
		foreach ($this->keys as $key) {
			$data[$key] = isset($row[$key]) ? static::valueToLines($row[$key]) : [''];
			$height = max($height, count($data[$key]));
		}

		$rowLines = [];
		for ($i = 0; $i < $height; $i++) {
			$rowLine = [];
			foreach ($data as $key => $value) {
				$rowLine[$key] = isset($value[$i]) ? $value[$i] : '';
			}
			$rowLines[] = $this->rowLine($rowLine, $alignment);
		}

		return $rowLines;
	}

	/**
	 * @param array<string,string> $row
	 * @param int                  $alignment
	 *
	 * @return string
	 *
	 * @throws MbstringException
	 * @throws PcreException
	 */
	protected function rowLine(array $row, int $alignment): string
	{
		$line = $this->decorator->getVertical();

		foreach ($row as $key => $value) {
			if (!in_array($key, $this->ignoredKeys, true)) {
				$line .= ' ' . static::mb_str_pad($value, $this->widths[$key], ' ', $alignment) . ' ' . $this->decorator->getVertical();
			}
		}

		if (count($row) === 0) {
			$line .= $this->decorator->getVertical();
		}

		return $line;
	}

	/**
	 * @return array<int,array<string|null>>
	 *
	 * @throws PcreException
	 */
	protected function prepare(): array
	{
		$this->keys = [];
		$this->widths = [];

		$data = $this->data;

		if ($this->formatter instanceof \Closure) {
			foreach ($data as &$row) {
				array_walk($row, $this->formatter, $this);
			}
			unset($row);
		}

		foreach ($data as $row) {
			$this->keys = array_merge($this->keys, array_keys($row));
		}
		$this->keys = array_unique($this->keys);

		foreach ($this->keys as $key) {
			$this->setWidth($key, $key);
		}

		foreach ($data as $row) {
			foreach ($row as $columnKey => $columnValue) {
				$this->setWidth($columnKey, $columnValue);
			}
		}

		return $data;
	}

	protected static function countCJK(string $string): int
	{
		return preg_match_all('/[\p{Han}\p{Katakana}\p{Hiragana}\p{Hangul}]/u', $string);
	}

	protected function setWidth(string $key, ?string $value): void
	{
		if (!isset($this->widths[$key])) {
			$this->widths[$key] = 0;
		}

		foreach (static::valueToLines($value) as $line) {
			$width = mb_strlen($line) + self::countCJK($line);
			if ($width > $this->widths[$key]) {
				$this->widths[$key] = $width;
			}
		}
	}

	/**
	 * @param string|null $value
	 *
	 * @return array<int,string>
	 */
	protected static function valueToLines(?string $value): array
	{
		return explode("\n", $value);
	}

	protected static function mb_str_pad(
		string $input,
		int $pad_length,
		string $pad_string = ' ',
		int $pad_type = STR_PAD_RIGHT,
		string|null $encoding = null,
	): string {
		/** @var string $encoding */
		$encoding = $encoding === null ? mb_internal_encoding() : $encoding;
		$pad_before = $pad_type === STR_PAD_BOTH || $pad_type === STR_PAD_LEFT;
		$pad_after = $pad_type === STR_PAD_BOTH || $pad_type === STR_PAD_RIGHT;
		$pad_length -= mb_strlen($input, $encoding) + self::countCJK($input);
		$target_length = $pad_before && $pad_after ? $pad_length / 2 : $pad_length;

		$repeat_times = (int) ceil($target_length / mb_strlen($pad_string, $encoding));
		$repeated_string = str_repeat($pad_string, max(0, $repeat_times));
		$before = $pad_before ? mb_substr($repeated_string, 0, (int) floor($target_length), $encoding) : '';
		$after = $pad_after ? mb_substr($repeated_string, 0, (int) ceil($target_length), $encoding) : '';

		return $before . $input . $after;
	}
}
