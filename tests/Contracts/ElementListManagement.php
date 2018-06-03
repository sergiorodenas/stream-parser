<?php
/**
 * Created by PhpStorm.
 * User: sergio.rodenas
 * Date: 3/6/18
 * Time: 19:43
 */

namespace Rodenastyle\StreamParser\Test\Contracts;


interface ElementListManagement
{
	/**
	 *  It manages to parse child lists with same keys (element lists):
	 */

	public function test_elements_lists_are_managed();
}