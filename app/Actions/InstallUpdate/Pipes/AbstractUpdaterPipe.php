<?php

namespace App\Actions\InstallUpdate\Pipes;

abstract class AbstractUpdaterPipe
{
	/**
	 * @param array<int,string> &$output
	 * @param \Closure(array<int,string> $output): array<int,string> $next
	 *
	 * @return array<int,string>
	 */
	abstract public function handle(array &$output, \Closure $next): array;
}