<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Http\Resources\Statistics;

use App\Models\Configs;
use Illuminate\Support\Collection;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript()]
class CountsData extends Data
{
	/** @var DayCount[] */
	public array $data;
	public int $low_number_of_shoots_per_day;
	public int $medium_number_of_shoots_per_day;
	public int $high_number_of_shoots_per_day;
	public string $min_created_at;
	public string $min_taken_at;

	/**
	 * @param Collection<int,object{date:string,uploads:int}> $data
	 *
	 * @return void
	 */
	public function __construct(Collection $data, string $min_taken_at, string $min_created_at)
	{
		$this->data = $data->map(fn ($v) => new DayCount($v->date, $v->uploads))->all();
		$this->low_number_of_shoots_per_day = Configs::getValueAsInt('low_number_of_shoots_per_day');
		$this->medium_number_of_shoots_per_day = Configs::getValueAsInt('medium_number_of_shoots_per_day');
		$this->high_number_of_shoots_per_day = Configs::getValueAsInt('high_number_of_shoots_per_day');
		$this->min_created_at = substr($min_created_at, 0, 4); // we only need the year
		$this->min_taken_at = substr($min_taken_at, 0, 4); // we only need the year
	}
}
