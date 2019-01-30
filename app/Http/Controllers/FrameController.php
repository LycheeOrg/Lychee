<?php

namespace App\Http\Controllers;


use App\Configs;
use App\Response;
use Illuminate\Http\Request;


class FrameController extends Controller
{
	/**
	 * @return false|string
	 */
	function init()
	{
		Configs::get();

		if (Configs::get_value('Mod_Frame') != '1') {
			return redirect()->route('home');
		}

		return view('frame');

	}

	function getSettings(Request $request)
	{
		Configs::get();

		if(Configs::get_value('Mod_Frame') != '1') {
			return Response::error('Frame is not enabled');
		}

		$return = array();
		$return['refresh'] = Configs::get_value('Mod_Frame_refresh');

		return $return;

	}

}