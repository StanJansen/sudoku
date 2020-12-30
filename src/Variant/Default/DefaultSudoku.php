<?php

namespace Stanjan\Sudoku\Variant\Default;

use Stanjan\Sudoku\Grid\Grid;
use Stanjan\Sudoku\SudokuInterface;

class DefaultSudoku implements SudokuInterface
{
    /**
     * The answers to this sudoku. Integer-indexed array (representing rows) containing an integer-indexed array (representing the columns per row).
     *
     * @var array<int, array<int, int>
     */
    protected array $answers;

    public function __construct(
        protected Grid $grid,
    ) {}

    public static function getVariantClassName(): string
    {
        return DefaultSudokuVariant::class;
    }

    public function getGrid(): Grid
    {
        return $this->grid;
    }

    public function addAnswer(int $row, int $column, int $answer): void
    {
        // Initialize the row array if it's not set yet.
        if (!isset($this->answers[$row])) {
            $this->answers[$row] = [];
        }

        $this->answers[$row][$column] = $answer;
    }

    public function getAnswer(int $row, int $column): ?int
    {
        return $this->answers[$row][$column] ?? null;
    }
}