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

class StreamParser
{
	public static function xml(String $source){
		return (new XMLParser())->from($source);
	}

	public static function json(String $source){
		return (new JSONParser())->from($source);
	}

	public static function csv(String $source){
		return (new CSVParser())->from($source);
	}
}