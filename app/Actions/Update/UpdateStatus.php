<?php

namespace App\Actions\Update;

enum UpdateStatus: int
{
	case NOT_MASTER = 0;
	case UP_TO_DATE = 1;
	case NOT_UP_TO_DATE = 2;
	case REQUIRE_MIGRATION = 3;
}