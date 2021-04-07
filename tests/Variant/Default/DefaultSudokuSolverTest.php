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

    public function testSolveNonAnswers(): void
    {
        $gridSize = new GridSize(3, 3);
        $subGridSize = new GridSize(1, 1);
        $grid = new Grid($gridSize, $subGridSize);
        $sudoku = new DefaultSudoku($grid);

        $this->expectException(SolverException::class);
        $this->expectExceptionMessage('The sudoku must have at least one answer.');

        $solver = new DefaultSudokuSolver();
        $solver->solve($sudoku);
    }

    public function testSolveNonSquare(): void
    {
        $gridSize = new GridSize(3, 6);
        $subGridSize = new GridSize(1, 3);
        $grid = new Grid($gridSize, $subGridSize);
        $sudoku = new DefaultSudoku($grid);
        $sudoku->setAnswer(1, 1, 1);

        $this->expectException(SolverException::class);
        $this->expectExceptionMessage('This solver only supports square sudoku grids.');

        $solver = new DefaultSudokuSolver();
        $solver->solve($sudoku);
    }

    public function testSolveInvalid(): void
    {
        $gridSize = new GridSize(4, 4);
        $subGridSize = new GridSize(2, 2);
        $grid = new Grid($gridSize, $subGridSize);
        $sudoku = new DefaultSudoku($grid);
        $sudoku->setAnswer(1, 1, 1);
        $sudoku->setAnswer(2, 2, 1);

        $this->expectException(SolverException::class);

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
    public function testSolveSmallNonSquareSubGrid(): void
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

    /**
     * A large sudoku with non-square sub-grids (12x12 grid, 3x4 subgrids).
     */
    public function testSolveLargeNonSquareSubGrid(): void
    {
        $gridSize = new GridSize(12, 12);
        $subGridSize = new GridSize(3, 4);
        $grid = new Grid($gridSize, $subGridSize);
        $sudoku = new DefaultSudoku($grid);

        $solutions = [
        ];
        $answers = [
            [null, 8, 4, 6, 1, null, null, 11, null, 7, null, 3],
            [7, null, null, 10, null, 4, null, 5, null, 12, 2, null],
            [null, 2, null, 1, null, 9, null, null, 6, null, null, 5],
            [9, 6, null, null, 12, null, 1, null, 8, 4, 11, null],
            [8, null, null, null, null, null, 4, 10, null, null, null, null],
            [null, 7, 1, 5, 11, null, null, 2, null, 9, 10, null],
            [10, null, null, 2, 3, 12, null, 9, null, 1, null, null],
            [null, null, 11, null, null, 6, 10, null, null, 5, null, 7],
            [null, 12, null, null, null, 7, null, null, 9, 11, 3, null],
            [null, 4, 5, null, 8, 10, null, null, 11, null, null, 6],
            [12, 3, null, null, 2, null, 5, null, 7, null, 8, null],
            [1, null, 9, null, null, null, 3, 6, 12, 2, null, null],
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
     * A large sudoku with non-square sub-grids (15x15 grid, 3x5 subgrids).
     */
//    public function testSolveHugeNonSquareSubGrid(): void
//    {
//        $gridSize = new GridSize(15, 15);
//        $subGridSize = new GridSize(3, 5);
//        $grid = new Grid($gridSize, $subGridSize);
//        $sudoku = new DefaultSudoku($grid);
//
//        $solutions = [
//            [8, 3, 4, 12, 13, 14, 5, 2, 7, 1, 9, 15, 6, 10, 11],
//            [15, 2, 11, 5, 9, 6, 13, 8, 10, 4, 3, 14, 12, 1, 7],
//            [10, 6, 1, 7, 14, 3, 9, 11, 15, 12, 5, 4, 13, 2, 8],
//            [1, 9, 12, 14, 5, 4, 10, 7, 3, 2, 11, 13, 8, 15, 6],
//            [6, 15, 3, 13, 8, 5, 1, 9, 12, 11, 7, 2, 10, 14, 4],
//            [11, 7, 10, 4, 2, 8, 15, 13, 6, 14, 12, 1, 9, 3, 5],
//            [12, 1, 8, 10, 4, 13, 14, 3, 5, 7, 2, 6, 11, 9, 15],
//            [3, 14, 7, 6, 11, 1, 2, 15, 9, 8, 4, 10, 5, 12, 13],
//            [5, 13, 2, 9, 15, 11, 12, 10, 4, 6, 1, 3, 7, 8, 14],
//            [2, 12, 14, 11, 7, 15, 6, 4, 8, 10, 13, 9, 1, 5, 3],
//            [4, 5, 9, 15, 1, 7, 3, 12, 2, 13, 6, 8, 14, 11, 10],
//            [13, 10, 6, 8, 3, 9, 11, 1, 14, 5, 15, 12, 4, 7, 2],
//            [14, 11, 15, 1, 10, 12, 7, 6, 13, 3, 8, 5, 2, 4, 9],
//            [9, 8, 13, 2, 12, 10, 4, 5, 11, 15, 14, 7, 3, 6, 1],
//            [7, 4, 5, 3, 6, 2, 8, 14, 1, 9, 10, 11, 15, 13, 12],
//        ];
//        $answers = [
//            [null, null, null, null, 13, null, null, null, 7, 1, null, 15, null, 10, null],
//            [null, null, 11, 5, null, null, null, null, null, 4, null, null, 12, null, 7],
//            [10, 6, null, 7, null, 3, 9, null, null, null, null, null, null, 2, 8],
//            [1, null, null, 14, 5, null, 10, null, 3, null, 11, 13, 8, null, null],
//            [null, null, null, null, null, 5, 1, null, null, 11, null, null, 10, 14, null],
//            [null, null, 10, null, null, 8, 15, 13, null, null, 12, null, null, null, 5],
//            [12, null, 8, 10, null, null, null, 3, null, null, 2, null, null, null, 15],
//            [null, 14, null, null, null, null, 2, 15, 9, null, null, null, null, 12, null],
//            [5, null, null, null, 15, null, null, 10, null, null, null, 3, 7, null, 14],
//            [2, null, null, null, 7, null, null, 4, 8, 10, null, null, 1, null, null],
//            [null, 5, 9, null, null, 7, null, null, 2, 13, null, null, null, null, null],
//            [null, null, 6, 8, 3, null, 11, null, 14, null, 15, 12, null, null, 2],
//            [14, 11, null, null, null, 12, null, null, 13, 3, null, 5, null, 4, 9],
//            [9, null, 13, null, null, null, 4, null, null, null, null, 7, 3, null, null],
//            [null, 4, null, 3, null, 2, 8, null, null, null, 10, null, null, null, null],
//        ];
//        foreach ($answers as $row => $columns) {
//            foreach ($columns as $column => $answer) {
//                $sudoku->setAnswer($row + 1, $column + 1, $answer);
//            }
//        }
//
//        $solver = new DefaultSudokuSolver();
//        $solver->solve($sudoku);
//
//        $this->assertTrue($sudoku->isFullyAnswered());
//
//        foreach ($solutions as $row => $columns) {
//            foreach ($columns as $column => $solution) {
//                $this->assertSame($solution, $sudoku->getAnswer($row + 1, $column + 1));
//            }
//        }
//    }
}
