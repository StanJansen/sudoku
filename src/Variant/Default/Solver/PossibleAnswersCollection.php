<?php

namespace Stanjan\Sudoku\Variant\Default\Solver;

class PossibleAnswersCollection
{
    /**
     * Integer-indexed array (representing rows) containing an integer-indexed array (representing the columns per row) containing the possible answers.
     *
     * @var array<int, array<int, array<int>>>
     */
    private array $possibleAnswers = [];

    /**
     * Returns the possible answers for the given row and column, if not set it will return null.
     *
     * @return array<int>|null
     */
    public function getPossibleAnswers(int $row, int $column): ?array
    {
        return $this->possibleAnswers[$row][$column] ?? null;
    }

    /**
     * Sets the possible answers for the given row and column.
     *
     * @param array<int> $possibleAnswers
     */
    public function setPossibleAnswers(int $row, int $column, array $possibleAnswers): void
    {
        if (!isset($this->possibleAnswers[$row])) {
            // This row is not yet initialized, set the base array.
            $this->possibleAnswers[$row] = [];
        }

        $this->possibleAnswers[$row][$column] = $possibleAnswers;
    }

    /**
     * Clears all possible answers from the collection.
     */
    public function clear(): void
    {
        $this->possibleAnswers = [];
    }
}
