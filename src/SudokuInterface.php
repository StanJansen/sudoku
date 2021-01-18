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
     * Sets the solution for the given row and column.
     */
    public function setSolution(int $row, int $column, int $solution): void;

    /**
     * Returns the solution for the sudoku for the given row and column, returns null if the solution is not set.
     */
    public function getSolution(int $row, int $column): ?int;
}
