<?php

namespace Stanjan\Sudoku\Variant\Default\Solver\Method;

use Stanjan\Sudoku\SudokuInterface;
use Stanjan\Sudoku\Variant\Default\Solver\PossibleAnswersCollection;

/**
 * Attempts to find an answer using the unique rectangle technique.
 *
 * https://www.learn-sudoku.com/unique-rectangle.html
 */
class UniqueRectangleMethod implements SolverMethodInterface
{
    /**
     * {@inheritDoc}
     */
    public static function tryAddAnswer(SudokuInterface $sudoku, PossibleAnswersCollection $cachedPossibleAnswers): bool
    {
        $gridSize = $sudoku->getGrid()->getSize();

        // Try to find an answer for every non-answered cell.
        for ($row = 1; $row <= $gridSize->getRowCount() - 1; $row++) { // Ignore the last row as it needs a row after this for an unique rectangle.
            for ($column = 1; $column <= $gridSize->getColumnCount() - 1; $column++) { // Ignore the last column as it needs a column after this for an unique rectangle.
                if (null !== $sudoku->getAnswer($row, $column)) {
                    // This cell is already answered, ignore.
                    continue;
                }

                $firstColumnPossibleAnswers = self::getPossibleAnswersForCell($sudoku, $cachedPossibleAnswers, $row, $column);
                $firstColumnPossibleAnswersCount = count($firstColumnPossibleAnswers);
                $multipleAnswersCell = $firstColumnPossibleAnswersCount > 2 ? [$row, $column] : null; // Keeps track of the fact if one of the four columns has more than 2 possible answers.
                for ($otherColumn = $column + 1; $otherColumn <= $gridSize->getColumnCount(); $otherColumn++) {
                    $secondColumnPossibleAnswers = self::getPossibleAnswersForCell($sudoku, $cachedPossibleAnswers, $row, $otherColumn);
                    $secondColumnPossibleAnswersCount = count($secondColumnPossibleAnswers);
                    if ($secondColumnPossibleAnswersCount !== 2 && ($multipleAnswersCell || $secondColumnPossibleAnswersCount < 2)) {
                        // This cannot be an unique rectangle, it can only have 2 or one column with multiple possible answers.
                        continue;
                    }

                    $possibleAnswers = array_intersect($firstColumnPossibleAnswers, $secondColumnPossibleAnswers);
                    if (count($possibleAnswers) !== 2) {
                        // These columns do not have two of the same possible answers, it cannot form an unique rectangle.
                        continue;
                    }
                    if (!$multipleAnswersCell && $secondColumnPossibleAnswersCount > 2) {
                        $multipleAnswersCell = [$row, $otherColumn];
                    }

                    // This is a possible unique rectangle, check if there is another row it can form the rectangle with.
                    for ($otherRow = $row + 1; $otherRow <= $gridSize->getRowCount(); $otherRow++) {
                        $thirdColumnPossibleAnswers = self::getPossibleAnswersForCell($sudoku, $cachedPossibleAnswers, $otherRow, $column);
                        $thirdColumnPossibleAnswersCount = count($thirdColumnPossibleAnswers);
                        if (($thirdColumnPossibleAnswersCount !== 2 && ($multipleAnswersCell || $thirdColumnPossibleAnswersCount < 2))
                            || count(array_intersect($possibleAnswers, $thirdColumnPossibleAnswers)) !== 2
                        ) {
                            // This cannot be an unique rectangle, it can only have 2 or one column with multiple possible answers, it also must match the possible answers of the original row.
                            continue;
                        }
                        if (!$multipleAnswersCell && $thirdColumnPossibleAnswersCount > 2) {
                            $multipleAnswersCell = [$otherRow, $column];
                        }

                        $fourthColumnPossibleAnswers = self::getPossibleAnswersForCell($sudoku, $cachedPossibleAnswers, $otherRow, $otherColumn);
                        $fourthColumnPossibleAnswersCount = count($fourthColumnPossibleAnswers);
                        if (((!$multipleAnswersCell || $fourthColumnPossibleAnswersCount !== 2) && ($multipleAnswersCell || $fourthColumnPossibleAnswersCount < 2))
                            || count(array_intersect($possibleAnswers, $fourthColumnPossibleAnswers)) !== 2
                        ) {
                            // This cannot be an unique rectangle, it can only have 2 or one column with multiple possible answers, it also must match the possible answers of the original row.
                            // If all previous cells only have 2 possible answers, this cell also must have more than 2 possible answers.
                            continue;
                        }
                        if (!$multipleAnswersCell) {
                            $multipleAnswersCell = [$otherRow, $otherColumn];
                        }

                        // An unique rectangle has been found, remove the possible answers on the multiple answers column.
                        $cachedPossibleAnswers->setPossibleAnswers($multipleAnswersCell[0], $multipleAnswersCell[1], array_diff(
                            $cachedPossibleAnswers->getPossibleAnswers($multipleAnswersCell[0], $multipleAnswersCell[1]),
                            $possibleAnswers,
                        ));
                        $newPossibleAnswers = $cachedPossibleAnswers->getPossibleAnswers($multipleAnswersCell[0], $multipleAnswersCell[1]);
                        if (count($newPossibleAnswers) === 1) {
                            // The cell only has one possible answer left, set it.
                            $sudoku->setAnswer(
                                $multipleAnswersCell[0],
                                $multipleAnswersCell[1],
                                reset($newPossibleAnswers)
                            );
                            return true;
                        }
                    }
                }
            }
        }

        // Unique rectangle could not be applied.
        return false;
    }

    /**
     * @return array<int>
     */
    private static function getPossibleAnswersForCell(SudokuInterface $sudoku, PossibleAnswersCollection $cachedPossibleAnswers, int $row, int $column): array
    {
        if ($existingAnswer = $sudoku->getAnswer($row, $column)) {
            return [$existingAnswer];
        }

        return $cachedPossibleAnswers->getPossibleAnswers($row, $column);
    }
}
