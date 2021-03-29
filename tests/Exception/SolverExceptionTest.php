<?php

namespace Stanjan\Sudoku\Tests\Exception;

use PHPUnit\Framework\TestCase;
use Stanjan\Sudoku\Exception\SolverException;
use Stanjan\Sudoku\Exception\SudokuExceptionInterface;

/**
 * @covers \Stanjan\Sudoku\Exception\SolverException
 */
final class SolverExceptionTest extends TestCase
{
    public function testConstruct(): void
    {
        $exception = new SolverException();
        
        $this->assertTrue($exception instanceof SudokuExceptionInterface);
        $this->assertTrue($exception instanceof \RuntimeException);
    }
}
