<?php

namespace Stanjan\Sudoku\Tests\Grid;

use PHPUnit\Framework\TestCase;
use Stanjan\Sudoku\Exception\InvalidGridException;
use Stanjan\Sudoku\Grid\GridSize;

/**
 * @covers GridSize::class
 */
final class GridSizeTest extends TestCase
{
    public function testConstruct(): void
    {
        $gridSize = new GridSize(1, 2);
        
        $this->assertSame(1, $gridSize->getRowCount());
        $this->assertSame(2, $gridSize->getColumnCount());
    }

    public function testNegativeRowCount(): void
    {
        $this->expectException(InvalidGridException::class);
        $this->expectExceptionMessage('The amount of rows must be greater than 0, -1 given.');

        new GridSize(-1, 2);
    }

    public function testNegativeColumnCount(): void
    {
        $this->expectException(InvalidGridException::class);
        $this->expectExceptionMessage('The amount of columns must be greater than 0, -2 given.');

        new GridSize(1, -2);
    }
}