<?php

/**
 * We don't care for unhandled exceptions in tests.
 * It is the nature of a test to throw an exception.
 * Without this suppression we had 100+ Linter warning in this file which
 * don't help anything.
 *
 * @noinspection PhpDocMissingThrowsInspection
 * @noinspection PhpUnhandledExceptionInspection
 */

namespace Tests\Feature;

use App\Models\Page;
use App\Models\PageContent;
use Tests\TestCase;

class PageTest extends TestCase
{
	public function testNoPage(): void
	{
		$response = $this->get('/hello');

		$response->assertNotFound();
	}

	public function testPage(): void
	{
		$page = new Page();
		$page->id = 2;
		$page->link = '/about';
		$page->order = 2;
		$page->in_menu = 1;
		$page->enabled = 0;
		$page->menu_title = 'About';
		static::assertTrue($page->save());

		$page_content = new PageContent();
		$page_content->page_id = 2;
		$page_content->content = 'dist/cat.jpg';
		$page_content->class = 'left_50';
		$page_content->type = 'img';
		static::assertTrue($page_content->save());

		$page_content_2 = new PageContent();
		$page_content_2->page_id = 2;
		$page_content_2->content = "# Hello\n*I'm a kitten*";
		$page_content_2->class = 'right_50';
		$page_content_2->type = 'div';
		static::assertTrue($page_content_2->save());

		$response = $this->get('/about');
		$response->assertNotFound();

		$page->enabled = 1;
		static::assertTrue($page->save());

		$response = $this->get('/about');
		$response->assertOk();

		/*
		 * cleaning up
		 */
		$page_content->delete();
		$page_content_2->delete();
		$page->delete();

		$response = $this->get('/about');
		$response->assertNotFound();
	}
}
