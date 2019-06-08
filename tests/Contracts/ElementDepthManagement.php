<?php
/**
 * Created by PhpStorm.
 * User: sergio.rodenas
 * Date: 3/6/18
 * Time: 19:43
 */

namespace Rodenastyle\StreamParser\Test\Contracts;


interface ElementDepthManagement
{
	/**
	 *  It manages to parse child elements with same parent name
     * 
     *  <booklist>
     *      <book>
     *          <title>Hello world</title>
     *          <book>456788</book>  <----- THIS IS ALSO PARSED
     *      </book>
     * </booklist>
     * 
	 */

	public function test_it_parses_child_with_same_parent_name();
}