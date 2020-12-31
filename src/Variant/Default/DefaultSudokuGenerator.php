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
     * The amount of times the generator will try to generate the answers of the sudoku.
     */
    protected int $retryAnswersLimit = self::DEFAULT_RETRY_ANSWERS_LIMIT;

    /**
     * {@inheritdoc}
     */
    public static function getVariantClassName(): string
    {
        return DefaultSudokuVariant::class;
    }

    /**
     * Overrides the retry answers limit.
     */
    public function setRetryAnswersLimit(int $retryAnswersLimit): void
    {
        $this->retryAnswersLimit = $retryAnswersLimit;
    }

    /**
     * {@inheritdoc}
     */
    public function generate(): SudokuInterface
    {
        $sudoku = $this->createBaseSudoku();

        // Generate the answers for the sudoku, try until succeeded or the limit is reached.
        $answered = false;
        $attempt = 0;
        while ($answered !== true) {
            // Throw an error if generating the answers fails more than the limit.
            if ($attempt++ > $this->retryAnswersLimit) {
                throw new GeneratorException(sprintf('Generator retry limit of %d exceeded.', $this->retryAnswersLimit));
            }

            try {
                $this->generateAnswers($sudoku);
                $answered = true;
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
     * Generates and sets all answers for the given sudoku.
     *
     * @throws GeneratorException When the answers generation attempt failed.
     */
    protected function generateAnswers(DefaultSudoku $sudoku): void
    {
        // Index all possible answers.
        $gridSize = $sudoku->getGrid()->getSize();
        $subGridSize = $sudoku->getGrid()->getSubGridSize();
        $highestAnswer = $subGridSize->getRowCount() * $subGridSize->getColumnCount();
        $possibleAnswers = range(1,$highestAnswer);
        $possibleAnswers = array_combine($possibleAnswers, $possibleAnswers); // Make sure the key is the same as the value for unsetting.

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
                            // Clone all possible answers.
                            $currentPossibleAnswers = $possibleAnswers;

                            // Remove previous answers from the same row.
                            for ($previousColumn = 1; $previousColumn < $column; $previousColumn++) {
                                unset($currentPossibleAnswers[$sudoku->getAnswer($row, $previousColumn)]);
                            }

                            // Remove previous answers from the same column index.
                            for ($previousRow = 1; $previousRow < $row; $previousRow++) {
                                unset($currentPossibleAnswers[$sudoku->getAnswer($previousRow, $column)]);
                            }

                            // Remove previous answers from the same subgrid.
                            $previousColumnBase = $column - ($column - 1) % $subGridSize->getColumnCount();
                            for ($previousRow = $row - ($row - 1) % $subGridSize->getRowCount(); $previousRow <= $row; $previousRow++) {
                                for ($previousColumn = $previousColumnBase; $previousColumn < $previousColumnBase + $subGridSize->getColumnCount(); $previousColumn++) {
                                    unset($currentPossibleAnswers[$sudoku->getAnswer($previousRow, $previousColumn)]);
                                }
                            }

                            if (count($currentPossibleAnswers) === 0) {
                                // There are no possible answers.
                                $attempt++;
                                continue 3;
                            }

                            // Pick a random answer.
                            $answer = (int) array_rand($currentPossibleAnswers);

                            $sudoku->setAnswer($row, $column, $answer);
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