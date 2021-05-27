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

    /**
     * Sets the answer for the given row and column.
     */
    public function setAnswer(int $row, int $column, int $answer): void;

    /**
     * Returns the answer for the sudoku for the given row and column, returns null if the answer is not set.
     */
    public function getAnswer(int $row, int $column): ?int;

    /**
     * Determines if the sudoku has an answer for all cells.
     */
    public function isFullyAnswered(): bool;

    /**
     * Returns the difficulty rating of the sudoku, if it's unknown this will return null.
     */
    public function getDifficultyRating(): ?int;
}
