<?php

namespace Rodenastyle\StreamParser\Extensions;

use Rodenastyle\StreamParser\Exceptions\UndefinedCallbackException;
use Rodenastyle\StreamParser\StreamParserInterface;

/**
 * Created by PhpStorm.
 * User: Loïc Gouttefangeas <loic.gouttefangeas.pro@gmail.com>
 * Date: 25/08/2018
 * Time: 20:59
 */
class ChunkExtension implements \Rodenastyle\StreamParser\StreamParserInterface
{
    const DEFAULT_CHUNK_SIZE = 5000;

    /**
     * @var \Rodenastyle\StreamParser\StreamParserInterface
     */
    private $parser;
    private $iterationCount = 0;
    private $chunkSize;
    /** @var  callable $onChunkCompleteCallback */
    private $onChunkCompleteCallback;

    public function __construct(\Rodenastyle\StreamParser\StreamParserInterface $parser, $chunkSize = self::DEFAULT_CHUNK_SIZE)
    {
        $this->parser = $parser;
        $this->chunkSize = $chunkSize;
    }

    public function from(String $source): \Rodenastyle\StreamParser\StreamParserInterface
    {
        return $this->parser->from($source);
    }

    public function eachChunk(callable $function): StreamParserInterface
    {
        $this->onChunkCompleteCallback = $function;
        return $this;
    }

    public function each(callable $function)
    {
        if ($this->onChunkCompleteCallback === null) {
            throw new UndefinedCallbackException("No callback is set on chunk complete");
        }
        // decorate function to apply both its behavior and the chunk process
        $function = function ($collection) use ($function) {
            $this->iterationCount++;
            $function($collection);

            if ($this->iterationCount % $this->chunkSize === 0) {
                $this->executeChunkCompleteCallback();
            }
        };
        $this->parser->each($function);

        // make sure no entry is left out
        $this->processLastEntries();
        return $this;
    }

    /**
     * @param $this
     */
    protected function executeChunkCompleteCallback()
    {
        $this->iterationCount = 0;
        ($this->onChunkCompleteCallback)();
    }

    protected function processLastEntries()
    {
        if ($this->iterationCount % $this->chunkSize !== 0) {
            $this->executeChunkCompleteCallback();
        }
    }


    /**
     * @param int $chunkSize
     * @return ChunkExtension
     */
    public function setChunkSize(int $chunkSize): ChunkExtension
    {
        $this->chunkSize = $chunkSize;
        return $this;
    }

    /**
     * @return \Rodenastyle\StreamParser\StreamParserInterface
     */
    public function getParser(): \Rodenastyle\StreamParser\StreamParserInterface
    {
        return $this->parser;
    }
}