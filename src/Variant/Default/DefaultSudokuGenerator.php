<?php

namespace Stanjan\Sudoku\Variant\Default;

use Stanjan\Sudoku\Exception\GeneratorException;
use Stanjan\Sudoku\Grid\Grid;
use Stanjan\Sudoku\Grid\GridSize;
use Stanjan\Sudoku\SudokuGeneratorInterface;
use Stanjan\Sudoku\SudokuInterface;

class DefaultSudokuGenerator implements SudokuGeneratorInterface
{
    const DEFAULT_RETRY_ANSWERS_LIMIT = 25_000;

    /**
     * The amount of times the generator will try to generate the solutions of the sudoku.
     */
    protected int $retrySolutionsLimit = self::DEFAULT_RETRY_ANSWERS_LIMIT;

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
    public function generate(): SudokuInterface
    {
        $sudoku = $this->createBaseSudoku();

        // Generate the solutions for the sudoku, try until succeeded or the limit is reached.
        $solutioned = false;
        $attempt = 0;
        while ($solutioned !== true) {
            // Throw an error if generating the solutions fails more than the limit.
            $attempt++;
            if ($attempt > $this->retrySolutionsLimit) {
                throw new GeneratorException(sprintf('Generator retry limit of %d exceeded.', $this->retrySolutionsLimit));
            }

            try {
                $this->generateSolutions($sudoku);
                $solutioned = true;
            } catch (GeneratorException) {
                // The generation failed.
            }
        }

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
        $possibleSolutions = range(1,$highestSolution);
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
}