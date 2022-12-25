<?php

namespace App\Http\Requests;

use App\Exceptions\Internal\InvalidSmartIdException;
use App\Exceptions\Internal\QueryBuilderException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\UploadedFile;

abstract class AbstractEmptyRequest extends BaseApiRequest
{
	/**
	 * Returns the validation rules that apply to the request.
	 *
	 * @return array
	 */
	public function rules(): array
	{
		return [];
	}

	/**
	 * Post-processes the validated values.
	 *
	 * @param array          $values
	 * @param UploadedFile[] $files
	 *
	 * @return void
	 *
	 * @throws ModelNotFoundException
	 * @throws InvalidSmartIdException
	 * @throws QueryBuilderException
	 */
	protected function processValidatedValues(array $values, array $files): void
	{
	}
}
