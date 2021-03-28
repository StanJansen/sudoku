<?php

namespace Stanjan\Sudoku\Variant\Default;

use Stanjan\Sudoku\Exception\SolverException;
use Stanjan\Sudoku\SudokuInterface;
use Stanjan\Sudoku\SudokuSolverInterface;

class DefaultSudokuSolver implements SudokuSolverInterface
{
    /**
     * {@inheritdoc}
     */
    public static function getVariantClassName(): string
    {
        return DefaultSudokuVariant::class;
    }

    /**
     * {@inheritdoc}
     */
    public function solve(SudokuInterface $sudoku): void
    {
        // Check if the sudoku is square.
        $gridSize = $sudoku->getGrid()->getSize();
        if ($gridSize->getRowCount() !== $gridSize->getColumnCount()) {
            throw new SolverException('This solver only supports square sudoku grids.');
        }

        // Keep adding solutions until the sudoku is solved or a SolverException is thrown.
        while (!$sudoku->isSolved()) {
            $this->addAnswer($sudoku);
        }
    }

    /**
     * Adds a single new answer to the sudoku.
     *
     * @throws SolverException When no answer could be generated.
     */
    protected function addAnswer(SudokuInterface $sudoku): void
    {
        $gridSize = $sudoku->getGrid()->getSize();

        // Try to add an answer for every non-answered cell.
        for ($row = 1; $row <= $gridSize->getRowCount(); $row++) {
            for ($column = 1; $column <= $gridSize->getColumnCount(); $column++) {
                if ($sudoku->getAnswer($row, $column) !== null) {
                    // This cell has already been answered, ignore.
                    continue;
                }

                try {
                    $this->addAnswerForCell($sudoku, $row, $column);
                    // Adding an answer succeeded, return.
                    return;
                } catch (SolverException) {
                    // This cell could not be answered, try the next cell.
                }
            }
        }

        throw new SolverException('No answer could be generated.');
    }

    /**
     * Answers the cell of the given sudoku.
     *
     * @throws SolverException When the cell could not be answered.
     */
    protected function addAnswerForCell(SudokuInterface $sudoku, int $row, int $column): void
    {
        $gridSize = $sudoku->getGrid()->getSize();

        // Index all possible answers.
        $possibleAnswers = range(1, $gridSize->getRowCount());

        // Remove the answers from all other cells in the same row.
        for ($i = 1; $i <= $gridSize->getColumnCount(); $i++) {
            if ($i === $column) {
                // This is the current cell, ignore.
                continue;
            }

            // Get the answer for the other cell in the same row.
            $existingAnswer = $sudoku->getAnswer($row, $i);
            if (null === $existingAnswer) {
                // This cell has not been answered either, ignore.
                continue;
            }

            // Remove the answer of the other cell if it's still possible.
            if (($index = array_search($existingAnswer, $possibleAnswers)) !== false) {
                unset($possibleAnswers[$index]);
            }
        }

        // Remove the answers from all other cells in the same column.
        for ($i = 1; $i <= $gridSize->getRowCount(); $i++) {
            if ($i === $row) {
                // This is the current cell, ignore.
                continue;
            }

            // Get the answer for the other cell in the same column.
            $existingAnswer = $sudoku->getAnswer($i, $column);
            if (null === $existingAnswer) {
                // This cell has not been answered either, ignore.
                continue;
            }

            // Remove the answer of the other cell if it's still possible.
            if (($index = array_search($existingAnswer, $possibleAnswers)) !== false) {
                unset($possibleAnswers[$index]);
            }
        }

        // Remove the answers from all other cells in the same subgrid.
        $subGridSize = $sudoku->getGrid()->getSubGridSize();
        $rowOffset = $row - (($row - 1) % $subGridSize->getRowCount());
        $columnOffset = $column - (($column - 1) % $subGridSize->getColumnCount());
        for ($subGridRow = $rowOffset; $subGridRow < $rowOffset + $subGridSize->getRowCount(); $subGridRow++) {
            for ($subGridColumn = $columnOffset; $subGridColumn < $columnOffset + $subGridSize->getColumnCount(); $subGridColumn++) {
                if ($subGridRow === $row && $subGridColumn === $column) {
                    // This is the current cell, ignore.
                    continue;
                }

                // Get the answer for the other cell.
                $existingAnswer = $sudoku->getAnswer($subGridRow, $subGridColumn);
                if (null === $existingAnswer) {
                    // This cell has not been answered either, ignore.
                    continue;
                }

                // Remove the answer of the other cell if it's still possible.
                if (($index = array_search($existingAnswer, $possibleAnswers)) !== false) {
                    unset($possibleAnswers[$index]);
                }
            }
        }

        // Check if the answer has been found.
        if (count($possibleAnswers) === 1) {
            $sudoku->setAnswer($row, $column, reset($possibleAnswers));
            return;
        }

        throw new SolverException(sprintf('The answer for row %d column %d could not be generated.', $row, $column));
    }
}
