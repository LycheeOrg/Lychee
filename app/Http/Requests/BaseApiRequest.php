<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Http\Requests;

use App\Contracts\Exceptions\LycheeException;
use App\Exceptions\Internal\FrameworkException;
use App\Exceptions\Internal\InvalidSmartIdException;
use App\Exceptions\Internal\QueryBuilderException;
use App\Exceptions\UnauthenticatedException;
use App\Exceptions\UnauthorizedException;
use App\Factories\AlbumFactory;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use LycheeVerify\Contract\VerifyInterface;
use LycheeVerify\Verify;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;

abstract class BaseApiRequest extends FormRequest
{
	protected AlbumFactory $albumFactory;
	protected VerifyInterface $verify;

	/**
	 * @throws FrameworkException
	 *
	 * @phpstan-ignore-next-line
	 */
	public function __construct(
		array $query = [],
		array $request = [],
		array $attributes = [],
		array $cookies = [],
		array $files = [],
		array $server = [],
		$content = null,
	) {
		try {
			$this->albumFactory = resolve(AlbumFactory::class);
			$this->verify = resolve(Verify::class);
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
		throw Auth::check() ? new UnauthorizedException() : new UnauthenticatedException();
	}

	/**
	 * Converts the input value to a boolean.
	 *
	 * Opposed to trivial type-casting the conversion also correctly recognizes
	 * the inputs `0`, `1`, `'0'`, `'1'`, `'true'` and `'false'`.
	 *
	 * @param mixed $value
	 *
	 * @return bool
	 */
	protected static function toBoolean(mixed $value): bool
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
	 * @return array<string,string|array<int,string|\Illuminate\Contracts\Validation\ValidationRule|\Illuminate\Validation\Rules\Enum>>
	 */
	abstract public function rules(): array;

	/**
	 * Post-processes the validated values.
	 *
	 * @param array<string,mixed> $values
	 * @param UploadedFile[]      $files
	 *
	 * @return void
	 *
	 * @throws ModelNotFoundException
	 * @throws InvalidSmartIdException
	 * @throws QueryBuilderException
	 */
	abstract protected function processValidatedValues(array $values, array $files): void;
}
