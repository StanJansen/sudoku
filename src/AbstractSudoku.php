<?php

namespace Stanjan\Sudoku;

use Stanjan\Sudoku\Grid\Grid;

abstract class AbstractSudoku implements SudokuInterface
{
    /**
     * The solutions to this sudoku. Integer-indexed array (representing rows) containing an integer-indexed array (representing the columns per row).
     *
     * @var array<int, array<int, ?int>>
     */
    protected array $solutions = [];

    /**
     * The answers to this sudoku. Integer-indexed array (representing rows) containing an integer-indexed array (representing the columns per row).
     *
     * @var array<int, array<int, ?int>>
     */
    protected array $answers = [];

    /**
     * The total difficulty rating of this sudoku.
     */
    protected ?int $difficultyRating = null;

    public function __construct(
        protected Grid $grid,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function getGrid(): Grid
    {
        return $this->grid;
    }

    /**
     * {@inheritdoc}
     */
    public function setSolution(int $row, int $column, int $solution): void
    {
        // Initialize the row array if it's not set yet.
        if (!isset($this->solutions[$row])) {
            $this->solutions[$row] = [];
        }

        $this->solutions[$row][$column] = $solution;
    }

    /**
     * {@inheritdoc}
     */
    public function getSolution(int $row, int $column): ?int
    {
        return $this->solutions[$row][$column] ?? null;
    }

    /**
     * {@inheritdoc}
     */
    public function setAnswer(int $row, int $column, ?int $answer): void
    {
        // Initialize the row array if it's not set yet.
        if (!isset($this->answers[$row])) {
            $this->answers[$row] = [];
        }

        $this->answers[$row][$column] = $answer;
    }

    /**
     * {@inheritdoc}
     */
    public function getAnswer(int $row, int $column): ?int
    {
        return $this->answers[$row][$column] ?? null;
    }

    /**
     * {@inheritdoc}
     */
    public function hasAllSolutions(): bool
    {
        $totalCount = 0;

        foreach ($this->answers as $rowAnswers) {
            // Do not count null values as an answer.
            $totalCount += count(array_filter($rowAnswers, fn (?int $answer) => null !== $answer));
        }

        return $totalCount === $this->grid->getSize()->getRowCount() * $this->grid->getSize()->getColumnCount();
    }

    /**
     * {@inheritdoc}
     */
    public function getDifficultyRating(): ?int
    {
        return $this->difficultyRating;
    }

    /**
     * Sets the difficulty rating of the sudoku.
     */
    public function setDifficultyRating(?int $difficultyRating): void
    {
        $this->difficultyRating = $difficultyRating;
    }

    /**
     * Adds the given rating to the total difficulty rating.
     */
    public function addToDifficultyRating(int $difficultyRating): void
    {
        if ($this->difficultyRating === null) {
            // This difficulty rating was still unknown, set it to zero.
            $this->difficultyRating = 0;
        }

        $this->difficultyRating += $difficultyRating;
    }
}
