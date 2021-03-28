<?php

namespace Stanjan\Sudoku\Variant\Default;

use Stanjan\Sudoku\Grid\Grid;
use Stanjan\Sudoku\SudokuInterface;

class DefaultSudoku implements SudokuInterface
{
    /**
     * The solutions to this sudoku. Integer-indexed array (representing rows) containing an integer-indexed array (representing the columns per row).
     *
     * @var array<int, array<int, int>>
     */
    protected array $solutions = [];

    /**
     * The answers to this sudoku. Integer-indexed array (representing rows) containing an integer-indexed array (representing the columns per row).
     *
     * @var array<int, array<int, int>>
     */
    protected array $answers = [];

    public function __construct(
        protected Grid $grid,
    ) {
    }

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
            ksort($this->answers); // Make sure the array is ordered so the isSolved method works properly.
        }

        $this->answers[$row][$column] = $answer;
        ksort($this->answers[$row]); // Make sure the array is ordered so the isSolved method works properly.
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
    public function isSolved(): bool
    {
        return $this->answers === $this->solutions;
    }
}
