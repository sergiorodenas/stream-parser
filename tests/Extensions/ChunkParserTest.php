<?php
/**
 * Created by PhpStorm.
 * User: Loïc Gouttefangeas <loic.gouttefangeas.pro@gmail.com>
 * Date: 25/08/2018
 * Time: 23:01
 */

namespace Rodenastyle\StreamParser\Test\Extensions;


use PHPUnit\Framework\TestCase;
use Rodenastyle\StreamParser\Exceptions\UndefinedCallbackException;
use Rodenastyle\StreamParser\Extensions\ChunkExtension;
use Rodenastyle\StreamParser\StreamParser;

class ChunkParserTest extends TestCase
{
    private $stub = __DIR__ . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "Stubs" . DIRECTORY_SEPARATOR . "sample.json";

    public function test_extension_does_not_break_base_functionnality()
    {
        $count = 0;
        $this->getParser()
            ->eachChunk(function(){})
            ->each(function () use (&$count) {
            $count++;
        });

        $this->assertEquals(5, $count);
    }

    public function test_chunk_is_triggered_correct_number_of_times()
    {
        $iterationCount = 0;

        $this->getParser()
            ->setChunkSize(5)
            ->eachChunk(function() use (& $iterationCount) {
                $iterationCount++;
            })
            ->each(function(){});
        $this->assertEquals(1, $iterationCount);
    }

    public function test_chunk_doesnt_forget_last_entries()
    {
        $iterationCount = 0;
        $this->getParser()
            ->setChunkSize(2)
            ->eachChunk(function() use (& $iterationCount) {
                $iterationCount++;
            })
            ->each(function(){});

        // 2 chunks of 2, one chunk of 1
        $this->assertEquals(3, $iterationCount);
    }



    public function test_it_throws_exception_if_no_callback_is_set()
    {
        $this->expectException(UndefinedCallbackException::class);
        $this->getParser()
            ->each(function(){});
    }

    /**
     * @return ChunkExtension
     */
    protected function getParser(): ChunkExtension
    {
        return StreamParser::chunk(StreamParser::json($this->stub));
    }
}