<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Page extends Model
{

	public function content()
	{
		return $this->hasMany('App\PageContent', 'page_id', 'id')->orderBy('order','ASC');
	}

	public function scopeMenu($query)
	{
		return $query->where('in_menu', true)->where('enabled',true)->orderBy('order','ASC');
	}

	public function scopeEnabled($query)
	{
		return $query->where('enabled',true)->orderBy('order','ASC');
	}
<<<<<<< HEAD
=======

>>>>>>> WIP pages
}
