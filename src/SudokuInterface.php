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
}