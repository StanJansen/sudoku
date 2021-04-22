<?php

namespace Stanjan\Sudoku\Variant\Default\Solver\Method;

use Stanjan\Sudoku\SudokuInterface;
use Stanjan\Sudoku\Variant\Default\Solver\PossibleAnswersCollection;

/**
 * Attempts to find an answer using the x-wing technique.
 *
 * https://www.learn-sudoku.com/swordfish.html
 */
class SwordfishMethod implements SolverMethodInterface
{
    /**
     * {@inheritDoc}
     */
    public static function tryAddAnswer(SudokuInterface $sudoku, PossibleAnswersCollection $cachedPossibleAnswers): bool
    {
        $gridSize = $sudoku->getGrid()->getSize();

        // Try to find a swordfish for every answer.
        for ($answer = 1; $answer <= $gridSize->getRowCount(); $answer++) {
            // Try swordfish for rows.
            for ($baseRow = 1; $baseRow <= $gridSize->getRowCount() - 2; $baseRow++) { // Don't try the last two rows, it needs at least two more to form a swordfish.
                $rows = [];
                $columns = [];
                for ($row = $baseRow; $row <= $gridSize->getRowCount(); $row++) {
                    $rowColumns = [];
                    for ($column = 1; $column <= $gridSize->getColumnCount(); $column++) {
                        if ($sudoku->getAnswer($row, $column)) {
                            // This cell is already answered, ignore.
                            continue;
                        }
                        if (in_array($answer, $cachedPossibleAnswers->getPossibleAnswers($row, $column))) {
                            // The possible answer is also in this column, add it if not set yet.
                            if (!in_array($column, $rowColumns)) {
                                $rowColumns[] = $column;
                                if (count($rowColumns) > 3) {
                                    // The limit of 3 is exceeded, continue to the next row, or to the next base row if this is the base row.
                                    if ($row === $baseRow) {
                                        continue 3;
                                    } else {
                                        continue 2;
                                    }
                                }
                            }
                        }
                    }

                    if (count($rowColumns) > 0 && (empty($columns) || count(array_unique(array_merge($columns, $rowColumns))) <= 3)) {
                        // This row can possibly form the swordfish, use it.
                        $rows[] = $row;
                        $columns = array_unique(array_merge($columns, $rowColumns));
                    } elseif ($row === $baseRow) {
                        // The base row cannot form a swordfish for this answer, continue to the next base row.
                        continue 2;
                    }
                }

                if (count($rows) !== 3 || count($columns) !== 3) {
                    // No swordfish found, ignore.
                    continue;
                }

                // Swordfish found, remove the possible answers from all intersecting cells in the same columns.
                for ($row = 1; $row <= $gridSize->getRowCount(); $row++) {
                    if (in_array($row, $rows)) {
                        // This is one of the swordfish rows, ignore.
                        continue;
                    }

                    foreach ($columns as $column) {
                        if ($sudoku->getAnswer($row, $column)) {
                            // This cell is already answered, ignore.
                            continue;
                        }

                        $cachedPossibleAnswers->setPossibleAnswers($row, $column, array_diff(
                            $cachedPossibleAnswers->getPossibleAnswers($row, $column),
                            [$answer]
                        ));
                        $newPossibleAnswers = $cachedPossibleAnswers->getPossibleAnswers($row, $column);
                        if (count($newPossibleAnswers) === 1) {
                            // There is only one possible answer left, add it and return.
                            $sudoku->setAnswer($row, $column, reset($newPossibleAnswers));
                            return true;
                        }
                    }
                }
            }

            // Try swordfish for columns.
            for ($baseColumn = 1; $baseColumn <= $gridSize->getColumnCount() - 2; $baseColumn++) { // Don't try the last two columns, it needs at least two more to form a swordfish.
                $columns = [];
                $rows = [];
                for ($column = $baseColumn; $column <= $gridSize->getColumnCount(); $column++) {
                    $columnRows = [];
                    for ($row = 1; $row <= $gridSize->getRowCount(); $row++) {
                        if ($sudoku->getAnswer($row, $column)) {
                            // This cell is already answered, ignore.
                            continue;
                        }
                        if (in_array($answer, $cachedPossibleAnswers->getPossibleAnswers($row, $column))) {
                            // The possible answer is also in this row, add it if not set yet.
                            if (!in_array($row, $columnRows)) {
                                $columnRows[] = $row;
                                if (count($columnRows) > 3) {
                                    // The limit of 3 is exceeded, continue to the next column, or to the next base column if this is the base column.
                                    if ($column === $baseColumn) {
                                        continue 3;
                                    } else {
                                        continue 2;
                                    }
                                }
                            }
                        }
                    }

                    if (count($columnRows) > 0 && (empty($rows) || count(array_unique(array_merge($rows, $columnRows))) <= 3)) {
                        // This column can possibly form the swordfish, use it.
                        $columns[] = $column;
                        $rows = array_unique(array_merge($rows, $columnRows));
                    } elseif ($column === $baseColumn) {
                        // The base column cannot form a swordfish for this answer, continue to the next base column.
                        continue 2;
                    }
                }

                if (count($columns) !== 3 || count($rows) !== 3) {
                    // No swordfish found, ignore.
                    continue;
                }

                // Swordfish found, remove the possible answers from all intersecting cells in the same rows.
                for ($column = 1; $column <= $gridSize->getColumnCount(); $column++) {
                    if (in_array($column, $columns)) {
                        // This is one of the swordfish columns, ignore.
                        continue;
                    }

                    foreach ($rows as $row) {
                        if ($sudoku->getAnswer($row, $column)) {
                            // This cell is already answered, ignore.
                            continue;
                        }

//                        if ($row === 9 && $column === 7) {
//                            var_dump($cachedPossibleAnswers->getPossibleAnswers(7, 8));
//                            var_dump($cachedPossibleAnswers->getPossibleAnswers(8, 8));
//                            var_dump($cachedPossibleAnswers->getPossibleAnswers(9, 7));
//                            var_dump($cachedPossibleAnswers->getPossibleAnswers(9, 8));
//                        }

                        $cachedPossibleAnswers->setPossibleAnswers($row, $column, array_diff(
                            $cachedPossibleAnswers->getPossibleAnswers($row, $column),
                            [$answer]
                        ));
                        $newPossibleAnswers = $cachedPossibleAnswers->getPossibleAnswers($row, $column);
                        if (count($newPossibleAnswers) === 1) {
                            // There is only one possible answer left, add it and return.
                            $sudoku->setAnswer($row, $column, reset($newPossibleAnswers));
                            return true;
                        }
                    }
                }
            }
        }

        // Swordfish could not be applied.
        return false;
    }
}
