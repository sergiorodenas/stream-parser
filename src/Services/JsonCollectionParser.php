<?php
/**
 * Created by PhpStorm.
 * User: sergio.rodenas
 * Date: 15/5/18
 * Time: 19:58
 */

namespace Rodenastyle\StreamParser\Services;


use JsonCollectionParser\Parser;
use Rodenastyle\StreamParser\Exceptions\IncompleteParseException;
use JsonCollectionParser\Listener;
use JsonStreamingParser\Parser as StreamingParser;

class JsonCollectionParser extends Parser
{
	/**
	 * @param string $filePath Source file path
	 * @param callback|callable $itemCallback Callback
	 * @param bool $assoc Parse as associative arrays
	 *
	 * @throws \Exception
	 */
	public function parse($filePath, $itemCallback, $assoc = true): void
	{
		$this->checkCallback($itemCallback);

		$stream = $this->openFile($filePath);

		try {
			$listener = new Listener($itemCallback, $assoc);
			$this->parser = new StreamingParser(
				$stream,
				$listener,
				$this->getOption('line_ending'),
				$this->getOption('emit_whitespace')
			);
			$this->parser->parse();
		} catch (\Exception $e) {
			fclose($stream);
			throw $e;
		}

		if( ! fclose($stream)){
			throw new IncompleteParseException();
		}
	}

	/**
	 * @param string $filePath
	 *
	 * @return resource
	 * @throws \Exception
	 */
	protected function openFile($filePath)
	{
		$stream = @fopen($filePath, 'r');
		if (false === $stream) {
			throw new \Exception('Unable to open file for read: ' . $filePath);
		}

		return $stream;
	}
}
