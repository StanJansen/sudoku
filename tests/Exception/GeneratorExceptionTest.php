<?php

namespace Stanjan\Sudoku\Tests\Exception;

use PHPUnit\Framework\TestCase;
use Stanjan\Sudoku\Exception\GeneratorException;
use Stanjan\Sudoku\Exception\SudokuExceptionInterface;

/**
 * @covers \Stanjan\Sudoku\Exception\GeneratorException
 */
final class GeneratorExceptionTest extends TestCase
{
    public function testConstruct(): void
    {
        $exception = new GeneratorException();
        
        $this->assertTrue($exception instanceof SudokuExceptionInterface);
        $this->assertTrue($exception instanceof \RuntimeException);
    }
}