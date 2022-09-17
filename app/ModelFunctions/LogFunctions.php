<?php

namespace App\ModelFunctions;

use App\Models\Logs;
use Psr\Log\AbstractLogger;
use Stringable;

// Class for FFMpeg to convert files to mov format
class LogFunctions extends AbstractLogger
{
	/**
	 * We check if a message is understandable as a string.
	 */
	private function is_stringable(mixed $in): bool
	{
		return !is_array($in) && (!is_object($in) || method_exists($in, '__toString'));
	}

	/**
	 * Interpolates context values into the message placeholders.
	 */
	private function interpolate(string $message, array $context = []): string
	{
		// build a replacement array with braces around the context keys
		$replace = [];
		foreach ($context as $key => $val) {
			// check that the value can be cast to string
			if ($this->is_stringable($val)) {
				$replace['{' . $key . '}'] = $val;
			}
		}

		// interpolate replacement values into the message and return
		return strtr($message, $replace);
	}

	/**
	 * Implements log so that AbstractLogger works.
	 */
	public function log($log, Stringable|string $message, array $context = []): void
	{
		$dbt = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 3);
		// debug_backtrace return the backtrace of all the function calls
		// we want to know who called us, because log is being called by the AbstractLogger,
		// we need to go one step further
		$fun = $dbt[2]['function'] ?? $dbt[1]['function'] ?? __METHOD__;
		$line = $dbt[2]['line'] ?? $dbt[1]['line'] ?? __LINE__;
		if ($this->is_stringable($message)) {
			$text = $this->interpolate($message, $context);
		} else {
			$text = 'argument is not stringable!';
		}

		$log = Logs::create([
			'type' => $log,
			'function' => $fun,
			'line' => $line,
			'text' => $text,
		]);
		$log->save();
	}
}
