<?php
/**
 * Created by PhpStorm.
 * User: sergio.rodenas
 * Date: 12/5/18
 * Time: 0:00
 */

namespace Rodenastyle\StreamParser;


use Rodenastyle\StreamParser\Parsers\CSVParser;
use Rodenastyle\StreamParser\Parsers\JSONParser;
use Rodenastyle\StreamParser\Parsers\XMLParser;
use Rodenastyle\StreamParser\Traits\Facade;
use Tightenco\Collect\Support\Collection;

class StreamParser
{
	use Facade;

	private function __construct()
	{
		Collection::macro('recursive', function () {
			return $this->map(function ($value) {
				if (is_array($value) || is_object($value)) {
					return (new Collection($value))->recursive();
				}
				return $value;
			});
		});
	}

	private function xml(String $source){
		return (new XMLParser())->from($source);
	}

	private function json(String $source){
		return (new JSONParser())->from($source);
	}

	private function csv(String $source){
		return (new CSVParser())->from($source);
	}
}