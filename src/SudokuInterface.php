<?php

namespace Stanjan\Sudoku;

use Stanjan\Sudoku\Grid\Grid;

/**
 * Description of a sudoku. Note that it can differ from the default 9x9 grid depending on the variant.
 */
interface SudokuInterface extends BelongsToSudokuVariantInterface
{
    /**
     * The grid containing the row and column base information.
     */
    public function getGrid(): Grid;

    /**
     * Sets the answer for the given row and column.
     */
    public function addAnswer(int $row, int $column, int $answer): void;

    /**
     * Returns the answer for the sudoku for the given row and column, returns null if the answer is not set.
     */
    public function getAnswer(int $row, int $column): ?int;
}