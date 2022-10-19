<?php

namespace App\DTO;

class LycheeChannelInfo extends DTO
{
	public const RELEASE_CHANNEL = 0;
	public const GIT_CHANNEL = 1;

	/**
	 * @var int either {@link LycheeChannelInfo::RELEASE_CHANNEL} or {@link LycheeChannelInfo::GIT_CHANNEL}
	 *
	 * @phpstan-var int<0,1>
	 */
	public int $channelType;
	public ?LycheeGitInfo $gitInfo;
	public ?Version $releaseVersion;

	/**
	 * @param int                $channelType    either {@link LycheeChannelInfo::RELEASE_CHANNEL} or {@link LycheeChannelInfo::GIT_CHANNEL}
	 * @param LycheeGitInfo|null $lycheeGitInfo
	 * @param Version|null       $releaseVersion
	 *
	 * @phpstan-param int<0,1> $channelType
	 */
	protected function __construct(int $channelType, ?LycheeGitInfo $lycheeGitInfo, ?Version $releaseVersion)
	{
		$this->channelType = $channelType;
		$this->gitInfo = $lycheeGitInfo;
		$this->releaseVersion = $releaseVersion;
	}

	public static function createReleaseInfo(?Version $releaseVersion): self
	{
		return new self(self::RELEASE_CHANNEL, null, $releaseVersion);
	}

	public static function createGitInfo(?LycheeGitInfo $lycheeGitInfo): self
	{
		return new self(self::GIT_CHANNEL, $lycheeGitInfo, null);
	}

	/**
	 * {@inheritDoc}
	 */
	public function toArray(): array
	{
		return match ($this->channelType) {
			self::RELEASE_CHANNEL => [
				'channel' => 'release',
				'version' => $this->releaseVersion?->toArray(),
			],
			self::GIT_CHANNEL => [
				'channel' => 'git',
				'info' => $this->gitInfo?->toArray(),
			],
		};
	}
}
