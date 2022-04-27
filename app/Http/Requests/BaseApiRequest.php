<?php

namespace App\Http\Requests;

use App\Actions\AlbumAuthorisationProvider;
use App\Actions\PhotoAuthorisationProvider;
use App\Contracts\AbstractAlbum;
use App\Contracts\LycheeException;
use App\Exceptions\Internal\FrameworkException;
use App\Exceptions\Internal\InvalidSmartIdException;
use App\Exceptions\Internal\QueryBuilderException;
use App\Exceptions\UnauthenticatedException;
use App\Exceptions\UnauthorizedException;
use App\Facades\AccessControl;
use App\Factories\AlbumFactory;
use App\Models\Photo;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection as BaseCollection;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;

abstract class BaseApiRequest extends FormRequest
{
	protected AlbumFactory $albumFactory;
	protected AlbumAuthorisationProvider $albumAuthorisationProvider;
	protected PhotoAuthorisationProvider $photoAuthorisationProvider;

	/**
	 * @throws FrameworkException
	 */
	public function __construct(
		array $query = [],
		array $request = [],
		array $attributes = [],
		array $cookies = [],
		array $files = [],
		array $server = [],
		$content = null
	) {
		try {
			$this->albumFactory = resolve(AlbumFactory::class);
			$this->albumAuthorisationProvider = resolve(AlbumAuthorisationProvider::class);
			$this->photoAuthorisationProvider = resolve(PhotoAuthorisationProvider::class);
			parent::__construct($query, $request, $attributes, $cookies, $files, $server, $content);
		} catch (BindingResolutionException $e) {
			throw new FrameworkException('Laravel\'s provider component', $e);
		}
	}

	/**
	 * Validate the class instance.
	 *
	 * Fixes another Laravel stupidity.
	 * We must **first** validate the input parameters of the request
	 * for syntactical correctness, and **then** authorize the request.
	 * Rationale: Whether a user is authorized to perform a specific action or
	 * not typically depends on the input parameters (e.g. the ID of the model,
	 * the property the user wants to change, the new value of the property,
	 * etc.).
	 * Hence, the input should be validated **before** a potential DB query is
	 * executed to determine the user's authorization.
	 * The original Laravel method tries to authorize the user first and
	 * then validate the request
	 * (see {@link \Illuminate\Validation\ValidatesWhenResolvedTrait::validateResolved()}).
	 *
	 * @return void
	 *
	 * @throws BindingResolutionException
	 * @throws ValidationException
	 * @throws UnauthorizedException
	 * @throws BadRequestException
	 */
	public function validateResolved(): void
	{
		// 1. Validate the request
		$this->prepareForValidation();
		$instance = $this->getValidatorInstance();
		if ($instance->fails()) {
			// the default implementation throws `ValidationException`
			$this->failedValidation($instance);
		}
		$this->passedValidation();

		// 2. Authorize the request
		if (!$this->passesAuthorization()) {
			$this->failedAuthorization();
		}
	}

	/**
	 * Called by the framework after successful input validation.
	 *
	 * Simply forwards the call to {@link BaseApiRequest::processValidatedValues()}
	 * of the child class.
	 *
	 * @throws ValidationException
	 * @throws BadRequestException
	 * @throws ModelNotFoundException
	 * @throws InvalidSmartIdException
	 * @throws QueryBuilderException
	 */
	protected function passedValidation()
	{
		$this->processValidatedValues($this->validated(), $this->allFiles());
	}

	/**
	 * Handles a failed authorization attempt.
	 *
	 * Always throws either {@link UnauthorizedException} or
	 * {@link UnauthenticatedException}.
	 *
	 * @return void
	 *
	 * @throws UnauthorizedException
	 * @throws UnauthenticatedException
	 */
	protected function failedAuthorization(): void
	{
		throw AccessControl::is_logged_in() ? new UnauthorizedException() : new UnauthenticatedException();
	}

	/**
	 * Determines if the user is authorized to access the designated album.
	 *
	 * @param AbstractAlbum|null $album the album
	 *
	 * @return bool true, if the authenticated user is authorized
	 */
	protected function authorizeAlbumAccess(?AbstractAlbum $album): bool
	{
		return $this->albumAuthorisationProvider->isAccessible($album);
	}

	/**
	 * Determines if the user is authorized to access the designated albums.
	 *
	 * @param BaseCollection<AbstractAlbum> $albums the albums
	 *
	 * @return bool true, if the authenticated user is authorized
	 */
	protected function authorizeAlbumsAccess(BaseCollection $albums): bool
	{
		/** @var AbstractAlbum $album */
		foreach ($albums as $album) {
			if (!$this->authorizeAlbumAccess($album)) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Determines if the user is authorized to modify or write into the
	 * designated album.
	 *
	 * @param AbstractAlbum|null $album the album; `null` designates the root album
	 *
	 * @return bool true, if the authenticated user is authorized
	 */
	protected function authorizeAlbumWrite(?AbstractAlbum $album): bool
	{
		return $this->albumAuthorisationProvider->isEditable($album);
	}

	/**
	 * Determines if the user is authorized to modify or write into the
	 * designated albums.
	 *
	 * @param BaseCollection<AbstractAlbum> $albums the albums
	 *
	 * @return bool true, if the authenticated user is authorized
	 */
	protected function authorizeAlbumsWrite(BaseCollection $albums): bool
	{
		/** @var AbstractAlbum $album */
		foreach ($albums as $album) {
			if (!$this->authorizeAlbumWrite($album)) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Determines if the user is authorized to modify or write into the
	 * designated albums.
	 *
	 * @param string[] $albumIDs the album IDs
	 *
	 * @return bool true, if the authenticated user is authorized
	 *
	 * @throws QueryBuilderException
	 */
	protected function authorizeAlbumsWriteByIDs(array $albumIDs): bool
	{
		return $this->albumAuthorisationProvider->areEditableByIDs($albumIDs);
	}

	/**
	 * Determines if the user is authorized to see the designated photo.
	 *
	 * @param Photo|null $photo the photo; `null` is accepted for convenience
	 *                          and the `null` photo is always authorized
	 *
	 * @return bool true, if the authenticated user is authorized
	 */
	protected function authorizePhotoVisible(?Photo $photo): bool
	{
		return $this->photoAuthorisationProvider->isVisible($photo);
	}

	/**
	 * Determines if the user is authorized to download the designated photo.
	 *
	 * @param Photo $photo the photo
	 *
	 * @return bool true, if the authenticated user is authorized
	 */
	protected function authorizePhotoDownload(Photo $photo): bool
	{
		return $this->photoAuthorisationProvider->isDownloadable($photo);
	}

	/**
	 * Determines if the user is authorized to download the designated photos.
	 *
	 * @param EloquentCollection<Photo> $photos the photos
	 *
	 * @return bool true, if the authenticated user is authorized
	 */
	protected function authorizePhotosDownload(EloquentCollection $photos): bool
	{
		/** @var Photo $photo */
		foreach ($photos as $photo) {
			if (!$this->authorizePhotoDownload($photo)) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Determines if the user is authorized to modify the designated photo.
	 *
	 * @param Photo $photo the photo
	 *
	 * @return bool true, if the authenticated user is authorized
	 */
	protected function authorizePhotoWrite(Photo $photo): bool
	{
		return $this->photoAuthorisationProvider->isEditable($photo);
	}

	/**
	 * Determines if the user is authorized to modify the designated photos.
	 *
	 * @param EloquentCollection<Photo> $photos the photos
	 *
	 * @return bool true, if the authenticated user is authorized
	 */
	protected function authorizePhotosWrite(EloquentCollection $photos): bool
	{
		/** @var Photo $photo */
		foreach ($photos as $photo) {
			if (!$this->authorizePhotoWrite($photo)) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Determines if the user is authorized to modify the designated photos.
	 *
	 * @param string[] $photoIDs the IDs of the photos
	 *
	 * @return bool true, if the authenticated user is authorized
	 */
	protected function authorizePhotosWriteByIDs(array $photoIDs): bool
	{
		return $this->photoAuthorisationProvider->areEditableByIDs($photoIDs);
	}

	/**
	 * Converts the input value to a boolean.
	 *
	 * Opposed to trivial type-casting the conversion also correctly recognizes
	 * the inputs `0`, `1`, `'0'`, `'1'`, `'true'` and `'false'`.
	 *
	 * @param $value
	 *
	 * @return bool
	 */
	protected static function toBoolean($value): bool
	{
		return filter_var($value, FILTER_VALIDATE_BOOLEAN);
	}

	/**
	 * Determines if the user is authorized to make this request.
	 *
	 * @return bool
	 *
	 * @throws LycheeException
	 */
	abstract public function authorize(): bool;

	/**
	 * Returns the validation rules that apply to the request.
	 *
	 * @return array
	 */
	abstract public function rules(): array;

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
	abstract protected function processValidatedValues(array $values, array $files): void;
}
