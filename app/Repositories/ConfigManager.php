<?php

namespace App\Repositories;

use App\Exceptions\ConfigurationKeyMissingException;
use App\Exceptions\ExternalComponentMissingException;
use App\Exceptions\Handler;
use App\Exceptions\Internal\InvalidConfigOption;
use App\Exceptions\Internal\QueryBuilderException;
use App\Exceptions\UnexpectedException;
use App\Facades\Helpers;
use App\Models\Configs;
use Illuminate\Support\Facades\Log;
use function Safe\exec;

class ConfigManager
{
	/** @var array<string,string> */
	protected array $cache = [];

	/**
	 * Fetch a configuration value by key.
	 *
	 * @param string $key
	 *
	 * @return int|bool|string|null
	 */
	public function getValue(string $key): int|bool|string|null
	{
		if (count($this->cache) === 0) {
			$this->load();
		}

		if (!array_key_exists($key, $this->cache)) {
			/*
			 * For some reason the $default is not returned above...
			 */
			// @codeCoverageIgnoreStart
			Log::critical(__METHOD__ . ':' . __LINE__ . ' ' . $key . ' does not exist in config (local) !');

			throw new ConfigurationKeyMissingException($key . ' does not exist in config!');
			// @codeCoverageIgnoreEnd
		}

		return $this->cache[$key];
	}

	/**
	 * Get string configuration value.
	 *
	 * @param string $key
	 *
	 * @return string
	 *
	 * @throws ConfigurationKeyMissingException
	 */
	public function getValueAsString(string $key): string
	{
		return strval($this->getValue($key));
	}

	/**
	 * Get int configuration value.
	 *
	 * @param string $key
	 *
	 * @return int
	 *
	 * @throws ConfigurationKeyMissingException
	 */
	public function getValueAsInt(string $key): int
	{
		return intval($this->getValue($key));
	}

	/**
	 * Get bool configuration value.
	 *
	 * @param string $key
	 *
	 * @return bool
	 *
	 * @throws ConfigurationKeyMissingException
	 */
	public function getValueAsBool(string $key): bool
	{
		return $this->getValue($key) === '1';
	}

	/**
	 * @template T of \BackedEnum
	 *
	 * @param string          $key
	 * @param class-string<T> $type
	 *
	 * @return T|null
	 */
	public function getValueAsEnum(string $key, string $type): \BackedEnum|null
	{
		if (!function_exists('enum_exists') || !enum_exists($type) || !method_exists($type, 'tryFrom')) {
			// @codeCoverageIgnoreStart
			throw new UnexpectedException();
			// @codeCoverageIgnoreEnd
		}

		return $type::tryFrom($this->getValue($key));
	}

	/**
	 * load the configuration.
	 *
	 * @return array
	 */
	public function load(): array
	{
		if (count($this->cache) > 0) {
			return $this->cache;
		}

		try {
			$this->cache = Configs::query()
				->select(['key', 'value'])
				->pluck('value', 'key')
				->all();
			// @codeCoverageIgnoreStart
		} catch (\Throwable) {
			$this->cache = [];
		}
		// @codeCoverageIgnoreEnd

		return $this->cache;
	}

	/**
	 * Reset the cache.
	 */
	public function invalidateCache(): void
	{
		$this->cache = [];
	}

	/**
	 * @return bool returns the Imagick setting
	 */
	public function hasImagick(): bool
	{
		return
			extension_loaded('imagick') &&
			$this->getValueAsBool('imagick');
	}

	/**
	 * @return bool returns the Exiftool setting
	 */
	public function hasExiftool(): bool
	{
		// has_exiftool has the following values:
		// 0: No Exiftool
		// 1: Exiftool is available
		// 2: Not yet tested if exiftool is available

		$has_exiftool = $this->getValueAsInt('has_exiftool');

		// value not yet set -> let's see if exiftool is available
		if ($has_exiftool === 2) {
			if (Helpers::isExecAvailable()) {
				try {
					$cmd_output = exec('command -v exiftool');
					// @codeCoverageIgnoreStart
				} catch (\Exception $e) {
					$cmd_output = false;
					Handler::reportSafely(new ExternalComponentMissingException('could not find exiftool; `has_exiftool` will be set to 0', $e));
				}
				// @codeCoverageIgnoreEnd
				$path = $cmd_output === false ? '' : $cmd_output;
				$has_exiftool = $path === '' ? 0 : 1;
			} else {
				// @codeCoverageIgnoreStart
				$has_exiftool = 0;
				// @codeCoverageIgnoreEnd
			}

			try {
				Configs::set('has_exiftool', $has_exiftool);
				$this->cache['has_exiftool'] = strval($has_exiftool);
				// @codeCoverageIgnoreStart
			} catch (InvalidConfigOption|QueryBuilderException $e) {
				// If we could not save the detected setting, still proceed
				Handler::reportSafely($e);
			}
			// @codeCoverageIgnoreEnd
		}

		return $has_exiftool === 1;
	}

	/**
	 * @return bool returns the FFMpeg setting
	 */
	public function hasFFmpeg(): bool
	{
		// has_ffmpeg has the following values:
		// 0: No ffmpeg
		// 1: ffmpeg is available
		// 2: Not yet tested if ffmpeg is available

		$has_ffmpeg = self::getValueAsInt('has_ffmpeg');

		// value not yet set -> let's see if ffmpeg is available
		if ($has_ffmpeg === 2) {
			if (Helpers::isExecAvailable()) {
				try {
					$cmd_output = exec('command -v ffmpeg');
					// @codeCoverageIgnoreStart
				} catch (\Exception $e) {
					$cmd_output = false;
					Handler::reportSafely(new ExternalComponentMissingException('could not find ffmpeg; `has_ffmpeg` will be set to 0', $e));
				}
				// @codeCoverageIgnoreEnd
				$path = $cmd_output === false ? '' : $cmd_output;
				$has_ffmpeg = $path === '' ? 0 : 1;
			} else {
				// @codeCoverageIgnoreStart
				$has_ffmpeg = 0;
				// @codeCoverageIgnoreEnd
			}

			try {
				Configs::set('has_ffmpeg', $has_ffmpeg);
				$this->cache['has_ffmpeg'] = strval($has_ffmpeg);
				// @codeCoverageIgnoreStart
			} catch (InvalidConfigOption|QueryBuilderException $e) {
				// If we could not save the detected setting, still proceed
				Handler::reportSafely($e);
			}
			// @codeCoverageIgnoreEnd
		}

		return $has_ffmpeg === 1;
	}
}
