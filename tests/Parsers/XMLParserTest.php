<?php
/**
 * Created by PhpStorm.
 * User: sergio.rodenas
 * Date: 12/5/18
 * Time: 0:12
 */

namespace Rodenastyle\StreamParser\Test\Parsers;

use Rodenastyle\StreamParser\StreamParser;
use Rodenastyle\StreamParser\Test\Contracts\ElementAttributesManagement;
use Rodenastyle\StreamParser\Test\Contracts\ElementDepthManagement;
use Rodenastyle\StreamParser\Test\Contracts\ElementListManagement;
use Rodenastyle\StreamParser\Test\TestCase;
use Tightenco\Collect\Support\Collection;

class XMLParserTest extends TestCase implements ElementAttributesManagement, ElementListManagement, ElementDepthManagement {

	private $stub = __DIR__.DIRECTORY_SEPARATOR."..".DIRECTORY_SEPARATOR."Stubs".DIRECTORY_SEPARATOR."sample.xml";

	public function test_detects_main_elements_automatically()
	{
		$count = 0;

		StreamParser::xml($this->stub)->each(function() use (&$count){
			$count++;
		});

		$this->assertEquals(7, $count);
	}

	public function test_transforms_elements_to_collections()
	{
		StreamParser::xml($this->stub)->each(function($book){
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
			"Great Works of Art",
			"The Greatest Element"
		];

		StreamParser::xml($this->stub)->each(function($book) use ($titles){
			$this->assertContains($book->get('title'), $titles);
		});
	}

	public function test_also_transforms_element_childs_to_collections_recursively()
	{
		StreamParser::xml($this->stub)->each(function($book){
			if($book->has('comments')){
				$this->assertInstanceOf(Collection::class, $book->get('comments'));
			}
		});
	}

	public function test_element_attributes_are_in_the_values()
	{
		$ISBNList = [
			"10-000000-001",
			"11-000000-002",
			"11-000000-003",
			"11-000000-004",
			"10-000000-999",
			"11-000000-005",
            "11-000000-006"
		];

		StreamParser::xml($this->stub)->each(function($book) use ($ISBNList){
			$this->assertContains($book->get('ISBN'), $ISBNList);
		});
	}

	public function test_elements_lists_are_managed()
	{
		$totalComments = 6;
		$countedComments = 0;

		StreamParser::xml($this->stub)->each(function($book) use (&$countedComments){
			if($book->has('comments')){
				$countedComments += $book->get('comments')->count();
			}
		});

		$this->assertEquals($totalComments, $countedComments);
	}

	public function test_element_is_empty()
	{
		StreamParser::xml($this->stub)->each(function($book) {
			if($book->has('reviews')) {
				$this->assertEmpty($book->get('reviews'));
			}
		});
	}

	public function test_it_parses_child_with_same_parent_name() {
		StreamParser::xml($this->stub)->each(function($book){
			if($book->has('book')){
				$this->assertEquals($book->get('book'), "The nested element named like the parent");
			}
		});
	}

	public function test_pass_key_element_in_callback() {
		StreamParser::xml($this->stub)->each(function($book, $key){
			$this->assertEquals($key, 'book');
		});
	}
	
	public function test_element_name_in_sub_element()
    {
        StreamParser::xml($this->stub)->each(function($book) {
           if ($book->has('author')) {
                $author = $book->get('author')[0];
                $this->assertEquals($author->get('name'), "Test");
            }
        });
	}
	
	public function test_separate_parameters_list()
    {
        $ISBNList = [
            "10-000000-001",
            "11-000000-002",
            "11-000000-003",
            "11-000000-004",
            "10-000000-999",
            "11-000000-005",
            "11-000000-006"
        ];

        StreamParser::xml($this->stub)->withSeparatedParametersList()->each(function ($book) use ($ISBNList) {
            if ($book->has('__params')) {
                $this->assertContains($book->get('__params')->get('ISBN'), $ISBNList);
            }
        });
    }

    public function test_element_name()
    {
        StreamParser::xml($this->stub)->withElementName()->each(function ($book) {

            /** @var Collection $book */
            self::assertTrue($book->has('__name'));
            self::assertEquals('book', $book->get('__name'));


            $comments = $book->get('comments');
            if($comments){
                self::assertFalse($comments->has('__name'));
            }
        });
    }
}
