<?php

namespace Stanjan\Sudoku\Variant\Default\Solver\Method;

use Stanjan\Sudoku\SudokuInterface;
use Stanjan\Sudoku\Variant\Default\Solver\PossibleAnswersCollection;

/**
 * Attempts to find an answer using the x-wing technique.
 *
 * https://www.learn-sudoku.com/x-wing.html
 */
class XWingMethod implements SolverMethodInterface
{
    /**
     * {@inheritDoc}
     */
    public static function tryAddAnswer(SudokuInterface $sudoku, PossibleAnswersCollection $cachedPossibleAnswers): bool
    {
        $gridSize = $sudoku->getGrid()->getSize();

        // Try to find an answer for every non-answered cell.
        for ($row = 1; $row <= $gridSize->getRowCount() - 1; $row++) { // Ignore the last row as it needs a row after this for x-wing.
            for ($column = 1; $column <= $gridSize->getColumnCount() - 1; $column++) { // Ignore the last column as it needs a column after this for x-wing.
                if (null !== $sudoku->getAnswer($row, $column)) {
                    // This cell is already answered, ignore.
                    continue;
                }

                $possibleAnswers = self::getPossibleAnswersForCell($sudoku, $cachedPossibleAnswers, $row, $column);

                // Check for x-wings in the same row.
                foreach ($possibleAnswers as $possibleAnswer) {
                    $otherColumnWithPossibleAnswer = null;

                    for ($otherColumn = 1; $otherColumn <= $gridSize->getColumnCount(); $otherColumn++) {
                        if ($otherColumn === $column || null !== $sudoku->getAnswer($row, $otherColumn)) {
                            // This is the current column or it's already answered, ignore.
                            continue;
                        }

                        if (in_array($possibleAnswer, self::getPossibleAnswersForCell($sudoku, $cachedPossibleAnswers, $row, $otherColumn))) {
                            if (null !== $otherColumnWithPossibleAnswer) {
                                // There are multiple columns in this row with this possible answer, try the next possible answer.
                                continue 2;
                            }
                            $otherColumnWithPossibleAnswer = $otherColumn;
                        }
                    }
                    if (null === $otherColumnWithPossibleAnswer) {
                        // There are no other columns with this possible answer.
                        continue;
                    }

                    // There is only one other column with this possible answer in this row. Check if this is the same case in a different row.
                    for ($otherRow = $row + 1; $otherRow <= $gridSize->getRowCount(); $otherRow++) {
                        for ($otherColumn = 1; $otherColumn <= $gridSize->getColumnCount(); $otherColumn++) {
                            if ($otherColumn === $column || $otherColumn === $otherColumnWithPossibleAnswer) {
                                // This is one of the same columns, it must contain the possible answer aswell.
                                if (!in_array($possibleAnswer, self::getPossibleAnswersForCell($sudoku, $cachedPossibleAnswers, $otherRow, $otherColumn))) {
                                    // The possible answer is not in the same column, continue to the next row.
                                    continue 2;
                                }
                            } elseif (in_array($possibleAnswer, self::getPossibleAnswersForCell($sudoku, $cachedPossibleAnswers, $otherRow, $otherColumn))) {
                                // The possible answer is also in a different column, continue to the next row.
                                continue 2;
                            }
                        }

                        // X-wing found. Remove the possible answer from all other cells in the same columns.
                        for ($removeRow = 1; $removeRow <= $gridSize->getRowCount(); $removeRow++) {
                            if ($removeRow === $row || $removeRow === $otherRow) {
                                // This is one of the x-wing rows, ignore.
                                continue;
                            }

                            if (null === $sudoku->getAnswer($removeRow, $column)) {
                                $cachedPossibleAnswers->setPossibleAnswers($removeRow, $column, array_diff(
                                    self::getPossibleAnswersForCell($sudoku, $cachedPossibleAnswers, $removeRow, $column),
                                    [$possibleAnswer]
                                ));
                                $newPossibleAnswers = $cachedPossibleAnswers->getPossibleAnswers($removeRow, $column);
                                if (count($newPossibleAnswers) === 1) {
                                    // There is only one possible answer left, add it and return.
                                    $sudoku->setAnswer($removeRow, $column, reset($newPossibleAnswers));
                                    return true;
                                }
                            }

                            if (null === $sudoku->getAnswer($removeRow, $otherColumnWithPossibleAnswer)) {
                                $cachedPossibleAnswers->setPossibleAnswers($removeRow, $otherColumnWithPossibleAnswer, array_diff(
                                    self::getPossibleAnswersForCell($sudoku, $cachedPossibleAnswers, $removeRow, $otherColumnWithPossibleAnswer),
                                    [$possibleAnswer]
                                ));
                                $newPossibleAnswers = $cachedPossibleAnswers->getPossibleAnswers($removeRow, $otherColumnWithPossibleAnswer);
                                if (count($newPossibleAnswers) === 1) {
                                    // There is only one possible answer left, add it and return.
                                    $sudoku->setAnswer($removeRow, $otherColumnWithPossibleAnswer, reset($newPossibleAnswers));
                                    return true;
                                }
                            }
                        }
                    }
                }

                // Check for x-wings in the same column.
                foreach ($possibleAnswers as $possibleAnswer) {
                    $otherRowWithPossibleAnswer = null;

                    for ($otherRow = 1; $otherRow <= $gridSize->getRowCount(); $otherRow++) {
                        if ($otherRow === $row || null !== $sudoku->getAnswer($otherRow, $column)) {
                            // This is the current row or it's already answered, ignore.
                            continue;
                        }

                        if (in_array($possibleAnswer, self::getPossibleAnswersForCell($sudoku, $cachedPossibleAnswers, $otherRow, $column))) {
                            if (null !== $otherRowWithPossibleAnswer) {
                                // There are multiple rows in this column with this possible answer, try the next possible answer.
                                continue 2;
                            }
                            $otherRowWithPossibleAnswer = $otherRow;
                        }
                    }

                    // There is only one other column with this possible answer in this row. Check if this is the same case in a different row.
                    for ($otherColumn = $column + 1; $otherColumn <= $gridSize->getColumnCount(); $otherColumn++) {
                        for ($otherRow = 1; $otherRow <= $gridSize->getRowCount(); $otherRow++) {
                            if ($otherRow === $row || $otherRow === $otherRowWithPossibleAnswer) {
                                // This is one of the same columns, it must contain the possible answer aswell.
                                if (!in_array($possibleAnswer, self::getPossibleAnswersForCell($sudoku, $cachedPossibleAnswers, $otherRow, $otherColumn))) {
                                    // The possible answer is not in the same column, continue to the next row.
                                    continue 2;
                                }
                            } elseif (in_array($possibleAnswer, self::getPossibleAnswersForCell($sudoku, $cachedPossibleAnswers, $otherRow, $otherColumn))) {
                                // The possible answer is also in a different column, continue to the next row.
                                continue 2;
                            }
                        }

                        // X-wing found. Remove the possible answer from all other cells in the same columns.
                        for ($removeColumn = 1; $removeColumn <= $gridSize->getRowCount(); $removeColumn++) {
                            if ($removeColumn === $column || $removeColumn === $otherColumn) {
                                // This is one of the x-wing rows, ignore.
                                continue;
                            }

                            if (null === $sudoku->getAnswer($row, $removeColumn)) {
                                $cachedPossibleAnswers->setPossibleAnswers($row, $removeColumn, array_diff(
                                    self::getPossibleAnswersForCell($sudoku, $cachedPossibleAnswers, $row, $removeColumn),
                                    [$possibleAnswer]
                                ));
                                $newPossibleAnswers = $cachedPossibleAnswers->getPossibleAnswers($row, $removeColumn);
                                if (count($newPossibleAnswers) === 1) {
                                    // There is only one possible answer left, add it and return.
                                    $sudoku->setAnswer($row, $removeColumn, reset($newPossibleAnswers));
                                    return true;
                                }
                            }

                            if (null === $sudoku->getAnswer($otherRowWithPossibleAnswer, $removeColumn)) {
                                $cachedPossibleAnswers->setPossibleAnswers($otherRowWithPossibleAnswer, $removeColumn, array_diff(
                                    self::getPossibleAnswersForCell($sudoku, $cachedPossibleAnswers, $otherRowWithPossibleAnswer, $removeColumn),
                                    [$possibleAnswer]
                                ));
                                $newPossibleAnswers = $cachedPossibleAnswers->getPossibleAnswers($otherRowWithPossibleAnswer, $removeColumn);
                                if (count($newPossibleAnswers) === 1) {
                                    // There is only one possible answer left, add it and return.
                                    $sudoku->setAnswer($otherRowWithPossibleAnswer, $removeColumn, reset($newPossibleAnswers));
                                    return true;
                                }
                            }
                        }
                    }
                }
            }
        }

        // X-wing could not be applied.
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
