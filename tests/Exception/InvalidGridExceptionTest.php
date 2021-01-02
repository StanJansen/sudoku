<?php

namespace Stanjan\Sudoku\Tests\Exception;

use PHPUnit\Framework\TestCase;
use Stanjan\Sudoku\Exception\InvalidGridException;
use Stanjan\Sudoku\Exception\SudokuExceptionInterface;

/**
 * @covers \Stanjan\Sudoku\Exception\InvalidGridException
 */
final class InvalidGridExceptionTest extends TestCase
{
    public function testConstruct(): void
    {
        $exception = new InvalidGridException();
        
        $this->assertTrue($exception instanceof SudokuExceptionInterface);
        $this->assertTrue($exception instanceof \InvalidArgumentException);
    }
}