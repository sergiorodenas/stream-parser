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
use Tightenco\Collect\Support\Collection;
use XMLReader;


class XMLParser implements StreamParserInterface
{
	protected $reader,$source;

	protected $skipFirstElement = true;

    protected $separateParameters = false;

    protected $extractElementName = false;

	public function from(String $source): StreamParserInterface
	{
		$this->source = $source;

		return $this;
	}

	public function withSeparatedParametersList(){
		$this->separateParameters = true;

		return $this;
	}

    /**
     * Extract and put element name in __name
     */
    public function withElementName(){
        $this->extractElementName = true;

        return $this;
    }

	public function withoutSkippingFirstElement(){
		$this->skipFirstElement = false;

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

	private function searchElement(callable $function)
	{
		if($this->isElement() && ! $this->shouldBeSkipped()){
			$function($this->extractElement($this->reader->name, false, $this->reader->depth), $this->reader->name);
		}
	}

	private function extractElement(String $elementName, $couldBeAnElementsList = false, int $parentDepth, string $foundInEl = null)
	{
		$emptyElement = $this->isEmptyElement($elementName);
		
		$elementCollection = new Collection();

		$elementParameters = $this->getCurrentElementAttributes();
		if($this->separateParameters){
			$elementCollection->put('__params', $elementParameters);
		} else {
			$elementCollection = $elementCollection->merge($elementParameters);
		}

        if($this->extractElementName && $parentDepth === 1){
            $elementCollection->put('__name', $elementName);
        }

		if($emptyElement) {
			return $elementCollection;
		}

		while($this->reader->read()) {
			if($this->isEndElement($elementName) && $this->reader->depth === $parentDepth) {
				break;
			}
			if($this->isValue()) {
				if($elementCollection->isEmpty()) {
				    if (!is_null($foundInEl)) {
                        		return $elementCollection->put($elementName, trim($this->reader->value));
                   		    }

				    return trim($this->reader->value);
				} else {
					return $elementCollection->put($elementName, trim($this->reader->value));
				}
			}
			if($this->isElement()) {
				if($couldBeAnElementsList) {
					$foundElementName = $this->reader->name;
					$elementCollection->push(new Collection($this->extractElement($foundElementName, false, $this->reader->depth, $elementName)));
				} else {
					$foundElementName = $this->reader->name;
					$elementCollection->put($foundElementName, $this->extractElement($foundElementName, true, $this->reader->depth));
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

	private function isEmptyElement(String $elementName = null){
		if($elementName) {
			return $this->reader->isEmptyElement && $this->reader->name === $elementName;
		} else {
			return false;
		}
	}
}
