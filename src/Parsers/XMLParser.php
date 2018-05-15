<?php
/**
 * Created by PhpStorm.
 * User: sergio.rodenas
 * Date: 12/5/18
 * Time: 0:09
 */

namespace Rodenastyle\StreamParser\Parsers;


use Rodenastyle\StreamParser\Exceptions\IncompleteParseException;
use Rodenastyle\StreamParser\StreamParserInterface;
use XMLReader;
use Illuminate\Support\Collection;


class XMLParser implements StreamParserInterface
{
	protected $reader,$source;

	protected $skipFirstElement = true;

	public function from(String $source): StreamParserInterface
	{
		$this->source = $source;

		return $this;
	}

	public function each(callable $function)
	{
		$this->start();
		while($this->reader->read()){
			$this->searchElement($function);
		}
		$this->stop();
	}

	public function withoutSkippingFirstElement(){
		$this->skipFirstElement = false;
	}

	private function searchElement(callable $function)
	{
		if($this->isElement() && ! $this->shouldBeSkipped()){
			$function($this->extractElement($this->reader->name));
		}
	}

	private function extractElement(String $elementName)
	{
		$elementCollection = (new Collection())->merge($this->getCurrentElementAttributes());

		while($this->reader->read()){
			if($this->isEndElement($elementName)){
				break;
			}
			if($this->isElement()){
				$foundElementName = $this->reader->name;
				$elementCollection->put($foundElementName, $this->extractElement($foundElementName));
			}
			if($this->isValue()){
				$elementCollection = $this->reader->value;
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
		return $this->reader->nodeType == XMLReader::CDATA || $this->reader->nodeType == XMLReader::TEXT;
	}
}