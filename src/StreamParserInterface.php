<?php
/**
 * Created by PhpStorm.
 * User: sergio.rodenas
 * Date: 12/5/18
 * Time: 0:10
 */

namespace Rodenastyle\StreamParser;


interface StreamParserInterface
{
	public function from(String $source): StreamParserInterface;
	public function each(callable $function);
}