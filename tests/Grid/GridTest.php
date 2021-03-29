<?php

namespace Stanjan\Sudoku\Tests\Grid;

use PHPUnit\Framework\TestCase;
use Stanjan\Sudoku\Exception\InvalidGridException;
use Stanjan\Sudoku\Grid\Grid;
use Stanjan\Sudoku\Grid\GridSize;

/**
 * @covers \Stanjan\Sudoku\Grid\Grid
 */
final class GridTest extends TestCase
{
    public function testConstruct(): void
    {
        $gridSize = new GridSize(6, 9);
        $subGridSize = new GridSize(3, 3);

        $grid = new Grid($gridSize, $subGridSize);

        $this->assertSame($gridSize, $grid->getSize());
        $this->assertSame($subGridSize, $grid->getSubGridSize());
        $this->assertSame(2, $grid->getVerticalSubGridCount());
        $this->assertSame(3, $grid->getHorizontalSubGridCount());
    }

    public function testGreaterSubGridRows(): void
    {
        $this->expectException(InvalidGridException::class);
        $this->expectExceptionMessage('The amount of rows (2) on the subgrid are higher than the amount of rows (1) on the base grid');

        $gridSize = new GridSize(1, 1);
        $subGridSize = new GridSize(2, 1);

        new Grid($gridSize, $subGridSize);
    }

    public function testGreaterSubGridColumns(): void
    {
        $this->expectException(InvalidGridException::class);
        $this->expectExceptionMessage('The amount of columns (2) on the subgrid are higher than the amount of columns (1) on the base grid');

        $gridSize = new GridSize(1, 1);
        $subGridSize = new GridSize(1, 2);

        new Grid($gridSize, $subGridSize);
    }

    public function testNonDivisibleRows(): void
    {
        $this->expectException(InvalidGridException::class);
        $this->expectExceptionMessage('The amount of rows (3) on the grid must be divisible by the amount of rows (2) on the subgrid.');

        $gridSize = new GridSize(3, 3);
        $subGridSize = new GridSize(2, 3);

        new Grid($gridSize, $subGridSize);
    }

    public function testNonDivisibleColumns(): void
    {
        $this->expectException(InvalidGridException::class);
        $this->expectExceptionMessage('The amount of columns (3) on the grid must be divisible by the amount of columns (2) on the subgrid.');

        $gridSize = new GridSize(3, 3);
        $subGridSize = new GridSize(3, 2);

        new Grid($gridSize, $subGridSize);
    }
}
