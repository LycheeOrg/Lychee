<?php

namespace App\Actions\Photo\Strategies;

class ImportMode
{
	public function __construct(
		protected readonly bool $deleteImported = false,
		protected readonly bool $skipDuplicates = false,
		protected bool $importViaSymlink = false,
		protected bool $resyncMetadata = false
	) {
		// avoid incompatible settings (delete originals takes precedence over symbolic links)
		if ($deleteImported) {
			$this->importViaSymlink = false;
		}
		// (re-syncing metadata makes no sense when importing duplicates)
		if (!$skipDuplicates) {
			$this->resyncMetadata = false;
		}
	}

	public function shallDeleteImported(): bool
	{
		return $this->deleteImported;
	}

	public function shallSkipDuplicates(): bool
	{
		return $this->skipDuplicates;
	}

	public function shallImportViaSymlink(): bool
	{
		return $this->importViaSymlink;
	}

	public function shallResyncMetadata(): bool
	{
		return $this->resyncMetadata;
	}
}
