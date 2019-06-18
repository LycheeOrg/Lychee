<?php

namespace Tests\Feature;

use App\Page;
use App\PageContent;
use Tests\TestCase;

class PageTest extends TestCase
{
	public function test_no_page()
	{
		$response = $this->get('/hello');

		$response->assertStatus(404);
	}

	public function test_page()
	{
		$page = new Page();
		$page->id = 2;
		$page->link = '/about';
		$page->order = 2;
		$page->in_menu = 1;
		$page->enabled = 0;
		$page->menu_title = 'About';
		$this->assertTrue($page->save());

		$page_content = new PageContent();
		$page_content->page_id = 2;
		$page_content->content = 'dist/cat.jpg';
		$page_content->class = 'left_50';
		$page_content->type = 'img';
		$this->assertTrue($page_content->save());

		$page_content_2 = new PageContent();
		$page_content_2->page_id = 2;
		$page_content_2->content = "# Hello\n*I'm a kitten*";
		$page_content_2->class = 'right_50';
		$page_content_2->type = 'div';
		$this->assertTrue($page_content_2->save());

		$response = $this->get('/about');
		$response->assertStatus(404);

		$page->enabled = 1;
		$this->assertTrue($page->save());

		$response = $this->get('/about');
		$response->assertOk();

		/*
		 * cleaning up
		 */
		$page_content->delete();
		$page_content_2->delete();
		$page->delete();

		$response = $this->get('/about');
		$response->assertStatus(404);
	}
}
