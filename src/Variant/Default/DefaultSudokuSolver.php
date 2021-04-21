<?php

namespace Stanjan\Sudoku\Variant\Default;

use Stanjan\Sudoku\Exception\SolverException;
use Stanjan\Sudoku\SudokuInterface;
use Stanjan\Sudoku\SudokuSolverInterface;

class DefaultSudokuSolver implements SudokuSolverInterface
{
    /**
     * Possible answers cache so the same cell won't be calculated multiple times.
     * Integer-indexed array (representing rows) containing an integer-indexed array (representing the columns per row) containing the possible answers.
     *
     * @var array<int, array<int, array<int>>>
     */
    private array $cachedPossibleAnswers = [];

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

        // Keep adding solutions until the sudoku is fully answered or a SolverException is thrown.
        while (!$sudoku->isFullyAnswered()) {
            $this->addAnswer($sudoku);

            // Clear cached possible answers after adding a new answer.
            $this->cachedPossibleAnswers = [];
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
                } catch (SolverException $exception) {
                    // This cell could not be answered, throw an error if the cell has no possible answers.
                    if (0 === count($this->getPossibleAnswersForCell($sudoku, $row, $column))) {
                        throw $exception;
                    }

                    // This cell still has multiple possible answers, try the next cell.
                }
            }
        }

        // Try advanced techniques.
        if ($this->tryAddAdvancedAnswer($sudoku)) {
            return;
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
     * Try adding an answer using advanced techniques.
     *
     * @return bool True when an answer could be added.
     */
    protected function tryAddAdvancedAnswer(SudokuInterface $sudoku): bool
    {
        if ($this->tryAddUniqueRectangleAnswer($sudoku)) {
            return true;
        }

        if ($this->tryAddXWingAnswer($sudoku)) {
            return true;
        }

        return false;
    }

    /**
     * Returns the possible answers for this cell, not taking other cells in consideration with multiple possible answers.
     *
     * @return array<int>
     */
    private function getPossibleAnswersForCell(SudokuInterface $sudoku, int $row, int $column): array
    {
        if (null !== $sudoku->getAnswer($row, $column)) {
            // This cell is already answered.
            return [$sudoku->getAnswer($row, $column)];
        }

        if (isset($this->cachedPossibleAnswers[$row][$column])) {
            // The possible answers for this cell have already been calculated.
            return $this->cachedPossibleAnswers[$row][$column];
        }

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

        if (!isset($this->cachedPossibleAnswers[$row])) {
            // Create the row array of no other cells in this row have been answered yet.
            $this->cachedPossibleAnswers[$row] = [];
        }

        // Cache the answers.
        $this->cachedPossibleAnswers[$row][$column] = $possibleAnswers;

        return $possibleAnswers;
    }

    /**
     * Try adding an answer using the unique rectangle technique.
     *
     * @return bool True when an answer could be added.
     */
    private function tryAddUniqueRectangleAnswer(SudokuInterface $sudoku): bool
    {
        $gridSize = $sudoku->getGrid()->getSize();

        // Try to find an answer for every non-answered cell.
        for ($row = 1; $row <= $gridSize->getRowCount() - 1; $row++) { // Ignore the last row as it needs a row after this for an unique rectangle.
            for ($column = 1; $column <= $gridSize->getColumnCount() - 1; $column++) { // Ignore the last column as it needs a column after this for an unique rectangle.
                if (null !== $sudoku->getAnswer($row, $column)) {
                    // This cell is already answered, ignore.
                    continue;
                }

                $firstColumnPossibleAnswers = $this->getPossibleAnswersForCell($sudoku, $row, $column);
                $firstColumnPossibleAnswersCount = count($firstColumnPossibleAnswers);
                $multipleAnswersCell = $firstColumnPossibleAnswersCount > 2 ? [$row, $column] : null; // Keeps track of the fact if one of the four columns has more than 2 possible answers.
                for ($otherColumn = $column + 1; $otherColumn <= $gridSize->getColumnCount(); $otherColumn++) {
                    $secondColumnPossibleAnswers = $this->getPossibleAnswersForCell($sudoku, $row, $otherColumn);
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
                        $thirdColumnPossibleAnswers = $this->getPossibleAnswersForCell($sudoku, $otherRow, $column);
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

                        $fourthColumnPossibleAnswers = $this->getPossibleAnswersForCell($sudoku, $otherRow, $otherColumn);
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
                        $this->cachedPossibleAnswers[$multipleAnswersCell[0]][$multipleAnswersCell[1]] = array_diff(
                            $this->cachedPossibleAnswers[$multipleAnswersCell[0]][$multipleAnswersCell[1]],
                            $possibleAnswers,
                        );
                        if (count($this->cachedPossibleAnswers[$multipleAnswersCell[0]][$multipleAnswersCell[1]]) === 1) {
                            // The cell only has one possible answer left, set it.
                            $sudoku->setAnswer(
                                $multipleAnswersCell[0],
                                $multipleAnswersCell[1],
                                reset($this->cachedPossibleAnswers[$multipleAnswersCell[0]][$multipleAnswersCell[1]])
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
     * Try adding an answer using the x-wing technique.
     *
     * @return bool True when an answer could be added.
     */
    private function tryAddXWingAnswer(SudokuInterface $sudoku): bool
    {
        $gridSize = $sudoku->getGrid()->getSize();

        // Try to find an answer for every non-answered cell.
        for ($row = 1; $row <= $gridSize->getRowCount() - 1; $row++) { // Ignore the last row as it needs a row after this for x-wing.
            for ($column = 1; $column <= $gridSize->getColumnCount() - 1; $column++) { // Ignore the last column as it needs a column after this for x-wing.
                if (null !== $sudoku->getAnswer($row, $column)) {
                    // This cell is already answered, ignore.
                    continue;
                }

                $possibleAnswers = $this->getPossibleAnswersForCell($sudoku, $row, $column);

                // Check for x-wings in the same row.
                foreach ($possibleAnswers as $possibleAnswer) {
                    $otherColumnWithPossibleAnswer = null;

                    for ($otherColumn = 1; $otherColumn <= $gridSize->getColumnCount(); $otherColumn++) {
                        if ($otherColumn === $column || null !== $sudoku->getAnswer($row, $otherColumn)) {
                            // This is the current column or it's already answered, ignore.
                            continue;
                        }

                        if (in_array($possibleAnswer, $this->getPossibleAnswersForCell($sudoku, $row, $otherColumn))) {
                            if (null !== $otherColumnWithPossibleAnswer) {
                                // There are multiple columns in this row with this possible answer, try the next possible answer.
                                continue 2;
                            }
                            $otherColumnWithPossibleAnswer = $otherColumn;
                        }
                    }

                    // There is only one other column with this possible answer in this row. Check if this is the same case in a different row.
                    for ($otherRow = $row + 1; $otherRow <= $gridSize->getRowCount(); $otherRow++) {
                        for ($otherColumn = 1; $otherColumn <= $gridSize->getColumnCount(); $otherColumn++) {
                            if ($otherColumn === $column || $otherColumn === $otherColumnWithPossibleAnswer) {
                                // This is one of the same columns, it must contain the possible answer aswell.
                                if (!in_array($possibleAnswer, $this->getPossibleAnswersForCell($sudoku, $otherRow, $otherColumn))) {
                                    // The possible answer is not in the same column, continue to the next row.
                                    continue 2;
                                }
                            } elseif (in_array($possibleAnswer, $this->getPossibleAnswersForCell($sudoku, $otherRow, $otherColumn))) {
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
                                $this->cachedPossibleAnswers[$removeRow][$column] = array_diff(
                                    $this->getPossibleAnswersForCell($sudoku, $removeRow, $column),
                                    [$possibleAnswer]
                                );
                                if (count($this->cachedPossibleAnswers[$removeRow][$column]) === 1) {
                                    // There is only one possible answer left, add it and return.
                                    $sudoku->setAnswer($removeRow, $column, reset($this->cachedPossibleAnswers[$removeRow][$column]));
                                    return true;
                                }
                            }

                            if (null === $sudoku->getAnswer($removeRow, $otherColumnWithPossibleAnswer)) {
                                $this->cachedPossibleAnswers[$removeRow][$otherColumnWithPossibleAnswer] = array_diff(
                                    $this->getPossibleAnswersForCell($sudoku, $removeRow, $otherColumnWithPossibleAnswer),
                                    [$possibleAnswer]
                                );
                                if (count($this->cachedPossibleAnswers[$removeRow][$otherColumnWithPossibleAnswer]) === 1) {
                                    // There is only one possible answer left, add it and return.
                                    $sudoku->setAnswer($removeRow, $otherColumnWithPossibleAnswer, reset($this->cachedPossibleAnswers[$removeRow][$otherColumnWithPossibleAnswer]));
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

                        if (in_array($possibleAnswer, $this->getPossibleAnswersForCell($sudoku, $otherRow, $column))) {
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
                                if (!in_array($possibleAnswer, $this->getPossibleAnswersForCell($sudoku, $otherRow, $otherColumn))) {
                                    // The possible answer is not in the same column, continue to the next row.
                                    continue 2;
                                }
                            } elseif (in_array($possibleAnswer, $this->getPossibleAnswersForCell($sudoku, $otherRow, $otherColumn))) {
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
                                $this->cachedPossibleAnswers[$row][$removeColumn] = array_diff(
                                    $this->getPossibleAnswersForCell($sudoku, $row, $removeColumn),
                                    [$possibleAnswer]
                                );
                                if (count($this->cachedPossibleAnswers[$row][$removeColumn]) === 1) {
                                    // There is only one possible answer left, add it and return.
                                    $sudoku->setAnswer($row, $removeColumn, reset($this->cachedPossibleAnswers[$row][$removeColumn]));
                                    return true;
                                }
                            }

                            if (null === $sudoku->getAnswer($otherRowWithPossibleAnswer, $removeColumn)) {
                                $this->cachedPossibleAnswers[$otherRowWithPossibleAnswer][$removeColumn] = array_diff(
                                    $this->getPossibleAnswersForCell($sudoku, $otherRowWithPossibleAnswer, $removeColumn),
                                    [$possibleAnswer]
                                );
                                if (count($this->cachedPossibleAnswers[$otherRowWithPossibleAnswer][$removeColumn]) === 1) {
                                    // There is only one possible answer left, add it and return.
                                    $sudoku->setAnswer($otherRowWithPossibleAnswer, $removeColumn, reset($this->cachedPossibleAnswers[$otherRowWithPossibleAnswer][$removeColumn]));
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
}
