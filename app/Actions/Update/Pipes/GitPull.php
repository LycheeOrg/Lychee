<?php

namespace App\Actions\Update\Pipes;

use App\Contracts\Versions\LycheeVersionInterface;
use App\Facades\Helpers;
use Illuminate\Support\Facades\Config;
use function Safe\exec;

class GitPull extends AbstractUpdaterPipe
{
	/**
	 * {@inheritDoc}
	 */
	public function handle(array &$output, \Closure $next): array
	{
		$lycheeVersion = resolve(LycheeVersionInterface::class);
		if (!$lycheeVersion->isRelease()) {
			return $next($output);
		}

		if (Helpers::isExecAvailable()) {
			$command = 'git pull --rebase ' . Config::get('urls.git.pull') . ' master 2>&1';
			exec($command, $output);

			return $next($output);
		}

		return $output;
	}
}