<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Exceptions;

use App\Contracts\Exceptions\Handlers\HttpExceptionHandler;
use App\DTO\BacktraceRecord;
use App\Enum\SeverityType;
use App\Exceptions\Handlers\AccessDBDenied;
use App\Exceptions\Handlers\AdminSetterHandler;
use App\Exceptions\Handlers\InstallationHandler;
use App\Exceptions\Handlers\LegacyIdExceptionHandler;
use App\Exceptions\Handlers\MigrationHandler;
use App\Exceptions\Handlers\NoEncryptionKey;
use App\Exceptions\Handlers\ViteManifestNotFoundHandler;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Container\Container;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Foundation\ViteException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

/**
 * Lychee's custom exception handler.
 *
 * While the overall architectural approach of the original exception handler
 * of the framework is fine, the original exception handler is mostly broken
 * when it comes to the details.
 *
 * The overall architectural approach is as follows:
 *
 *  1. Substitute or wrap certain exceptions by or into other exceptions
 *     (i.e. via `mapException`)
 *  2. Decide whether the client expects an HTML or JSON response
 *  3. Convert (or "render") the exception into a response with said content
 *     type
 *
 * However, there are two major issues with the original exception handler:
 *
 *  - Substitution of exception is not limited to `mapException` but happens
 *    all the time which makes it hard to reliably predict what happens to
 *    an exception when a method (other than `mapException`) is called.
 *    One might end up with a different exception.
 *    Moreover, not all of these substitution are sensible enough to add the
 *    original exception as a predecessor to the new exception.
 *  - A constant mix-up of the terms "HTTP" and "HTML".
 *    The framework frequently uses the term "HTTP" as an antonym to "JSON"
 *    when "HTML" would be rather appropriate.
 *    For example like in `renderJsonResponse($e)` vs. `renderHttpResponse($e)`.
 *    The latter is called, when an exception shall be converted into HTML.
 *    But of course, a JSON response is also an HTTP response.
 *    It seems as if the framework is not even aware of this confusion.
 *
 * 90% of this handler are bug fixes.
 * This means, parent methods are not overwritten, because we need a special
 * non-standard behaviour, but simply the _right_ behaviour.
 * Unfortunately, this class cannot solve the unfortunate naming of some
 * methods, but must stick to the names used by the parent class.
 * Alternatively, this class could overwrite the entry method `render($e)`,
 * re-implement everything which comes after that (even using better names)
 * and let the rest of the parent class go down the drain.
 * However, this bears the risk that some 3rd-party calls unfixed methods of
 * the original exception handler.
 */
class Handler extends ExceptionHandler
{
	/**
	 * Maps class names of exceptions to their severity.
	 *
	 * By default, exceptions are logged with severity
	 * {@link SeverityType::ERROR} by {@link Handler::report()}.
	 * This array overwrites the default severity per exception.
	 *
	 * @var array<class-string,SeverityType>
	 */
	public const EXCEPTION2SEVERITY = [
		HttpHoneyPotException::class => SeverityType::NOTICE, // In theory this is a 404, but because it touches honey we don't really care.
		PhotoResyncedException::class => SeverityType::WARNING,
		PhotoSkippedException::class => SeverityType::WARNING,
		ImportCancelledException::class => SeverityType::NOTICE,
		ConfigurationException::class => SeverityType::NOTICE,
		LocationDecodingFailed::class => SeverityType::ERROR,
	];

	/**
	 * {@inheritDoc}
	 */
	protected $dontReport = [
		TokenMismatchException::class,
		SessionExpiredException::class,
		NoWriteAccessOnLogsExceptions::class,
		ViteException::class,
	];

	/** @var array<int,class-string<HttpExceptionHandler>> */
	protected $exception_checks = [
		NoEncryptionKey::class,
		AccessDBDenied::class,
		InstallationHandler::class,
		AdminSetterHandler::class,
		MigrationHandler::class,
		ViteManifestNotFoundHandler::class,
		LegacyIdExceptionHandler::class,
	];

	/** @var array<int,class-string<\Throwable>> */
	protected $force_exception_to_http = [
		ViteException::class,
	];

	/**
	 * {@inheritDoc}
	 */
	protected $internalDontReport = [];

	/** @var string the application path */
	protected string $appPath;

	public function __construct(Container $container)
	{
		parent::__construct($container);
		// Cache the application path to avoid multiple function calls
		// and potential exceptions in `report()`
		$this->appPath = app_path();
	}

	/**
	 * Maps an exception to something else.
	 *
	 * The method is called before {@link Handler::report()},
	 * {@link Handler::renderHttpException()},
	 * {@link Handler::convertExceptionToArray()}.
	 *
	 * We overwrite this method to wrap the following exception into proper
	 * HTTP exceptions which masquerades them and avoids that the framework
	 * handles them in special ways:
	 *
	 *  - {@link TokenMismatchException}
	 *  - {@link AuthenticationException}
	 *
	 * Note, that the default Laravel handler actually replaces exceptions by
	 * other exception at **three** places.
	 * The method {@link ExceptionHandler::render()} is the entry point for
	 * exception handling.
	 * This method calls
	 *
	 *  - {@link ExceptionHandler::mapException()}
	 *  - {@link ExceptionHandler::prepareException()}
	 *
	 * in that order which both replace exceptions.
	 * Finally, the parent method {@link ExceptionHandler::render()} also
	 * replaces some exceptions, too.
	 * We hook into the earliest of the three methods, i.e. `mapException`.
	 *
	 * **`TokenMismatchException`**
	 *
	 * Per default, the framework eventually replaces
	 * {@link TokenMismatchException} by generic HTTP exception in
	 * {@link ExceptionHandler::prepareException()}.
	 * We want to keep it more specific in order to detect this kind of
	 * exception more easily in the frontend.
	 *
	 * **`AuthenticationException`**
	 *
	 * Per default, the framework replaces {@link AuthenticationException}
	 * by a redirection to the route `login` in
	 * {@link ExceptionHandler::render()}.
	 * This is problematic for various reasons:
	 *
	 *  1. We do not really have a dedicated login page to which users
	 *     could be redirected.
	 *     Our login dialog is implemented in JavaScript.
	 *     Surely, we could use the main page `/gallery` as a redirection
	 *     target, but it would probably confuse people to be redirected there
	 *     without obvious reason.
	 *  2. In theory, all requests for content type `text/html` should always
	 *     succeed.
	 *     Any interaction which might trigger an authorization error is done
	 *     via JavaScript and JSON requests.
	 *     If an authorization error occurs for an HTML request, this indicates
	 *     a programming error.
	 *     In this case we want to be informed about that, and we want users
	 *     to tell us so, instead of suppressing the error by silent
	 *     redirection (cp. previous point).
	 *     Moreover, such an event always implies that the backend and the
	 *     frontend are out-of-sync with respect to the authentication state.
	 *     The backend considers the session to be unauthenticated while the
	 *     frontend considers the user still to be authenticated.
	 *     In particular, users could not even login again, even if the knew
	 *     what was going on, because the frontend did not provide the option
	 *     to do so.
	 *     Hence, we are in an unrecoverable situation anyway.
	 *  3. For JSON requests, we want the structure of the JSON response to
	 *     match our error reporting scheme as defined by
	 *     {@link Handler::convertExceptionToArray} such that the frontend
	 *     can properly interpret and display it.
	 *     By default, the framework would return a JSON response whose format
	 *     is unique to the {@link AuthenticationException}.
	 *
	 * @param \Throwable $e
	 *
	 * @return \Throwable
	 */
	protected function mapException(\Throwable $e): \Throwable
	{
		if ($e instanceof TokenMismatchException) {
			return new SessionExpiredException(SessionExpiredException::DEFAULT_MESSAGE, $e);
		}

		if ($e instanceof AuthenticationException) {
			return new UnauthenticatedException(UnauthenticatedException::DEFAULT_MESSAGE, $e);
		}

		return parent::mapException($e);
	}

	/**
	 * Prepare a response for the given exception.
	 *
	 * This method is called by the framework, _after_ the framework has
	 * decided that the client expects a HTML response, but _before_ the
	 * actual work horse {@link Handler::renderHttpException} is called.
	 *
	 * This method is 99% identical to the parent method except for a tiny
	 * bug fix which adds the original exception to the encapsulating
	 * `HttpException`.
	 *
	 * @param Request    $request
	 * @param \Throwable $e
	 *
	 * @return RedirectResponse|Response
	 *
	 * @throws BindingResolutionException
	 * @throws \InvalidArgumentException
	 * @throws ContainerExceptionInterface
	 * @throws NotFoundExceptionInterface
	 */
	protected function prepareResponse($request, \Throwable $e): RedirectResponse|Response
	{
		if (!$this->isHttpException($e)) {
			if ($this->mustForceToHttpException($e) || config('app.debug') !== true) {
				$e = new HttpException(500, $e->getMessage(), $e);
			} else {
				return $this->toIlluminateResponse($this->convertExceptionToResponse($e), $e);
			}
		}

		/** @var HttpExceptionInterface $e */
		return $this->toIlluminateResponse($this->renderHttpException($e), $e);
	}

	/**
	 * Check if the exception must be converted to HttpException.
	 *
	 * @param \Throwable $e to check
	 *
	 * @return bool true if conversion is required
	 */
	protected function mustForceToHttpException(\Throwable $e): bool
	{
		// This loop order is more efficient:
		// We take the first layer of the exception, check if match any of the forced conversion
		// then the next layer etc...
		do {
			foreach ($this->force_exception_to_http as $exception) {
				if ($e instanceof $exception) {
					return true;
				}
			}
		} while ($e = $e->getPrevious());

		return false;
	}

	/**
	 * Renders the given HttpException into HTML.
	 *
	 * This method is called by the framework if
	 *  1. `config('app.debug')` is not set, i.e. the application is not in debug mode
	 *  2. the client expects an HTML response
	 *
	 * **Attention:**
	 * This method is a misnomer caused by the framework.
	 * The framework provides two methods `renderHttpException` and
	 * `renderJsonException` with the former being called if the client
	 * expects HTML.
	 * Hence, the method should rather be named `renderHtmlException`.
	 * That current name of the method, if meant as an antonym to
	 * `renderJsonException` is obviously nonsense as JSON is also transported
	 * over HTTP.
	 *
	 * @param HttpExceptionInterface $e
	 *
	 * @return SymfonyResponse
	 *
	 * @noinspection PhpDocMissingThrowsInspection
	 * @noinspection PhpUnhandledExceptionInspection
	 */
	protected function renderHttpException(HttpExceptionInterface $e): SymfonyResponse
	{
		// If we are in debug mode, we use the internal method of the parent
		// method to render a useful response with backtrace, etc., depending
		// on the available extensions (i.e. Whoops, Symfony renderer, etc.)
		// If we are in non-debug mode, we render our own template that
		// matches Lychee's style and only contains rudimentary information.
		$defaultResponse = config('app.debug') === true ?
			$this->convertExceptionToResponse($e) :
			response()->view('error.error', [
				'code' => $e->getStatusCode(),
				'type' => class_basename($e),
				'message' => $e->getMessage(),
			], $e->getStatusCode(), $e->getHeaders());

		// We check, if any of our special handlers wants to do something.

		/** @var HttpExceptionHandler[] $checks */
		$checks = collect($this->exception_checks)
			->map(fn ($c) => new $c())
			->toArray();

		foreach ($checks as $check) {
			if ($check->check($e)) {
				return $check->renderHttpException($defaultResponse, $e);
			}
		}

		return $defaultResponse;
	}

	/**
	 * Converts the given exception to an array.
	 *
	 * The result only includes details about the exception, if the
	 * application is in debug mode.
	 * Identical to
	 * {@link \Illuminate\Foundation\Exceptions\Handler::convertExceptionToAray()}
	 * but recursively adds the previous exceptions, too.
	 *
	 * @param \Throwable $e
	 *
	 * @return array<string,mixed>
	 */
	protected function convertExceptionToArray(\Throwable $e): array
	{
		try {
			// debub mode.
			if (config('app.debug') === true) {
				return $this->convertDebugExceptionToArray($e);
			}

			// normal use
			return [
				'message' => $this->isHttpException($e) ? $e->getMessage() : 'Server Error',
				'exception' => class_basename($e),
			];
		} catch (\Throwable) {
			return [];
		}
	}

	/**
	 * Converts the given exception to an array.
	 *
	 * The result only includes details about the exception, if the
	 * application is in debug mode.
	 * Identical to
	 * {@link \Illuminate\Foundation\Exceptions\Handler::convertExceptionToAray()}
	 * but recursively adds the previous exceptions, too.
	 *
	 * @param \Throwable|null $e
	 *
	 * @return ($e is null ? null : array<string,mixed>)
	 */
	private function convertDebugExceptionToArray(\Throwable|null $e): array|null
	{
		if ($e === null) {
			return null;
		}

		$previous_exception = $this->convertDebugExceptionToArray($e->getPrevious());

		return [
			'message' => $e->getMessage(),
			'exception' => get_class($e),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
			'trace' => collect($e->getTrace())->map(function ($trace) {
				return Arr::except($trace, ['args']);
			})->all(),
			'previous_exception' => $previous_exception,
		];
	}

	/**
	 * Called by the framework if an exception occurs for logging purposes.
	 *
	 * As we have our own home-brewed logging mechanism via {@link Logs}
	 * which does not implement {@link \Psr\Log\LoggerInterface} and does
	 * not register with the service container, we override the method.
	 */
	public function report(\Throwable $e): void
	{
		$e = $this->mapException($e);

		if ($this->shouldntReport($e)) {
			return;
		}

		// We use the severity of the first exception for all subsequent
		// exceptions, because a causing exception should never be reported
		// with a higher severity than the eventual exception
		$severity = self::getLogSeverity($e);

		$msg = '';
		do {
			$cause = $this->findCause($e);
			if (count($cause) === 2) {
				$msg_ = $cause[1]->getMethodBeautified() . ':' . $cause[1]->getLine() . ' ' . $e->getMessage() . '; caused by';
				$msg = $msg_ . PHP_EOL . $msg;
			}

			if ($e->getPrevious() !== null) {
				$msg_ = $cause[0]->getMethodBeautified() . ':' . $cause[0]->getLine() . ' ' . $e->getMessage() . '; caused by';
			} else {
				$msg_ = $cause[0]->getMethodBeautified() . ':' . $cause[0]->getLine() . ' ' . $e->getMessage();
			}
			$msg = $msg_ . PHP_EOL . $msg;
		} while ($e = $e->getPrevious());
		try {
			Log::log($severity->value, $msg);
			/** @phpstan-ignore-next-line // Yes it is thrown, trust me.... */
		} catch (\UnexpectedValueException $e2) {
			throw new NoWriteAccessOnLogsExceptions($e2);
			// abort(507, 'Could not write in the logs. Check that storage/logs/ and containing files have proper permissions.');
		}
	}

	/**
	 * @param \Throwable $e
	 *
	 * @return SeverityType
	 */
	public static function getLogSeverity(\Throwable $e): SeverityType
	{
		return array_key_exists(get_class($e), self::EXCEPTION2SEVERITY) ?
			self::EXCEPTION2SEVERITY[get_class($e)] :
			SeverityType::ERROR;
	}

	/**
	 * Returns up to two interesting backtrace entries which might help to
	 * pinpoint the cause of an  exception.
	 *
	 * The first backtrace entry always points the most inner function which
	 * originally has thrown the exception.
	 * The can point to a file of the Lychee source code, but may also point
	 * to a file which is part of the PHP engine or one of the libraries.
	 *
	 * The second backtrace entry is optional and - if it included - always
	 * points to the most inner method of the Lychee source code on the
	 * stack which eventually has led to the exception.
	 *
	 * Laravel's backtraces are usually hundreds of frames deep with a lot
	 * of anonymous closures in between.
	 * Printing everything only litters the log with needless entries and
	 * won't help to keep track of what really happened.
	 * The two entries above have been chosen to be the most interesting ones.
	 * The first directly points to the failing line, the second one (if not
	 * identical to the first) indicates the last line of Lychee code which
	 * has been passed before the exception occurred.
	 *
	 * The standard backtrace reported by PHP is oddly strange.
	 * The attribute pair file/line on the one hand-side and class/function
	 * on the other hand-side of a standard PHP backtrace are off-by-one.
	 * The reported file/line of an entry of the backtrace don't refer to
	 * the position *inside* the reported class/function, but where
	 * class/method has been invoked.
	 * In particular, if one wants to know the position where the
	 * exception has been thrown, then one must not look up
	 * `backtrace[0]['file']` and `backtrace[0]['line']`, resp., but
	 * use `getFile` and `getLine()` of the exception.
	 *
	 * @param \Throwable $e
	 *
	 * @return BacktraceRecord[]
	 */
	private function findCause(\Throwable $e): array
	{
		$result = [];
		$backtrace = $e->getTrace();

		// Special rule for legacy PHP errors which are caught via
		// `set_error_handler`, converted into an `ErrorException` and
		// re-injected into the "modern" exception handling procedure
		//
		// The `set_error_handler` routine is special (thank you, PHP, for
		// nothing) in two ways: (a) the `file` parameter is not filled
		// (WTF?), and (b) the top entry of the backtrace points to
		// `set_error_handler` which is not really part of the frame stack
		// and does not provide any helpful information.
		//
		// For all who don't know the background: PHP provides two different
		// approaches to indicate and handle error conditions which both
		// interrupt the normal program flow:
		//
		//  a) the engine error reporting system (legacy approach)
		//  b) exceptions (modern approach)
		//
		// The legacy approach is very similar to POSIX signal handling in the
		// sense that one can register a static, global error handler and the
		// PHP engine calls this handler whenever some error has occurred
		// anywhere in the program.
		// This error handler is not part of the normal program stack, but
		// "lives" outside the normal program stack.
		// When the error handler returns, the normal program flow and call
		// stack is resumed.
		//
		// The modern approach uses exceptions which bubble up the call stack
		// until they are caught and handled.
		//
		// In order to unify the error handling, the default `error_handler`
		// nowadays wraps the reported error into a `\ErrorException` which
		// then is thrown as if it was thrown by the method which caused the
		// error in the first place.
		// Unfortunately, this messes with the backtrace.
		//
		// Hopefully, the whole legacy PHP error reporting system will be
		// nuked some day.
		// PHP 8 made a great step into that direction
		// (e.g., see https://wiki.php.net/rfc/consistent_type_errors,
		// https://wiki.php.net/rfc/engine_warnings,
		// https://wiki.php.net/rfc/lsp_errors).
		// I really like the sentence about the dark ages of PHP ;-).
		//
		// And hopefully, this is the only special rule we need and nobody
		// never ever misuses `\ErrorException` for "normal" exceptions.
		$offset = $e instanceof \ErrorException ? 1 : 0;

		$file = $e->getFile();
		$line = $e->getLine();
		$class = $backtrace[$offset]['class'] ?? '';
		$function = $backtrace[$offset]['function'] ?? '';

		// Always add the most inner frame
		$result[] = new BacktraceRecord(
			$file,
			$line,
			$class,
			$function
		);

		// If this frame is part of our own code, we are done.
		// We are also done, if there are no more frame on the backtrace
		if (str_contains($file, $this->appPath) || count($backtrace) <= $offset + 1) {
			return $result;
		}

		// Try to find the most inner method of our own code

		// Normally, every backtrace entry must have a `file` and `line`
		// attribute.
		// But in view of the problems with legacy error handling, this
		// must not be taken for granted.
		// It seems that for certain low level methods which are part of
		// the PHP engine (like `fopen`) this cannot be taken for granted.
		// As this method must not fail, we are better safe than sorry.
		$file = $backtrace[$offset]['file'] ?? '';
		$line = $backtrace[$offset]['line'] ?? 0;

		for ($idx = $offset + 1; $idx < count($backtrace); $idx++) {
			$class = $backtrace[$idx]['class'] ?? '';
			$function = $backtrace[$idx]['function'] ?? '';
			// If this frame is part of our own code, we are done.
			if (str_contains($file, $this->appPath)) {
				break;
			}
			$file = $backtrace[$idx]['file'] ?? '';
			$line = $backtrace[$idx]['line'] ?? 0;
		}

		$result[] = new BacktraceRecord(
			$file,
			$line,
			$class,
			$function
		);

		return $result;
	}

	/**
	 * An exception-free replacement for Laravel's global `report` function.
	 *
	 * Normally, if one is inside a `catch`-block handling exceptions, one
	 * does not like to deal with another (new) exception.
	 * If `report` threw an exception, what should we do about it anyway?
	 * Report it? ;-)
	 * Even though the Laravel framework is very reluctant to document the
	 * exceptions thrown by their methods, one of the few Laravel methods
	 * which documents an exception surprisingly is
	 * {@link \Illuminate\Contracts\Debug\ExceptionHandler::report()}.
	 * Unfortunately, it is an unspecific `\Throwable`.
	 * Even worse, we know that our own implementation of that method
	 * {@link Handler::report()} does not even throw an exception.
	 *
	 * Here, we rectify this situation by provided an alternative function
	 * which does not throw another exception.
	 * This also makes the IDE happy again, because we don't use an
	 * exception throwing method inside an exception handler.
	 *
	 * @param \Throwable $e
	 *
	 * @return void
	 */
	public static function reportSafely(\Throwable $e): void
	{
		try {
			report($e);
		} catch (\Throwable) {
			// Simply do nothing.
			// If even exception reporting does not work, we are lost anyway.
			// There is nothing we could do, except maybe die.
		}
	}
}
