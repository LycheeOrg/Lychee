<?php

namespace App\Livewire\Components\Modules\Photo;

use App\Contracts\Http\Requests\RequestAttribute;
use App\Exceptions\Internal\IllegalOrderOfOperationException;
use App\Http\RuleSets\Photo\SetPhotoDescriptionRuleSet;
use App\Livewire\Traits\Notify;
use App\Livewire\Traits\UseValidator;
use App\Models\Configs;
use App\Models\Photo as PhotoModel;
use App\Policies\PhotoPolicy;
use App\Rules\TitleRule;
use Illuminate\Contracts\Container\BindingResolutionException;
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
	public string $title;
	public string $created_at;
	public string $description;
	public string $tags_with_comma;

	/** @var array<int,string> */
	#[Locked] public array $tags;
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
		$date_format_uploaded = Configs::getValueAsString('date_format_sidebar_uploaded');

		$this->photoID = $photo->id;
		$this->title = $photo->title;
		$this->created_at = $photo->created_at->format($date_format_uploaded);
		$this->description = $photo->description ?? '';
		$this->tags = $photo->tags;
		$this->tags_with_comma = join(', ', $photo->tags);
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
		$this->tags = collect(explode(',', $this->tags_with_comma))->map(fn (string $v) => trim($v))->all();

		$rules = [
			RequestAttribute::TITLE_ATTRIBUTE => ['required', new TitleRule()],
			...SetPhotoDescriptionRuleSet::rules(),
			RequestAttribute::TAGS_ATTRIBUTE => 'present|array',
			RequestAttribute::TAGS_ATTRIBUTE . '.*' => 'required|string|min:1',
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
		$photo->save();

		$this->notify(__('lychee.CHANGE_SUCCESS'));
	}
}
