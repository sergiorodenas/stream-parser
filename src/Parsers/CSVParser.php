<?php
/**
 * Created by PhpStorm.
 * User: sergio.rodenas
 * Date: 15/5/18
 * Time: 18:59
 */

namespace Rodenastyle\StreamParser\Parsers;


use Rodenastyle\StreamParser\Exceptions\IncompleteParseException;
use Rodenastyle\StreamParser\StreamParserInterface;
use Tightenco\Collect\Support\Collection;

class CSVParser implements StreamParserInterface
{
	protected $reader, $source, $headers, $currentLine;

	public static $delimiters = [",", ";"];
	public static $skipsEmptyLines = true;

	public function from(String $source): StreamParserInterface
	{
		$this->source = $source;

		return $this;
	}

	public function each(callable $function)
	{
		$this->start();
		while($this->read()){
			if($this->currentLineIsValid()){
				$function($this->getCurrentLineAsCollection());
			}
		}
		$this->close();
	}

	private function start()
	{
		$this->reader = fopen($this->source, 'r');

		$this->read();
		$this->headers = $this->currentLine;

		return $this;
	}

	private function close(){
		if( ! fclose($this->reader)){
			throw new IncompleteParseException();
		}
	}

	private function read(): bool{
		$this->currentLine = new Collection(fgetcsv($this->reader));

		//EOF detection
		return $this->currentLine->first() !== false;
	}

	private function currentLineIsValid(): bool{
		if(static::$skipsEmptyLines){
			return ! $this->currentLineIsEmpty();
		}

		return true;
	}

	private function currentLineIsEmpty(){
		return $this->currentLine->reject(function($value){
			return $this->isEmptyValue($value);
		})->isEmpty();
	}

	private function isEmptyValue($value){
		return $value === "" || $value === null;
	}

	private function getCurrentLineAsCollection()
	{
		$headers = $this->headers;
		$values = $this->formatCurrentLineValues($this->currentLine);

		return $headers->intersectByKeys($this->currentLine)
			->combine($values->recursive())
			->reject(function($value){
				return $this->isEmptyValue($value);
			});
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
					return $this->isEmptyValue($value);
				});
			} else {
				return $value;
			}
		});
	}
}
