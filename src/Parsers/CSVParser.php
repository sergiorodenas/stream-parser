<?php
/**
 * Created by PhpStorm.
 * User: sergio.rodenas
 * Date: 15/5/18
 * Time: 18:59
 */

namespace Rodenastyle\StreamParser\Parsers;


use Rodenastyle\StreamParser\Exceptions\IncompleteParseException;
use Rodenastyle\StreamParser\Exceptions\StopParseException;
use Rodenastyle\StreamParser\StreamParserInterface;
use Tightenco\Collect\Support\Collection;

class CSVParser implements StreamParserInterface
{
	protected $reader, $source, $headers, $currentLine;

	public static $delimiters = [",", ";"];

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
			while($this->read()){
				if($function($this->getCurrentLineAsCollection()) === false) {
					break;
				}
			}
		} catch (StopParseException $e) {
		} finally {
			$this->close();
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

			while($this->read()){
				$chunk->push($this->getCurrentLineAsCollection());

				if($chunk->count() >= $count) {
					$stop = $function($chunk) === false;

					$chunk = new Collection();

					if($stop) {
						break;
					}
				}
			}

			if($chunk->count() > 0) {
				$function($chunk);
			}
		} catch (StopParseException $e) {
		} finally {
			$this->close();
		}
	}

	private function start()
	{
		$this->reader = fopen($this->source, 'r');

		$this->read();
		$this->headers = new Collection($this->currentLine);

		return $this;
	}

	private function close(){
		if( ! fclose($this->reader)){
			throw new IncompleteParseException();
		}
	}

	private function read(): bool{
		$this->currentLine = (new Collection(fgetcsv($this->reader)))->filter();
		return $this->currentLine->isNotEmpty();
	}

	private function getCurrentLineAsCollection()
	{
		$headers = $this->headers;
		$values = $this->formatCurrentLineValues($this->currentLine);

		return $headers->intersectByKeys($this->currentLine)->combine($values->recursive());
	}

	private function formatCurrentLineValues(Collection $collection){
		$this->explodeCollectionValues($collection);
		return $collection;
	}

	private function explodeCollectionValues(Collection $collection){
		$collection->transform(function($value){
			(new Collection(static::$delimiters))->each(function($delimiter) use (&$value){
				if( ! is_array($value) && strpos($value, $delimiter) !== false){
					$value = explode($delimiter, $value);
				}
			});
			if(is_array($value)){
				return (new Collection($value))->reject(function($value){
					return empty($value);
				});
			} else {
				return $value;
			}
		});
	}
}
