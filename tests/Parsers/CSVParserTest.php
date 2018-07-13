<?php
/**
 * Created by PhpStorm.
 * User: sergio.rodenas
 * Date: 15/5/18
 * Time: 19:00
 */

namespace Rodenastyle\StreamParser\Test\Parsers;

use PHPUnit\Framework\TestCase;
use Rodenastyle\StreamParser\StreamParser;
use Tightenco\Collect\Support\Collection;

class CSVParserTest extends TestCase
{
	private $stub = __DIR__.DIRECTORY_SEPARATOR."..".DIRECTORY_SEPARATOR."Stubs".DIRECTORY_SEPARATOR."sample.csv";

	public function test_detects_main_elements_automatically()
	{
		$count = 0;

		StreamParser::csv($this->stub)->each(function() use (&$count){
			$count++;
		});

		$this->assertEquals(5, $count);
	}

	public function test_transforms_elements_to_collections()
	{
		StreamParser::csv($this->stub)->each(function($book){
			$this->assertInstanceOf(Collection::class, $book);
		});
	}

	public function test_element_values_are_there_after_transform()
	{
		$titles = [
			"The Iliad and The Odyssey",
			"Anthology of World Literature",
			"Computer Dictionary",
			"Cooking on a Budget",
			"Great Works of Art"
		];

		StreamParser::csv($this->stub)->each(function($book) use ($titles){
			$this->assertContains($book->get('title'), $titles);
		});
	}

	public function test_also_transforms_element_childs_to_collections_recursively()
	{
		StreamParser::csv($this->stub)->each(function($book){
			if($book->has('comments')){
				$this->assertInstanceOf(Collection::class, $book->get('comments'));
			}
		});
	}
}
