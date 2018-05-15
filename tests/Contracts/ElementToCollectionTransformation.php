<?php
/**
 * Created by PhpStorm.
 * User: sergio.rodenas
 * Date: 15/5/18
 * Time: 16:55
 */

namespace Rodenastyle\StreamParser\Test\Contracts;


interface ElementToCollectionTransformation
{
	public function test_transforms_elements_to_collections();

	public function test_element_values_are_there_after_transform();

	/**
	 *  It gets element childs transformed to collections recursively:
	 *
	 *  <booklist>
	 *      <book> <-- as Collection
	 *          <title>Example</title>
	 *          <comments> <-- also as Collection
	 *              <comment>hello</comment>
	 *              <comment>world</comment>
	 *          </comments>
	 *      </book>
	 *  </booklist>
	 *
	 *  or
	 *
	 *  [
	 *      { <-- as Collection
	 *          title: 'Example',
	 *          comments: [ <-- also as Collection
	 *              'hello',
	 *              'world'
	 *          ]
	 *      }
	 *  ]
	 */

	public function test_also_transforms_element_childs_to_collections_recursively();
}