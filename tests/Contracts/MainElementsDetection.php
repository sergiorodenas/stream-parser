<?php
/**
 * Created by PhpStorm.
 * User: sergio.rodenas
 * Date: 14/5/18
 * Time: 11:35
 */

namespace Rodenastyle\StreamParser\Test\Contracts;


interface MainElementsDetection
{
	/**
	 *  It gets the main elements automatically:
	 *
	 *  <booklist>
	 *      <book>...</book> <-- main elements
	 *      <book>...</book>
	 *      <book>...</book>
	 *  </booklist>
	 *
	 *  or
	 *
	 *  [
	 *      {title: 'Hello'}, <-- main elements
	 *      {title: 'World'},
	 *  ]
	 *
	 */

	public function test_detects_main_elements_automatically();
}