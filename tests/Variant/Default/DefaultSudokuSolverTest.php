<?php

namespace Stanjan\Sudoku\Tests\Variant\Default;

use PHPUnit\Framework\TestCase;
use Stanjan\Sudoku\Exception\SolverException;
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

    public function testSolveNonSquare(): void
    {
        $gridSize = new GridSize(3, 6);
        $subGridSize = new GridSize(1, 3);
        $grid = new Grid($gridSize, $subGridSize);
        $sudoku = new DefaultSudoku($grid);

        $this->expectException(SolverException::class);
        $this->expectExceptionMessage('This solver only supports square sudoku grids.');

        $solver = new DefaultSudokuSolver();
        $solver->solve($sudoku);
    }

    public function testSolveInvalid(): void
    {
        $gridSize = new GridSize(3, 3);
        $subGridSize = new GridSize(1, 1);
        $grid = new Grid($gridSize, $subGridSize);
        $sudoku = new DefaultSudoku($grid);

        $this->expectException(SolverException::class);
        $this->expectExceptionMessage('No answer could be generated.');

        $solver = new DefaultSudokuSolver();
        $solver->solve($sudoku);
    }

    /**
     * A normal sudoku (9x9 grid, 3x3 subgrids).
     */
    public function testSolve(): void
    {
        $gridSize = new GridSize(9, 9);
        $subGridSize = new GridSize(3, 3);
        $grid = new Grid($gridSize, $subGridSize);
        $sudoku = new DefaultSudoku($grid);

        $solutions = [
            [9, 7, 3, 5, 8, 1, 4, 2, 6],
            [5, 2, 6, 4, 7, 3, 1, 9, 8],
            [1, 8, 4, 2, 9, 6, 7, 5, 3],
            [2, 4, 7, 8, 6, 5, 3, 1, 9],
            [3, 9, 8, 1, 2, 4, 6, 7, 5],
            [6, 5, 1, 7, 3, 9, 8, 4, 2],
            [8, 1, 9, 3, 4, 2, 5, 6, 7],
            [7, 6, 5, 9, 1, 8, 2, 3, 4],
            [4, 3, 2, 6, 5, 7, 9, 8, 1],
        ];
        $answers = [
            [9, 7, null, null, null, null, 4, 2, null],
            [null, 2, null, 4, null, 3, null, null, 8],
            [1, 8, null, null, null, null, null, null, 3],
            [null, null, null, null, 6, null, null, 1, null],
            [3, null, null, 1, null, 4, null, null, 5],
            [null, 5, null, null, 3, null, null, null, null],
            [8, null, null, null, null, null, null, 6, 7],
            [7, null, null, 9, null, 8, null, 3, null],
            [null, 3, 2, null, null, null, null, 8, 1],
        ];
        foreach ($answers as $row => $columns) {
            foreach ($columns as $column => $answer) {
                $sudoku->setAnswer($row + 1, $column + 1, $answer);
            }
        }

        $solver = new DefaultSudokuSolver();
        $solver->solve($sudoku);

        $this->assertTrue($sudoku->isFullyAnswered());

        foreach ($solutions as $row => $columns) {
            foreach ($columns as $column => $solution) {
                $this->assertSame($solution, $sudoku->getAnswer($row + 1, $column + 1));
            }
        }
    }

    /**
     * A smaller sudoku with non-square sub-grids (6x6 grid, 2x3 subgrids).
     */
    public function testSolveNonSquareSubGrid(): void
    {
        $gridSize = new GridSize(6, 6);
        $subGridSize = new GridSize(2, 3);
        $grid = new Grid($gridSize, $subGridSize);
        $sudoku = new DefaultSudoku($grid);

        $solutions = [
            [4, 2, 3, 5, 1, 6],
            [5, 6, 1, 3, 2, 4],
            [1, 5, 4, 2, 6, 3],
            [2, 3, 6, 4, 5, 1],
            [3, 1, 2, 6, 4, 5],
            [6, 4, 5, 1, 3, 2],
        ];
        $answers = [
            [null, null, 3, null, 1, null],
            [5, 6, null, 3, 2, null],
            [null, 5, 4, 2, null, 3],
            [2, null, 6, 4, 5, null],
            [null, 1, 2, null, 4, 5],
            [null, 4, null, 1, null, null],
        ];
        foreach ($answers as $row => $columns) {
            foreach ($columns as $column => $answer) {
                $sudoku->setAnswer($row + 1, $column + 1, $answer);
            }
        }

        $solver = new DefaultSudokuSolver();
        $solver->solve($sudoku);

        $this->assertTrue($sudoku->isFullyAnswered());

        foreach ($solutions as $row => $columns) {
            foreach ($columns as $column => $solution) {
                $this->assertSame($solution, $sudoku->getAnswer($row + 1, $column + 1));
            }
        }
    }
}
