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
 * @covers \Stanjan\Sudoku\Variant\Default\Solver\PossibleAnswersCollection
 * @covers \Stanjan\Sudoku\Variant\Default\Solver\Method\UniqueRectangleMethod
 * @covers \Stanjan\Sudoku\Variant\Default\Solver\Method\XWingMethod
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

    /**
     * Test solving a cell by basic elimination of the same row.
     */
    public function testSolveBasicRow(): void
    {
        $gridSize = new GridSize(4, 4);
        $subGridSize = new GridSize(2, 2);
        $grid = new Grid($gridSize, $subGridSize);
        $sudoku = new DefaultSudoku($grid);

        $answers = [
            [1, 2, 3, null],
            [null, null, null, null],
            [null, null, null, null],
            [null, null, null, null],
        ];
        foreach ($answers as $row => $columns) {
            foreach ($columns as $column => $answer) {
                $sudoku->setAnswer($row + 1, $column + 1, $answer);
            }
        }

        $solver = new DefaultSudokuSolver();

        try {
            $solver->solve($sudoku);
            throw new \LogicException('The solver did not fail for some reason.');
        } catch (SolverException) {
            // The solver should fail as not all cells can be answered, we only care about row 1 column 4 being answered.
        }

        $this->assertSame(4, $sudoku->getAnswer(1, 4));
    }

    /**
     * Test solving a cell by basic elimination of the same column.
     */
    public function testSolveBasicColumn(): void
    {
        $gridSize = new GridSize(4, 4);
        $subGridSize = new GridSize(2, 2);
        $grid = new Grid($gridSize, $subGridSize);
        $sudoku = new DefaultSudoku($grid);

        $answers = [
            [1, null, null, null],
            [2, null, null, null],
            [3, null, null, null],
            [null, null, null, null],
        ];
        foreach ($answers as $row => $columns) {
            foreach ($columns as $column => $answer) {
                $sudoku->setAnswer($row + 1, $column + 1, $answer);
            }
        }

        $solver = new DefaultSudokuSolver();

        try {
            $solver->solve($sudoku);
            throw new \LogicException('The solver did not fail for some reason.');
        } catch (SolverException) {
            // The solver should fail as not all cells can be answered, we only care about row 4 column 1 being answered.
        }

        $this->assertSame(4, $sudoku->getAnswer(4, 1));
    }

    /**
     * Test solving a cell by basic elimination of the same subgrid.
     */
    public function testSolveBasicSubGrid(): void
    {
        $gridSize = new GridSize(4, 4);
        $subGridSize = new GridSize(2, 2);
        $grid = new Grid($gridSize, $subGridSize);
        $sudoku = new DefaultSudoku($grid);

        $answers = [
            [1, 2, null, null],
            [3, null, null, null],
            [null, null, null, null],
            [null, null, null, null],
        ];
        foreach ($answers as $row => $columns) {
            foreach ($columns as $column => $answer) {
                $sudoku->setAnswer($row + 1, $column + 1, $answer);
            }
        }

        $solver = new DefaultSudokuSolver();

        try {
            $solver->solve($sudoku);
            throw new \LogicException('The solver did not fail for some reason.');
        } catch (SolverException) {
            // The solver should fail as not all cells can be answered, we only care about row 2 column 2 being answered.
        }

        $this->assertSame(4, $sudoku->getAnswer(2, 2));
    }

    /**
     * Test solving a cell by being the only cell in the row with a possible answer.
     */
    public function testSolvePossibleAnswersRow(): void
    {
        $gridSize = new GridSize(4, 4);
        $subGridSize = new GridSize(2, 2);
        $grid = new Grid($gridSize, $subGridSize);
        $sudoku = new DefaultSudoku($grid);

        $answers = [
            [1, null, 3, null],
            [null, 4, null, null],
            [null, null, null, null],
            [null, null, null, null],
        ];
        foreach ($answers as $row => $columns) {
            foreach ($columns as $column => $answer) {
                $sudoku->setAnswer($row + 1, $column + 1, $answer);
            }
        }

        $solver = new DefaultSudokuSolver();

        try {
            $solver->solve($sudoku);
            throw new \LogicException('The solver did not fail for some reason.');
        } catch (SolverException) {
            // The solver should fail as not all cells can be answered, we only care about row 1 column 2 & 4 being answered.
        }

        $this->assertSame(2, $sudoku->getAnswer(1, 2));
        $this->assertSame(4, $sudoku->getAnswer(1, 4));
    }

    /**
     * Test solving a cell by being the only cell in the column with a possible answer.
     */
    public function testSolvePossibleAnswersColumn(): void
    {
        $gridSize = new GridSize(4, 4);
        $subGridSize = new GridSize(2, 2);
        $grid = new Grid($gridSize, $subGridSize);
        $sudoku = new DefaultSudoku($grid);

        $answers = [
            [1, null, null, null],
            [null, 4, null, null],
            [3, null, null, null],
            [null, null, null, null],
        ];
        foreach ($answers as $row => $columns) {
            foreach ($columns as $column => $answer) {
                $sudoku->setAnswer($row + 1, $column + 1, $answer);
            }
        }

        $solver = new DefaultSudokuSolver();

        try {
            $solver->solve($sudoku);
            throw new \LogicException('The solver did not fail for some reason.');
        } catch (SolverException) {
            // The solver should fail as not all cells can be answered, we only care about column 1 row 2 & 4 being answered.
        }

        $this->assertSame(2, $sudoku->getAnswer(2, 1));
        $this->assertSame(4, $sudoku->getAnswer(4, 1));
    }

    /**
     * Test solving a cell by being the only cell in the subgrid with a possible answer.
     */
    public function testSolvePossibleAnswersSubGrid(): void
    {
        $gridSize = new GridSize(9, 9);
        $subGridSize = new GridSize(3, 3);
        $grid = new Grid($gridSize, $subGridSize);
        $sudoku = new DefaultSudoku($grid);

        $answers = [
            [1, 2, 3, null, null, null],
            [null, 4, 5, null, null, null],
            [null, null, 6, null, null, 9],
            [null, null, null, null, null, null],
            [null, null, null, null, null, null],
            [null, null, null, null, null, null],
        ];
        foreach ($answers as $row => $columns) {
            foreach ($columns as $column => $answer) {
                $sudoku->setAnswer($row + 1, $column + 1, $answer);
            }
        }

        $solver = new DefaultSudokuSolver();

        try {
            $solver->solve($sudoku);
            throw new \LogicException('The solver did not fail for some reason.');
        } catch (SolverException) {
            // The solver should fail as not all cells can be answered, we only care about row 2 column 1 being answered.
        }

        $this->assertSame(9, $sudoku->getAnswer(2, 1));
    }

    /**
     * Test solving a cell by using the unique rectangle method.
     */
    public function testSolveUniqueRectangle(): void
    {
        $gridSize = new GridSize(9, 9);
        $subGridSize = new GridSize(3, 3);
        $grid = new Grid($gridSize, $subGridSize);
        $sudoku = new DefaultSudoku($grid);

        $answers = [
            [null, null, 3, 2, 7, 8, 1, null, 6],
            [8, 6, null, 1, null, null, null, 2, null],
            [2, 7, 1, 6, null, null, 8, null, null],
            [3, 8, 7, 9, 6, 5, 2, 1, 4],
            [6, 1, 9, 3, 4, 2, 5, 7, 8],
            [null, null, 2, 8, 1, 7, 6, 3, 9],
            [null, 3, 8, 4, 2, 1, null, 6, null],
            [null, 2, 6, 5, 9, 3, null, 8, 1],
            [1, null, null, 7, 8, 6, 3, null, 2],
        ];
        foreach ($answers as $row => $columns) {
            foreach ($columns as $column => $answer) {
                $sudoku->setAnswer($row + 1, $column + 1, $answer);
            }
        }

        $solver = new DefaultSudokuSolver();
        $solver->solve($sudoku);

        $this->assertSame(7, $sudoku->getAnswer(2, 9));
    }

    /**
     * Test solving a cell by using the X-Wing method.
     */
    public function testSolveXWing(): void
    {
        $gridSize = new GridSize(9, 9);
        $subGridSize = new GridSize(3, 3);
        $grid = new Grid($gridSize, $subGridSize);
        $sudoku = new DefaultSudoku($grid);

        $answers = [
            [6, null, 1, 7, 3, null, 4, null, null],
            [4, 9, null, null, null, 6, null, 7, 1],
            [7, null, null, 1, 4, 9, null, 6, null],
            [8, 1, 2, 6, 7, 4, 9, 3, 5],
            [3, 6, 7, 9, 5, 8, 1, 2, 4],
            [5, 4, 9, 2, 1, 3, 6, 8, 7],
            [null, 3, null, 4, 9, 7, null, null, 6],
            [9, 7, 6, null, null, 1, null, 4, 3],
            [null, null, 4, 3, 6, null, 7, null, null],
        ];
        foreach ($answers as $row => $columns) {
            foreach ($columns as $column => $answer) {
                $sudoku->setAnswer($row + 1, $column + 1, $answer);
            }
        }

        $solver = new DefaultSudokuSolver();
        $solver->solve($sudoku);

        $this->assertSame(2, $sudoku->getAnswer(3, 9));
    }
}
