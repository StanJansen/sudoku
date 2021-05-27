<?php

namespace Stanjan\Sudoku\Variant\Default;

use Stanjan\Sudoku\Exception\SolverException;
use Stanjan\Sudoku\SudokuInterface;
use Stanjan\Sudoku\SudokuSolverInterface;
use Stanjan\Sudoku\Variant\Default\Solver\Method\SwordfishMethod;
use Stanjan\Sudoku\Variant\Default\Solver\Method\UniqueRectangleMethod;
use Stanjan\Sudoku\Variant\Default\Solver\Method\XWingMethod;
use Stanjan\Sudoku\Variant\Default\Solver\PossibleAnswersCollection;
use Stanjan\Sudoku\Variant\Default\Solver\SolvingMethod;

class DefaultSudokuSolver implements SudokuSolverInterface
{
    /**
     * Possible answers cache so the same cell won't be calculated multiple times.
     */
    private PossibleAnswersCollection $cachedPossibleAnswers;

    public function __construct()
    {
        $this->cachedPossibleAnswers = new PossibleAnswersCollection();
    }

    /**
     * {@inheritdoc}
     */
    public static function getVariantClassName(): string
    {
        return DefaultSudokuVariant::class;
    }

    /**
     * @param array<string> $allowedSolvingMethods The allowed solving methods, if empty it will use all methods.
     *
     * {@inheritdoc}
     */
    public function solve(SudokuInterface $sudoku, array $allowedSolvingMethods = []): void
    {
        // Set the base allowed solving methods.
        if (empty($allowedSolvingMethods)) {
            $allowedSolvingMethods = SolvingMethod::getAll();
        }

        // Check if the sudoku is square.
        $gridSize = $sudoku->getGrid()->getSize();
        if ($gridSize->getRowCount() !== $gridSize->getColumnCount() || !($sudoku instanceof DefaultSudoku)) {
            throw new SolverException('This solver only supports square default sudoku grids.');
        }

        // Keep adding solutions until the sudoku is fully answered or a SolverException is thrown.
        while (!$sudoku->isFullyAnswered()) {
            // Clear cached possible answers before adding a new answer.
            $this->cachedPossibleAnswers->clear();

            $this->addAnswer($sudoku, $allowedSolvingMethods);
        }
    }

    /**
     * Adds a single new answer to the sudoku.
     *
     * @param array<string> $allowedSolvingMethods The allowed solving methods
     *
     * @throws SolverException When no answer could be generated.
     */
    protected function addAnswer(DefaultSudoku $sudoku, array $allowedSolvingMethods, bool $retry = true): void
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
                    $this->addAnswerForCell($sudoku, $row, $column, $allowedSolvingMethods);
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
        if ($this->tryAddAdvancedAnswer($sudoku, $allowedSolvingMethods)) {
            return;
        }

        if ($retry) {
            // Retry in case a combination works.
            $this->addAnswer($sudoku, $allowedSolvingMethods, false);
            return;
        }

        throw new SolverException('No answer could be generated.');
    }

    /**
     * Answers the cell of the given sudoku.
     *
     * @param array<string> $allowedSolvingMethods The allowed solving methods
     *
     * @throws SolverException When the cell could not be answered.
     */
    protected function addAnswerForCell(DefaultSudoku $sudoku, int $row, int $column, array $allowedSolvingMethods): void
    {
        $gridSize = $sudoku->getGrid()->getSize();

        $possibleAnswers = $this->getPossibleAnswersForCell($sudoku, $row, $column);
        $originalPossibleAnswers = $possibleAnswers;
        $solvingMethod = SolvingMethod::SINGLE_POSSIBLE_ANSWER;

        // Check permissions for solving methods.
        $canSingleElimination = in_array(SolvingMethod::SINGLE_ELIMINATION, $allowedSolvingMethods);
        $canPairElimination = in_array(SolvingMethod::PAIR_ELIMINATION, $allowedSolvingMethods);
        $canCombinedPairElimination = in_array(SolvingMethod::COMBINED_PAIR_ELIMINATION, $allowedSolvingMethods);

        // Check if it's the only field in its' row with a possible answer.
        $hasRowPairElimination = false;
        if ($canSingleElimination && count($possibleAnswers) > 1) {
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
                if ($canPairElimination && $mappedCellPossibleAnswers[$possibleAnswersKey] === count($answers)) {
                    // It cannot be one of these answers as they are already shared over other cells in the same row, remove them.
                    $possibleAnswers = array_diff($possibleAnswers, $answers);
                    $hasRowPairElimination = true;
                }
            }
            if (count($possibleAnswers) === 1) {
                $solvingMethod = SolvingMethod::PAIR_ELIMINATION;
            } elseif (count($possibleRowAnswers) === 1) {
                // There is only one answer possible for this cell.
                $possibleAnswers = $possibleRowAnswers;
                $solvingMethod = SolvingMethod::SINGLE_ELIMINATION;
            }

            if (!$canCombinedPairElimination && count($possibleAnswers) > 1) {
                // Pair elimination cannot be combined, reset the possible answers if no answer is found.
                $possibleAnswers = $originalPossibleAnswers;
            }
        }

        // Check if it's the only field in its' column with a possible answer.
        $hasColumnPairElimination = false;
        if ($canSingleElimination && count($possibleAnswers) > 1) {
            $possibleColumnAnswers = $possibleAnswers;
            $originalPossibleColumnAnswers = $originalPossibleAnswers;
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
                $originalPossibleColumnAnswers = array_diff($originalPossibleColumnAnswers, $answers);
                if ($canPairElimination && $mappedCellPossibleAnswers[$possibleAnswersKey] === count($answers)) {
                    // It cannot be one of these answers as they are already shared over other cells in the same row, remove them.
                    $possibleAnswers = array_diff($possibleAnswers, $answers);
                    $hasColumnPairElimination = true;
                }
            }
            if (count($possibleAnswers) === 1) {
                $solvingMethod = $hasRowPairElimination ? SolvingMethod::COMBINED_PAIR_ELIMINATION : SolvingMethod::PAIR_ELIMINATION;
            } elseif (count($possibleColumnAnswers) === 1) {
                // There is only one answer possible for this cell.
                $possibleAnswers = $possibleColumnAnswers;
                $solvingMethod = $originalPossibleColumnAnswers === $possibleAnswers ? SolvingMethod::SINGLE_ELIMINATION : SolvingMethod::PAIR_ELIMINATION;
            }

            if (!$canCombinedPairElimination && count($possibleAnswers) > 1) {
                // Pair elimination cannot be combined, reset the possible answers if no answer is found.
                $possibleAnswers = $originalPossibleAnswers;
            }
        }
        // Check if it's the only field in its' subgrid with a possible answer.
        if ($canSingleElimination && count($possibleAnswers) > 1) {
            $possibleSubGridAnswers = $possibleAnswers;
            $originalPossibleSubGridAnswers = $originalPossibleAnswers;
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
                    $originalPossibleSubGridAnswers = array_diff($originalPossibleSubGridAnswers, $answers);
                    if ($canPairElimination && $mappedCellPossibleAnswers[$possibleAnswersKey] === count($answers)) {
                        // It cannot be one of these answers as they are already shared over other cells in the same row, remove them.
                        $possibleAnswers = array_diff($possibleAnswers, $answers);
                    }
                }
            }
            if (count($possibleAnswers) === 1) {
                $solvingMethod = $hasRowPairElimination || $hasColumnPairElimination ? SolvingMethod::COMBINED_PAIR_ELIMINATION : SolvingMethod::PAIR_ELIMINATION;
            } elseif (count($possibleSubGridAnswers) === 1) {
                // There is only one answer possible for this cell.
                $possibleAnswers = $possibleSubGridAnswers;
                $solvingMethod = $originalPossibleSubGridAnswers === $possibleAnswers ? SolvingMethod::SINGLE_ELIMINATION : ($hasRowPairElimination && $hasColumnPairElimination ? SolvingMethod::COMBINED_PAIR_ELIMINATION : SolvingMethod::PAIR_ELIMINATION);
            }

            if (!$canCombinedPairElimination && count($possibleAnswers) > 1) {
                // Pair elimination cannot be combined, reset the possible answers if no answer is found.
                $possibleAnswers = $originalPossibleAnswers;
            }
        }

        // Update the unique possible answers.
        $this->cachedPossibleAnswers->setPossibleAnswers($row, $column, $possibleAnswers);

        // Check if the answer has been found.
        if (count($possibleAnswers) === 1) {
            $sudoku->setAnswer($row, $column, reset($possibleAnswers));
            $sudoku->addSolvingMethodToDifficultyRating($solvingMethod);
            return;
        }

        throw new SolverException(sprintf('The answer for row %d column %d could not be generated.', $row, $column));
    }

    /**
     * Try adding an answer using advanced techniques.
     *
     * @param array<string> $allowedSolvingMethods The allowed solving methods
     *
     * @return bool True when an answer could be added.
     */
    protected function tryAddAdvancedAnswer(DefaultSudoku $sudoku, array $allowedSolvingMethods): bool
    {
        if (in_array(SolvingMethod::UNIQUE_RECTANGLE, $allowedSolvingMethods) && UniqueRectangleMethod::tryAddAnswer($sudoku, $this->cachedPossibleAnswers)) {
            $sudoku->addSolvingMethodToDifficultyRating(SolvingMethod::UNIQUE_RECTANGLE);
            return true;
        }

        if (in_array(SolvingMethod::X_WING, $allowedSolvingMethods) && XWingMethod::tryAddAnswer($sudoku, $this->cachedPossibleAnswers)) {
            $sudoku->addSolvingMethodToDifficultyRating(SolvingMethod::X_WING);
            return true;
        }

        if (in_array(SolvingMethod::SWORDFISH, $allowedSolvingMethods) && SwordfishMethod::tryAddAnswer($sudoku, $this->cachedPossibleAnswers)) {
            $sudoku->addSolvingMethodToDifficultyRating(SolvingMethod::SWORDFISH);
            return true;
        }

        return false;
    }

    /**
     * Returns the possible answers for this cell, not taking other cells in consideration with multiple possible answers.
     *
     * @return array<int>
     */
    private function getPossibleAnswersForCell(DefaultSudoku $sudoku, int $row, int $column): array
    {
        if (null !== $sudoku->getAnswer($row, $column)) {
            // This cell is already answered.
            return [$sudoku->getAnswer($row, $column)];
        }

        if (null !== $this->cachedPossibleAnswers->getPossibleAnswers($row, $column)) {
            // The possible answers for this cell have already been calculated.
            return $this->cachedPossibleAnswers->getPossibleAnswers($row, $column);
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

        // Cache the answers.
        $this->cachedPossibleAnswers->setPossibleAnswers($row, $column, $possibleAnswers);

        return $possibleAnswers;
    }
}
