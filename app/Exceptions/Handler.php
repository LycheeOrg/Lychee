<?php

namespace App\Exceptions;

use App\Contracts\HttpExceptionHandler;
use App\Exceptions\Handlers\AccessDBDenied;
use App\Exceptions\Handlers\InstallationHandler;
use App\Exceptions\Handlers\MigrationHandler;
use App\Exceptions\Handlers\NoEncryptionKey;
use App\Models\Logs;
use Illuminate\Contracts\Container\Container;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Support\Arr;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

class Handler extends ExceptionHandler
{
	/**
	 * Maps class names of exceptions to their severity.
	 *
	 * By default, exceptions are logged with severity
	 * {@link Logs::SEVERITY_ERROR} by {@link Handler::report()}.
	 * This array overwrites the default severity per exception.
	 *
	 * @var array<class-string, int>
	 */
	public const EXCEPTION2SEVERITY = [
		PhotoSkippedException::class => Logs::SEVERITY_WARNING,
		ImportCancelledException::class => Logs::SEVERITY_NOTICE,
	];

	protected $dontReport = [];
	protected $internalDontReport = [];
	protected string $appPath;

	public function __construct(Container $container)
	{
		parent::__construct($container);
		// Cache the application path to avoid multiple function calls
		// and potential exceptions in `report()`
		$this->appPath = app_path();
	}

	/**
	 * Renders the given HttpException.
	 *
	 * This method is called by the framework if
	 *  1. `config('app.debug')` is not set, i.e. the application is not in debug mode
	 *  2. the client expects an HTML response
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
		// on the available extensions (i.e. Whoops, Symfonfy renderer, etc.)
		// If we are in non-debug mode, we render our own template that
		// matches Lychee's style and only contains rudimentary information.
		$defaultResponse = config('app.debug') ?
			$this->convertExceptionToResponse($e) :
			response()->view('error.error', [
				'code' => $e->getStatusCode(),
				'type' => class_basename($e),
				'message' => $e->getMessage(),
			], $e->getStatusCode(), $e->getHeaders());

		// We check, if any of our special handlers wants to do something.

		/** @var HttpExceptionHandler[] $checks */
		$checks = [
			new NoEncryptionKey(),
			new AccessDBDenied(),
			new InstallationHandler(),
			new MigrationHandler(),
		];

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
	 * @return array
	 */
	protected function convertExceptionToArray(\Throwable $e): array
	{
		try {
			return config('app.debug') ? [
				'message' => $e->getMessage(),
				'exception' => get_class($e),
				'file' => $e->getFile(),
				'line' => $e->getLine(),
				'trace' => collect($e->getTrace())->map(function ($trace) {
					return Arr::except($trace, ['args']);
				})->all(),
				'previous_exception' => $e->getPrevious() ? $this->convertExceptionToArray($e->getPrevious()) : null,
			] : [
				'message' => $this->isHttpException($e) ? $e->getMessage() : 'Server Error',
				'exception' => class_basename($e),
			];
		} catch (\Throwable) {
			return [];
		}
	}

	/**
	 * Called by the framework if an exception occurs for logging purposes.
	 *
	 * As we have our own home-brewed logging mechanism via {@link Logs}
	 * which does not implement {@link \Psr\Log\LoggerInterface} and does
	 * not register with the service container, we override the method.
	 *
	 * @param \Throwable $e the exception to log
	 */
	public function report(\Throwable $e): void
	{
		do {
			$e = $this->mapException($e);

			if ($this->shouldntReport($e)) {
				return;
			}

			$severity = array_key_exists(get_class($e), self::EXCEPTION2SEVERITY) ?
				self::EXCEPTION2SEVERITY[get_class($e)] :
				Logs::SEVERITY_ERROR;

			$backtrace = self::normalizeBacktrace($e);

			// Find the first (i.e. deepest) entry that refers to our own code base.
			// Methods inside the framework don't help us.
			$btEntry = Arr::first($backtrace, fn (array $bt) => str_contains($bt['file'], $this->appPath));
			$line = $btEntry['line'];
			$method = ($btEntry['class'] ? $btEntry['class'] . '::' : '') . ($btEntry['function'] ?: '<unknown>');

			if ($e->getPrevious() !== null) {
				Logs::log($severity, $method, $line, $e->getMessage() . '; caused by');
			} else {
				Logs::log($severity, $method, $line, $e->getMessage());
			}
		} while ($e = $e->getPrevious());
	}

	/**
	 * Returns the re-organized backtrace of an exception.
	 *
	 * The backtrace reported by PHP is oddly strange.
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
	 * @return array{file: string, line: int, class: ?string, function: ?string}
	 */
	private static function normalizeBacktrace(\Throwable $e): array
	{
		$backtrace = $e->getTrace();
		$file = $e->getFile();
		$line = $e->getLine();
		foreach ($backtrace as &$bt) {
			$f = $bt['file'];
			$l = $bt['line'];
			$bt['file'] = $file;
			$bt['line'] = $line;
			$file = $f;
			$line = $l;
		}
		$backtrace[] = [
			'file' => $file,
			'line' => $line,
			'class' => null,    // the top most call always happens in global
			'function' => null, // namespace outside a function
		];

		return $backtrace;
	}
}
