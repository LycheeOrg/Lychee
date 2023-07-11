<?php

namespace App\Enum\Livewire;

enum AlbumMenuMode: string
{
	case ABOUT = 'about';
	case SHARE = 'share';
	case MOVE = 'move';
	case TRANSFER = 'transfer';
	case DELETE = 'delete';
}
