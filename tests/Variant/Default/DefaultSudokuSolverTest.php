<?php

namespace Stanjan\Sudoku\Tests\Variant\Default;

use PHPUnit\Framework\TestCase;
use Stanjan\Sudoku\Grid\Grid;
use Stanjan\Sudoku\Grid\GridSize;
use Stanjan\Sudoku\Variant\Default\DefaultSudoku;
use Stanjan\Sudoku\Variant\Default\DefaultSudokuSolver;
use Stanjan\Sudoku\Variant\Default\DefaultSudokuVariant;

/**
 * @covers \Stanjan\Sudoku\Variant\Default\DefaultSudokuSolver
 */
final class DefaultSudokuSolverTest extends TestCase
{
    public function testGetVariantClassName(): void
    {
        $this->assertSame(DefaultSudokuVariant::class, DefaultSudokuSolver::getVariantClassName());
    }
    
    public function testSolve(): void
    {
        $gridSize = new GridSize(1, 2);
        $subGridSize = new GridSize(1, 2);

        $grid = new Grid($gridSize, $subGridSize);

        $sudoku = new DefaultSudoku($grid);

        $solver = new DefaultSudokuSolver();

        $solver->solve($sudoku);

        // TODO: Create test after the solve method of the solver is implemented.
        $this->expectNotToPerformAssertions();
    }
}