<?php
/**
 * Created by PhpStorm.
 * User: sergio.rodenas
 * Date: 12/5/18
 * Time: 0:09
 */

namespace Rodenastyle\StreamParser\Parsers;


use Rodenastyle\StreamParser\Exceptions\IncompleteParseException;
use Rodenastyle\StreamParser\Exceptions\StopParseException;
use Rodenastyle\StreamParser\StreamParserInterface;
use Tightenco\Collect\Support\Collection;
use XMLReader;


class XMLParser implements StreamParserInterface
{
	protected $reader,$source;

	protected $skipFirstElement = true;

	public function from(String $source): StreamParserInterface
	{
		$this->source = $source;

		return $this;
	}

	public function withoutSkippingFirstElement(){
		$this->skipFirstElement = false;

		return $this;
	}

	public function each(callable $function)
	{
		$this->start();
		try {
			while($this->reader->read()){
				if($this->searchElement($function) === false) {
					break;
				}
			}
		} catch (StopParseException $e) {
		} finally {
			$this->stop();
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

			while($this->reader->read()){
				$this->searchElement(function($item) use (&$chunk) {
					$chunk->push($item);
				});

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
			$this->stop();
		}
	}

	private function searchElement(callable $function)
	{
		if($this->isElement() && ! $this->shouldBeSkipped()){
			return $function($this->extractElement($this->reader->name));
		}
	}

	private function extractElement(String $elementName, $couldBeAnElementsList = false)
	{
		$elementCollection = (new Collection())->merge($this->getCurrentElementAttributes());

		while($this->reader->read()){
			if($this->isEndElement($elementName)){
				break;
			}
			if($this->isValue()){
				if($elementCollection->isEmpty()){
					return trim($this->reader->value);
				} else {
					return $elementCollection->put($elementName, trim($this->reader->value));
				}
			}
			if($this->isElement()){
				if($couldBeAnElementsList){
					$foundElementName = $this->reader->name;
					$elementCollection->push(new Collection($this->extractElement($foundElementName)));
				} else {
					$foundElementName = $this->reader->name;
					$elementCollection->put($foundElementName, $this->extractElement($foundElementName, true));
				}
			}
		}

		return $elementCollection;
	}

	private function getCurrentElementAttributes(){
		$attributes = new Collection();
		if($this->reader->hasAttributes)  {
			while($this->reader->moveToNextAttribute()) {
				$attributes->put($this->reader->name, $this->reader->value);
			}
		}
		return $attributes;
	}

	private function start()
	{
		$this->reader = new XMLReader();
		$this->reader->open($this->source);

		return $this;
	}

	private function stop()
	{
		if( ! $this->reader->close()){
			throw new IncompleteParseException();
		}
	}

	private function shouldBeSkipped(){
		if($this->skipFirstElement){
			$this->skipFirstElement = false;
			return true;
		}

		return false;
	}

	private function isElement(String $elementName = null){
		if($elementName){
			return $this->reader->nodeType == XMLReader::ELEMENT && $this->reader->name === $elementName;
		} else {
			return $this->reader->nodeType == XMLReader::ELEMENT;
		}
	}

	private function isEndElement(String $elementName = null){
		if($elementName){
			return $this->reader->nodeType == XMLReader::END_ELEMENT && $this->reader->name === $elementName;
		} else {
			return $this->reader->nodeType == XMLReader::END_ELEMENT;
		}
	}

	private function isValue(){
		return $this->reader->nodeType == XMLReader::TEXT || $this->reader->nodeType === XMLReader::CDATA;
	}
}
