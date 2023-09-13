<?php

namespace App\Enum\Livewire;

enum FileStatus: string
{
	case UPLOADING = 'uploading';
	case PROCESSING = 'processing';
	case READY = 'ready';
	case SKIPPED = 'skipped';
	case DONE = 'done';
}
