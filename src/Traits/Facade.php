<?php
/**
 * Created by PhpStorm.
 * User: sergio.rodenas
 * Date: 27/6/18
 * Time: 15:49
 */

namespace Rodenastyle\StreamParser\Traits;


trait Facade
{
	public static function __callStatic($name, $arguments)
	{
		return (new static())->{$name}(...$arguments);
	}
}