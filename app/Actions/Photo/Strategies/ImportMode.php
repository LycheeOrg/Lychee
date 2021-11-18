<?php

namespace App\Actions\Photo\Strategies;

class ImportMode
{
	protected bool $deleteImported = false;
	protected bool $skipDuplicates = false;
	protected bool $importViaSymlink = false;
	protected bool $resyncMetadata = false;

	public function __construct(
		bool $deleteImported = false,
		bool $skipDuplicates = false,
		bool $importViaSymlink = false,
		bool $resyncMetadata = false
	) {
		$this->setMode(
			$deleteImported, $skipDuplicates, $importViaSymlink, $resyncMetadata
		);
	}

	public function setMode(
		bool $deleteImported = false,
		bool $skipDuplicates = false,
		bool $importViaSymlink = false,
		bool $resyncMetadata = false
	): void {
		$this->deleteImported = $deleteImported;
		$this->skipDuplicates = $skipDuplicates;
		$this->importViaSymlink = $importViaSymlink;
		$this->resyncMetadata = $resyncMetadata;
		// avoid incompatible settings (delete originals takes precedence over symbolic links)
		if ($deleteImported) {
			$this->importViaSymlink = false;
		}
		// (re-syncing metadata makes no sense when importing duplicates)
		if (!$skipDuplicates) {
			$this->resyncMetadata = false;
		}
	}

	public function setDeleteImported(bool $flag): void
	{
		$this->deleteImported = $flag;
		// avoid incompatible settings (delete originals takes precedence over symbolic links)
		if ($this->deleteImported) {
			$this->importViaSymlink = false;
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
