<?php

declare(strict_types=1);

namespace App\Livewire\Forms;

use App\Contracts\Http\Requests\RequestAttribute;
use App\Enum\LicenseType;
use App\Http\RuleSets\Photo\SetPhotoDescriptionRuleSet;
use App\Models\Photo;
use App\Rules\TitleRule;
use Illuminate\Support\Facades\Validator as ValidatorFacade;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Validation\Validator;
use Livewire\Attributes\Locked;
use Livewire\Drawer\Utils;

class PhotoUpdateForm
{
	private ?Photo $photo = null;
	/** @var string[] */
	#[Locked] public array $tags = [];
	#[Locked] public string $date = '';
	public function __construct(
		public string $photoID,
		public string $title,
		public string $description,
		public string $tagsWithComma,
		public string $uploadDate,
		public string $uploadTz,
		public string $license,
	) {
		$this->tags = collect(explode(',', $this->tagsWithComma))->map(fn ($v) => trim($v))->filter(fn ($v) => $v !== '')->all();
		$this->date = $this->uploadDate . ':' . $this->uploadTz;
	}

	/**
	 * This allows Livewire to know which values of the $configs we
	 * want to display in the wire:model. Sort of a white listing.
	 *
	 * @return array<string,mixed>
	 */
	protected function rules(): array
	{
		return [
			RequestAttribute::TITLE_ATTRIBUTE => ['required', new TitleRule()],
			...SetPhotoDescriptionRuleSet::rules(),
			RequestAttribute::TAGS_ATTRIBUTE => 'present|array',
			RequestAttribute::TAGS_ATTRIBUTE . '.*' => 'required|string|min:1',
			RequestAttribute::LICENSE_ATTRIBUTE => ['required', new Enum(LicenseType::class)],
			RequestAttribute::DATE_ATTRIBUTE => 'required|date',
		];
	}

	/**
	 * Validate form.
	 *
	 * @return array<int,array<string>>
	 */
	public function validate(): array
	{
		/** @var Validator $validator */
		$validator = ValidatorFacade::make($this->all(), $this->rules());

		if ($validator->fails()) {
			return $validator->getMessageBag()->messages();
		}

		return [];
	}

	/**
	 * Fetch photo associated with request.
	 *
	 * @return Photo
	 */
	public function getPhoto(): Photo
	{
		$this->photo = Photo::query()->findOrFail($this->photoID);

		return $this->photo;
	}

	/**
	 * Save data in photo.
	 *
	 * @return void
	 */
	public function save(): void
	{
		$photo = Photo::query()->where('id', '=', $this->photoID)->first();

		$photo->title = $this->title;
		$photo->description = $this->description;
		$photo->created_at = $this->date;
		$photo->tags = $this->tags;
		$photo->license = LicenseType::from($this->license);
		$photo->save();
	}

	/**
	 * @return array<string,mixed>
	 */
	public function all(): array
	{
		return Utils::getPublicProperties($this);
	}
}
