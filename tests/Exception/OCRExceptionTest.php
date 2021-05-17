<?php

namespace Stanjan\Sudoku\Tests\Exception;

use PHPUnit\Framework\TestCase;
use Stanjan\Sudoku\Exception\OCRException;

/**
 * @covers \Stanjan\Sudoku\Exception\OCRException
 */
final class OCRExceptionTest extends TestCase
{
    public function testConstruct(): void
    {
        $exception = new OCRException();
        
        $this->assertTrue($exception instanceof \RuntimeException);
    }
}
