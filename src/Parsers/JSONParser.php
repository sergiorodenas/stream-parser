<?php
/**
 * Created by PhpStorm.
 * User: sergio.rodenas
 * Date: 14/5/18
 * Time: 11:02
 */

namespace Rodenastyle\StreamParser\Parsers;


use Rodenastyle\StreamParser\Exceptions\StopParseException;
use Rodenastyle\StreamParser\Services\JsonCollectionParser as Parser;
use Rodenastyle\StreamParser\StreamParserInterface;
use Tightenco\Collect\Support\Collection;

class JSONParser implements StreamParserInterface
{
	protected $reader, $source;

	public function __construct()
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

	public function from(String $source): StreamParserInterface
	{
		$this->source = $source;

		return $this;
	}

	public function each(callable $function)
	{
		$this->start();
		try {
			$this->reader->parse($this->source, function(array $item) use ($function){
				if($function((new Collection($item))->recursive()) === false) {
					throw new StopParseException();
				}
			});
		} catch (StopParseException $e) {
		}
	}

	public function chunk($count, callable $function)
	{
		if($count <= 0) {
			return;
		}

		$this->start();
		try {
			$chunk = new Collection();

			$this->reader->parse($this->source, function(array $item) use ($function, &$chunk, &$count){
				$chunk->push((new Collection($item))->recursive());

				if($chunk->count() >= $count) {
					$stop = $function($chunk) === false;

					$chunk = new Collection();

					if($stop) {
						throw new StopParseException();
					}
				}
			});

			if($chunk->count() > 0) {
				$function($chunk);
			}
		} catch (StopParseException $e) {
		}
	}

	private function start()
	{
		$this->reader = new Parser();

		return $this;
	}
}
