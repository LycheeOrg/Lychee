<?php

declare(strict_types=1);

namespace App\Http\Requests\Sharing;

use App\Contracts\Models\AbstractAlbum;
use App\Http\Requests\BaseApiRequest;
use App\Policies\AlbumPolicy;
use App\Rules\IntegerIDRule;
use Illuminate\Support\Facades\Gate;

class DeleteSharingRequest extends BaseApiRequest
{
	public const SHARE_IDS_ATTRIBUTE = 'shareIDs';

	/**
	 * @var array<int>
	 */
	protected array $shareIDs = [];

	/**
	 * {@inheritDoc}
	 */
	public function authorize(): bool
	{
		return Gate::check(AlbumPolicy::CAN_SHARE_ID, [AbstractAlbum::class, $this->shareIDs]);
	}

	/**
	 * {@inheritDoc}
	 */
	public function rules(): array
	{
		return [
			self::SHARE_IDS_ATTRIBUTE => 'required|array|min:1',
			self::SHARE_IDS_ATTRIBUTE . '.*' => ['required', new IntegerIDRule(false)],
		];
	}

	/**
	 * {@inheritDoc}
	 */
	protected function processValidatedValues(array $values, array $files): void
	{
		$this->shareIDs = $values[self::SHARE_IDS_ATTRIBUTE];
	}

	/**
	 * @return array<int>
	 */
	public function shareIDs(): array
	{
		return $this->shareIDs;
	}
}
