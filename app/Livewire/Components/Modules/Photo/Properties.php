<?php

namespace App\Livewire\Components\Modules\Photo;

use App\Contracts\Http\Requests\RequestAttribute;
use App\Enum\LicenseType;
use App\Exceptions\Internal\IllegalOrderOfOperationException;
use App\Http\RuleSets\Photo\SetPhotoDescriptionRuleSet;
use App\Livewire\Traits\Notify;
use App\Livewire\Traits\UseValidator;
use App\Models\Photo as PhotoModel;
use App\Policies\PhotoPolicy;
use App\Rules\TitleRule;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Validation\Rules\Enum;
use Illuminate\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;

/**
 * This is the side bar in the case of Photo.
 *
 * Contrary to the JS implementation, the attributes are directly embeded in the bar.
 * This will (hopefully) simplify the update when editing properties.
 */
class Properties extends Component
{
	use Notify;
	use UseValidator;

	#[Locked] public string $photoID;
	/** @var array<int,string> */
	#[Locked] public array $tags;
	#[Locked] public string $sec_tz;
	#[Locked] public string $date;
	public string $title; // ! wired
	public string $created_at; // ! wired
	public string $description; // ! wired
	public string $tags_with_comma; // ! wired
	public string $license; // ! wired

	/**
	 * Given a photo model extract all the information.
	 *
	 * @param PhotoModel $photo
	 *
	 * @return void
	 *
	 * @throws IllegalOrderOfOperationException
	 */
	public function mount(PhotoModel $photo): void
	{
		$this->date = $photo->created_at->toIso8601String();
		$this->photoID = $photo->id;
		$this->title = $photo->title;
		$this->description = $photo->description ?? '';
		$this->tags = $photo->tags;
		$this->license = $photo->getOriginalLicense() ?? 'none';
		$this->tags_with_comma = join(', ', $photo->tags);
		$this->created_at = substr($this->date, 0, 16);
		$this->sec_tz = substr($this->date, 16);
	}

	/**
	 * Render the view.
	 *
	 * @return View
	 *
	 * @throws BindingResolutionException
	 */
	public function render(): View
	{
		return view('livewire.modules.photo.properties');
	}

	/**
	 * Update Username & Password of current user.
	 */
	public function submit(): void
	{
		$this->tags = collect(explode(',', $this->tags_with_comma))->map(fn ($v) => trim($v))->filter(fn ($v) => $v !== '')->all();
		$this->date = $this->created_at . $this->sec_tz;

		$rules = [
			RequestAttribute::TITLE_ATTRIBUTE => ['required', new TitleRule()],
			...SetPhotoDescriptionRuleSet::rules(),
			RequestAttribute::TAGS_ATTRIBUTE => 'present|array',
			RequestAttribute::TAGS_ATTRIBUTE . '.*' => 'required|string|min:1',
			RequestAttribute::LICENSE_ATTRIBUTE => ['required', new Enum(LicenseType::class)],
			RequestAttribute::DATE_ATTRIBUTE => 'required|date',
		];

		if (!$this->areValid($rules)) {
			return;
		}

		$photo = PhotoModel::query()->where('id', '=', $this->photoID)->first();
		$this->authorize(PhotoPolicy::CAN_EDIT, $photo);

		$photo->title = $this->title;
		$photo->description = $this->description;
		$photo->created_at = $this->created_at;
		$photo->tags = $this->tags;
		$photo->license = LicenseType::from($this->license);
		$photo->save();

		$this->notify(__('lychee.CHANGE_SUCCESS'));
	}

	final public function getLicensesProperty(): array
	{
		return LicenseType::localized();
	}
}
