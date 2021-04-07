<?php

namespace Stanjan\Sudoku\Variant\Default;

use Stanjan\Sudoku\Exception\SolverException;
use Stanjan\Sudoku\SudokuInterface;
use Stanjan\Sudoku\SudokuSolverInterface;

class DefaultSudokuSolver implements SudokuSolverInterface
{
    const DEFAULT_BRUTE_FORCE_LIMIT = 50_000;

    /**
     * The amount of times the solver will try to brute force the solutions of the sudoku.
     */
    protected int $bruteForceLimit = self::DEFAULT_BRUTE_FORCE_LIMIT;

    /**
     * {@inheritdoc}
     */
    public static function getVariantClassName(): string
    {
        return DefaultSudokuVariant::class;
    }

    /**
     * Overrides the brute force limit.
     */
    public function setBruteForceLimit(int $bruteForceLimit): void
    {
        $this->bruteForceLimit = $bruteForceLimit;
    }

    /**
     * {@inheritdoc}
     */
    public function solve(SudokuInterface $sudoku): void
    {
        // Check if the sudoku has at least one answer.
        if (!$sudoku->hasAnswers()) {
            throw new SolverException('The sudoku must have at least one answer.');
        }

        // Check if the sudoku is square.
        $gridSize = $sudoku->getGrid()->getSize();
        if ($gridSize->getRowCount() !== $gridSize->getColumnCount()) {
            throw new SolverException('This solver only supports square sudoku grids.');
        }

        // Keep adding solutions until the sudoku is fully answered or a SolverException is thrown.
        try {
            while (!$sudoku->isFullyAnswered()) {
                $this->addAnswer($sudoku);
            }
        } catch (SolverException $e) {
            // No answer could be generated anymore, fall back to brute forcing.
            // TODO: Try adding advanced solving techniques instead to see if it's faster at solving.
            $this->bruteForce($sudoku);
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

        $possibleAnswers = $this->getPossibleAnswersForCell($sudoku, $row, $column);

        // Check if it's the only field in its' row with a possible answer.
        if (count($possibleAnswers) > 1) {
            $possibleRowAnswers = $possibleAnswers;
            $mappedCellPossibleAnswers = [];
            for ($i = 1; $i <= $gridSize->getColumnCount(); $i++) {
                if ($i === $column || null !== $sudoku->getAnswer($row, $i)) {
                    // This is the current cell or it already has an answer, ignore.
                    continue;
                }

                // Remove the possible answers for the other cell from the possible answers.
                $answers = $this->getPossibleAnswersForCell($sudoku, $row, $i);
                $possibleAnswersKey = implode(',', $answers);
                $mappedCellPossibleAnswers[$possibleAnswersKey] = ($mappedCellPossibleAnswers[$possibleAnswersKey] ?? 0) + 1;
                $possibleRowAnswers = array_diff($possibleRowAnswers, $answers);
                if ($mappedCellPossibleAnswers[$possibleAnswersKey] === count($answers)) {
                    // It cannot be one of these answers as they are already shared over other cells in the same row, remove them.
                    $possibleAnswers = array_diff($possibleAnswers, $answers);
                }
            }
            if (count($possibleRowAnswers) === 1) {
                // There is only one answer possible for this cell.
                $possibleAnswers = $possibleRowAnswers;
            }
        }
        // Check if it's the only field in its' column with a possible answer.
        if (count($possibleAnswers) > 1) {
            $possibleColumnAnswers = $possibleAnswers;
            $mappedCellPossibleAnswers = [];
            for ($i = 1; $i <= $gridSize->getRowCount(); $i++) {
                if ($i === $row || null !== $sudoku->getAnswer($i, $column)) {
                    // This is the current cell or it already has an answer, ignore.
                    continue;
                }

                // Remove the possible answers for the other cell from the possible answers.
                $answers = $this->getPossibleAnswersForCell($sudoku, $i, $column);
                $possibleAnswersKey = implode(',', $answers);
                $mappedCellPossibleAnswers[$possibleAnswersKey] = ($mappedCellPossibleAnswers[$possibleAnswersKey] ?? 0) + 1;
                $possibleColumnAnswers = array_diff($possibleColumnAnswers, $answers);
                if ($mappedCellPossibleAnswers[$possibleAnswersKey] === count($answers)) {
                    // It cannot be one of these answers as they are already shared over other cells in the same row, remove them.
                    $possibleAnswers = array_diff($possibleAnswers, $answers);
                }
            }
            if (count($possibleColumnAnswers) === 1) {
                // There is only one answer possible for this cell.
                $possibleAnswers = $possibleColumnAnswers;
            }
        }
        // Check if it's the only field in its' subgrid with a possible answer.
        if (count($possibleAnswers) > 1) {
            $possibleSubGridAnswers = $possibleAnswers;
            $mappedCellPossibleAnswers = [];
            $subGridSize = $sudoku->getGrid()->getSubGridSize();
            $rowOffset = $row - (($row - 1) % $subGridSize->getRowCount());
            $columnOffset = $column - (($column - 1) % $subGridSize->getColumnCount());
            for ($subGridRow = $rowOffset; $subGridRow < $rowOffset + $subGridSize->getRowCount(); $subGridRow++) {
                for ($subGridColumn = $columnOffset; $subGridColumn < $columnOffset + $subGridSize->getColumnCount(); $subGridColumn++) {
                    if ($subGridRow === $row && $subGridColumn === $column || null !== $sudoku->getAnswer($subGridRow, $subGridColumn)) {
                        // This is the current cell or it has already been answered, ignore.
                        continue;
                    }

                    // Remove the possible answers for the other cell from the possible answers.
                    $answers = $this->getPossibleAnswersForCell($sudoku, $subGridRow, $subGridColumn);
                    $possibleAnswersKey = implode(',', $answers);
                    $mappedCellPossibleAnswers[$possibleAnswersKey] = ($mappedCellPossibleAnswers[$possibleAnswersKey] ?? 0) + 1;
                    $possibleSubGridAnswers = array_diff($possibleSubGridAnswers, $answers);
                    if ($mappedCellPossibleAnswers[$possibleAnswersKey] === count($answers)) {
                        // It cannot be one of these answers as they are already shared over other cells in the same row, remove them.
                        $possibleAnswers = array_diff($possibleAnswers, $answers);
                    }
                }
            }
            if (count($possibleSubGridAnswers) === 1) {
                // There is only one answer possible for this cell.
                $possibleAnswers = $possibleSubGridAnswers;
            }
        }

        // Check if the answer has been found.
        if (count($possibleAnswers) === 1) {
            $sudoku->setAnswer($row, $column, reset($possibleAnswers));
            return;
        }

        throw new SolverException(sprintf('The answer for row %d column %d could not be generated.', $row, $column));
    }

    /**
     * Brute forces the answers for the given sudoku.
     *
     * @throws SolverException When the sudoku could not be brute forced.
     */
    protected function bruteForce(SudokuInterface $sudoku): void
    {
        $gridSize = $sudoku->getGrid()->getSize();
        $rows = range(1, $gridSize->getRowCount());
        $columns = range(1, $gridSize->getColumnCount());

        $limit = min($this->bruteForceLimit,pow($gridSize->getRowCount() * $gridSize->getColumnCount(), 2));
        for ($attempt = 0; $attempt < $limit; $attempt++) {
            // Fill in a random possible answer for every non-answered cell on a clone of the sudoku.
            $sudokuClone = clone $sudoku;
            // Shuffle the rows and columns so attempts are different.
            shuffle($rows);
            shuffle($columns);
            foreach ($rows as $row) {
                foreach ($columns as $column) {
                    if (null !== $sudokuClone->getAnswer($row, $column)) {
                        // This cell is already answered, ignore.
                        continue;
                    }
                    $possibleAnswers = $this->getPossibleAnswersForCell($sudokuClone, $row, $column);

                    if (0 === count($possibleAnswers)) {
                        // No answers possible, retry brute forcing.
                        continue 3;
                    }

                    // Set a random answer.
                    $sudokuClone->setAnswer($row, $column, $possibleAnswers[array_rand($possibleAnswers)]);

                    // Try answering all cells normally again with this random answer.
                    try {
                        while (!$sudokuClone->isFullyAnswered()) {
                            $this->addAnswer($sudokuClone);
                        }
                        // Sudoku is fully answered successfully.
                        break 2;
                    } catch (SolverException) {
                        // Sudoku still cannot be solved normally, continue brute forcing.
                    }
                }
            }

            // Brute forcing succeeded, copy the answers to the original sudoku and return.
            for ($row = 1; $row <= $gridSize->getRowCount(); $row++) {
                for ($column = 1; $column <= $gridSize->getColumnCount(); $column++) {
                    $sudoku->setAnswer($row, $column, $sudokuClone->getAnswer($row, $column));
                }
            }
            return;
        }

        throw new SolverException('The sudoku answers could not be brute forced.');
    }

    /**
     * Returns the possible answers for this cell, not taking other cells in consideration with multiple possible answers.
     *
     * @return array<int>
     */
    private function getPossibleAnswersForCell(SudokuInterface $sudoku, int $row, int $column): array
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

        return $possibleAnswers;
    }
}
