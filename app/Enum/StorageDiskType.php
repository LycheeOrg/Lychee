<?php

declare(strict_types=1);

namespace App\Enum;

enum StorageDiskType: string
{
	case LOCAL = 'images';
	case S3 = 's3';
}