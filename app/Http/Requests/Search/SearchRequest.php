<?php

namespace App\Http\Requests\Search;

use App\Http\Requests\BaseApiRequest;
use App\Models\Configs;
use Illuminate\Support\Facades\Auth;

class SearchRequest extends BaseApiRequest
{
	public const TERM_ATTRIBUTE = 'term';

	/**
	 * @var string[]
	 */
	protected array $terms = [];

	/**
	 * {@inheritDoc}
	 */
	public function authorize(): bool
	{
		return Auth::check() || Configs::getValueAsBool('public_search');
	}

	/**
	 * {@inheritDoc}
	 */
	public function rules(): array
	{
		return [self::TERM_ATTRIBUTE => 'required|string'];
	}

	/**
	 * {@inheritDoc}
	 */
	protected function processValidatedValues(array $values, array $files): void
	{
		// Escape special characters for a LIKE query
		$this->terms = explode(' ', str_replace(
			['\\', '%', '_'],
			['\\\\', '\\%', '\\_'],
			$values[self::TERM_ATTRIBUTE]
		));
	}

	/**
	 * @return string[]
	 */
	public function terms(): array
	{
		return $this->terms;
	}
}
