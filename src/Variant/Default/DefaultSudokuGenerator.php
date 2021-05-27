<?php

namespace Stanjan\Sudoku\Variant\Default;

use Stanjan\Sudoku\Exception\GeneratorException;
use Stanjan\Sudoku\Exception\SolverException;
use Stanjan\Sudoku\Grid\Grid;
use Stanjan\Sudoku\Grid\GridSize;
use Stanjan\Sudoku\SudokuGeneratorInterface;

class DefaultSudokuGenerator implements SudokuGeneratorInterface
{
    const DEFAULT_RETRY_SOLUTIONS_LIMIT = 25_000;

    /**
     * The amount of times the generator will try to generate the solutions of the sudoku.
     */
    protected int $retrySolutionsLimit = self::DEFAULT_RETRY_SOLUTIONS_LIMIT;

    /**
     * {@inheritdoc}
     */
    public static function getVariantClassName(): string
    {
        return DefaultSudokuVariant::class;
    }

    /**
     * Overrides the retry solutions limit.
     */
    public function setRetrySolutionsLimit(int $retrySolutionsLimit): void
    {
        $this->retrySolutionsLimit = $retrySolutionsLimit;
    }

    /**
     * {@inheritdoc}
     */
    public function generate(): DefaultSudoku
    {
        $sudoku = $this->createBaseSudoku();

        // Generate the solutions for the sudoku, try until succeeded or the limit is reached.
        $solutionsGenerated = false;
        $attempt = 0;
        while ($solutionsGenerated !== true) {
            // Throw an error if generating the solutions fails more than the limit.
            $attempt++;
            if ($attempt > $this->retrySolutionsLimit) {
                throw new GeneratorException(sprintf('Generator retry limit of %d exceeded.', $this->retrySolutionsLimit));
            }

            try {
                $this->generateSolutions($sudoku);
                $solutionsGenerated = true;
            } catch (GeneratorException) {
                // The generation failed.
            }
        }

        // Set the base answers of the sudoku.
        $this->setBaseAnswers($sudoku);

        // Clean up the answers of the sudoku.
        $this->cleanAnswers($sudoku);

        return $sudoku;
    }

    /**
     * Generates a base sudoku instance.
     */
    protected function createBaseSudoku(): DefaultSudoku
    {
        // Generate the grid.
        $gridSize = new GridSize(9, 9);
        $subGridSize = new GridSize(3, 3);
        $grid = new Grid($gridSize, $subGridSize);

        return new DefaultSudoku($grid);
    }

    /**
     * Generates and sets all solutions for the given sudoku.
     *
     * @throws GeneratorException When the solutions generation attempt failed.
     */
    protected function generateSolutions(DefaultSudoku $sudoku): void
    {
        // Index all possible solutions.
        $gridSize = $sudoku->getGrid()->getSize();
        $subGridSize = $sudoku->getGrid()->getSubGridSize();
        $highestSolution = $subGridSize->getRowCount() * $subGridSize->getColumnCount();
        $possibleSolutions = range(1, $highestSolution);
        $possibleSolutions = array_combine($possibleSolutions, $possibleSolutions); // Make sure the key is the same as the value for unsetting.

        // Generate all subgrids, base the max amount of generation attempts on the grid and subgrid size.
        $maxAttempts = ceil(($gridSize->getColumnCount() * $gridSize->getRowCount()) / ($subGridSize->getColumnCount() * $subGridSize->getRowCount()));
        for ($subGridHorizontal = 1; $subGridHorizontal <= $sudoku->getGrid()->getHorizontalSubGridCount(); $subGridHorizontal++) {
            $horizontalOffset = $subGridSize->getColumnCount() * $subGridHorizontal - $subGridSize->getColumnCount();
            for ($subGridVertical = 1; $subGridVertical <= $sudoku->getGrid()->getVerticalSubGridCount(); $subGridVertical++) {
                $verticalOffset = $subGridSize->getRowCount() * $subGridVertical - $subGridSize->getRowCount();

                // Attempt building the subgrid. We build subgrid by subgrid instead of row by row as it's more likely to succeed.
                $attempt = 1;
                while ($attempt < $maxAttempts) {
                    for ($row = 1 + $verticalOffset; $row <= $subGridSize->getRowCount() + $verticalOffset; $row++) {
                        for ($column = 1 + $horizontalOffset; $column <= $subGridSize->getColumnCount() + $horizontalOffset; $column++) {
                            // Clone all possible solutions.
                            $currentPossibleSolutions = $possibleSolutions;

                            // Remove previous solutions from the same row.
                            for ($previousColumn = 1; $previousColumn < $column; $previousColumn++) {
                                unset($currentPossibleSolutions[$sudoku->getSolution($row, $previousColumn)]);
                            }

                            // Remove previous solutions from the same column index.
                            for ($previousRow = 1; $previousRow < $row; $previousRow++) {
                                unset($currentPossibleSolutions[$sudoku->getSolution($previousRow, $column)]);
                            }

                            // Remove previous solutions from the same subgrid.
                            $previousColumnBase = $column - ($column - 1) % $subGridSize->getColumnCount();
                            for ($previousRow = $row - ($row - 1) % $subGridSize->getRowCount(); $previousRow <= $row; $previousRow++) {
                                for ($previousColumn = $previousColumnBase; $previousColumn < $previousColumnBase + $subGridSize->getColumnCount(); $previousColumn++) {
                                    unset($currentPossibleSolutions[$sudoku->getSolution($previousRow, $previousColumn)]);
                                }
                            }

                            if (count($currentPossibleSolutions) === 0) {
                                // There are no possible solutions, retry generating the subgrid.
                                $attempt++;
                                continue 3;
                            }

                            // Pick a random solution.
                            $solution = (int) array_rand($currentPossibleSolutions);

                            $sudoku->setSolution($row, $column, $solution);
                        }
                    }

                    break;
                }

                if ($attempt >= $maxAttempts) {
                    throw new GeneratorException(sprintf('Subgrid could not be generated after %d attempts.', $attempt));
                }
            }
        }
    }

    /**
     * Sets the base answers required to be able to solve the sudoku.
     */
    protected function setBaseAnswers(DefaultSudoku $sudoku): void
    {
        // Keep adding a random answer until the sudoku can be solved.
        $gridSize = $sudoku->getGrid()->getSize();
        $totalCells = $gridSize->getColumnCount() * $gridSize->getRowCount();
        $solver = new DefaultSudokuSolver();

        for ($answerCount = 0; $answerCount < $totalCells; $answerCount++) {
            // Do not try to solve the sudoku until at least 1/5th of the answers have been set.
            if ($answerCount >= ceil($totalCells / 5)) {
                // Try to solve a clone of the sudoku so the answers won't all be set.
                $sudokuClone = clone $sudoku;
                try {
                    $solver->solve($sudokuClone);
                    // The sudoku could be solved, do not add any more answers.
                    return;
                } catch (SolverException) {
                    // The sudoku could not be solved, add more answers.
                }
            }

            // Add a random answer to the sudoku.
            $this->addRandomAnswer($sudoku);
        }

        throw new GeneratorException('Setting the base answers to the given sudoku failed.');
    }

    /**
     * Adds a random answer to the given sudoku.
     */
    protected function addRandomAnswer(DefaultSudoku $sudoku): void
    {
        if ($sudoku->hasAllSolutions()) {
            throw new GeneratorException('Cannot add a random answer to a sudoku that has already been fully answered.');
        }

        // Pick a random cell.
        $gridSize = $sudoku->getGrid()->getSize();
        $row = rand(1, $gridSize->getRowCount());
        $column = rand(1, $gridSize->getColumnCount());

        // Retry if the answer for this cell has already been set correctly.
        $existingAnswer = $sudoku->getAnswer($row, $column);
        $solution = $sudoku->getSolution($row, $column);
        if ($existingAnswer === $solution) {
            $this->addRandomAnswer($sudoku);
            return;
        }

        // Set the answer.
        $sudoku->setAnswer($row, $column, $solution);
    }

    /**
     * Cleans answers from the sudoku that are not necessary.
     */
    protected function cleanAnswers(DefaultSudoku $sudoku): void
    {
        $gridSize = $sudoku->getGrid()->getSize();
        $solver = new DefaultSudokuSolver();

        // Try to remove every answered cell and check if the sudoku is still solvable.
        $rows = range(1, $gridSize->getRowCount());
        $columns = range(1, $gridSize->getColumnCount());
        // Shuffle the rows and columns so the removed order will always be random, otherwise the top left will always be more empty than bottom right.
        shuffle($rows);
        shuffle($columns);
        foreach ($rows as $row) {
            foreach ($columns as $column) {
                if (null === $sudoku->getAnswer($row, $column)) {
                    // This cell has not been answered, ignore.
                    continue;
                }

                // Clone the sudoku to test so the original will not be altered in case of a fail.
                $sudokuClone = clone $sudoku;
                // Remove the cell answer.
                $sudokuClone->setAnswer($row, $column, null);

                try {
                    $solver->solve($sudokuClone);
                    // The sudoku could still be solved without the cell answer, remove it on the original sudoku too.
                    $sudoku->setAnswer($row, $column, null);
                } catch (SolverException) {
                    // The sudoku could not be solved anymore, do not remove this answer on the original sudoku.
                }
            }
        }
    }
}
