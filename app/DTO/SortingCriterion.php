<?php

namespace App\DTO;

use App\Exceptions\Internal\InvalidOrderDirectionException;

abstract class SortingCriterion extends ArrayableDTO
{
	public const ASC = 'ASC';
	public const DESC = 'DESC';

	public const COLUMN_CREATED_AT = 'created_at';
	public const COLUMN_TITLE = 'title';
	public const COLUMN_DESCRIPTION = 'description';
	public const COLUMN_IS_PUBLIC = 'is_public';

	public const COLUMNS = [
		self::COLUMN_CREATED_AT,
		self::COLUMN_TITLE,
		self::COLUMN_DESCRIPTION,
		self::COLUMN_IS_PUBLIC,
	];

	public string $column;
	public string $order;

	/**
	 * @throws InvalidOrderDirectionException
	 */
	public function __construct(string $column, string $order = self::ASC)
	{
		if ($order !== self::ASC && $order !== self::DESC) {
			throw new InvalidOrderDirectionException();
		}
		$this->column = $column;
		$this->order = $order;
	}
}
