<?php

namespace App\Image;

/**
 * Class `StreamStatFilter` collects {@link StreamStat} during streaming.
 */
class StreamStatFilter extends \php_user_filter
{
	public const REGISTERED_NAME = 'stream-stat-filter';

	/** @var string the used hashing algorithm; value must be supported by PHP, see {@link hash_algos()} */
	public const HASH_ALGO_NAME = 'sha1';

	/** @var \HashContext|null the hash context for progressive hashing */
	protected ?\HashContext $hashContext = null;

	/**
	 * Called to move streamed data from `$in` to `$out`.
	 *
	 * {@inheritDoc}
	 *
	 * Updates the byte counter and the hash.
	 */
	public function filter($in, $out, &$consumed, bool $closing): int
	{
		while ($bucket = stream_bucket_make_writeable($in)) {
			$consumed += $bucket->datalen;

			$streamStat = $this->getStreamStats();
			if ($streamStat !== null) {
				$streamStat->bytes += $bucket->datalen;
				\hash_update($this->hashContext, $bucket->data);
			}
			stream_bucket_append($out, $bucket);
		}

		return PSFS_PASS_ON;
	}

	/**
	 * Called when the stream is closed.
	 *
	 * {@inheritDoc}
	 *
	 * Finalizes the hash.
	 */
	public function onClose(): void
	{
		$streamStat = $this->getStreamStats();
		if ($streamStat !== null) {
			$streamStat->checksum = \hash_final($this->hashContext);
		}
		parent::onClose();
	}

	/**
	 * Called when the stream is opened.
	 *
	 * {@inheritDoc}
	 *
	 * Initializes the hash.
	 */
	public function onCreate(): bool
	{
		$streamStat = $this->getStreamStats();
		if ($streamStat !== null) {
			$streamStat->bytes = 0;
			$this->hashContext = \hash_init(self::HASH_ALGO_NAME);
		}

		return parent::onCreate();
	}

	/**
	 * Retrieve StreamStat.
	 *
	 * @return StreamStat|null
	 */
	private function getStreamStats(): StreamStat|null
	{
		if ($this->params instanceof StreamStat) {
			return $this->params;
		}
		if (is_array($this->params)) {
			for ($i = 0; $i < count($this->params); $i++) {
				if ($this->params[$i] instanceof StreamStat) {
					return $this->params[$i];
				}
			}
		}

		return null;
	}
}
