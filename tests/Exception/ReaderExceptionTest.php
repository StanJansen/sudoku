<?php

namespace Stanjan\Sudoku\Tests\Exception;

use PHPUnit\Framework\TestCase;
use Stanjan\Sudoku\Exception\ReaderException;
use Stanjan\Sudoku\Exception\SudokuExceptionInterface;

/**
 * @covers \Stanjan\Sudoku\Exception\ReaderException
 */
final class ReaderExceptionTest extends TestCase
{
    public function testConstruct(): void
    {
        $exception = new ReaderException();
        
        $this->assertTrue($exception instanceof SudokuExceptionInterface);
        $this->assertTrue($exception instanceof \RuntimeException);
    }
}
