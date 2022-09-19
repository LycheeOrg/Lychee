<?php

namespace App\Http\Controllers;

use App\Exceptions\Internal\FrameworkException;
use App\Http\Requests\View\GetPhotoViewRequest;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;

class ViewController extends Controller
{
	/**
	 * View is only used when sharing a single picture.
	 *
	 * @param GetPhotoViewRequest $request
	 *
	 * @return RedirectResponse
	 *
	 * @throws FrameworkException
	 */
	public function view(GetPhotoViewRequest $request): RedirectResponse
	{
		try {
			$photo = $request->photo();

			return redirect('/#view/' . $photo->id);
		} catch (BindingResolutionException $e) {
			throw new FrameworkException('Laravel\'s container component', $e);
		}
	}
}
