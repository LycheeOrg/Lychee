<?php

namespace App\Enum;

enum StorageDiskType: string
{
	case LOCAL = 'images';
	case S3 = 's3';
}