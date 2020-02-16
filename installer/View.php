<?php /** @noinspection PhpIncludeInspection */


namespace Installer;


use Installer\Templates\Head;
use Installer\Templates\Tail;
use Template;

class View
{

	public function apply(string $view_name, array $inputs)
	{

		$template_name = __NAMESPACE__ . '\\Templates\\' .$view_name;
		/** @var Template $template */
		$template = new $template_name();
		$head = new Head();
		$tail = new Tail();

		$head_input = ['title' => 'Lychee-installer', 'step' => $view_name, 'errors' => $inputs['errors'] ?? null];
		$head->print($head_input);
		$template->print($inputs);
		$tail->print($head_input);
	}

}