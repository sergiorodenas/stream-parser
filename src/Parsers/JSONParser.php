<?php
/**
 * Created by PhpStorm.
 * User: sergio.rodenas
 * Date: 14/5/18
 * Time: 11:02
 */

namespace Rodenastyle\StreamParser\Parsers;


use Rodenastyle\StreamParser\Services\JsonCollectionParser as Parser;
use Rodenastyle\StreamParser\StreamParserInterface;
use Tightenco\Collect\Support\Collection;

class JSONParser implements StreamParserInterface
{
	protected $reader, $source;

	public function from(String $source): StreamParserInterface
	{
		$this->source = $source;

		return $this;
	}

	public function each(callable $function)
	{
		$this->start();
		$this->reader->parse($this->source, function(array $item) use ($function){
			$function((new Collection($item))->recursive());
		});
	}

	private function start()
	{
		$this->reader = new Parser();

		return $this;
	}
}
