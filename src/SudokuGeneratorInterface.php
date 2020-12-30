<?php

namespace Stanjan\Sudoku;

interface SudokuGeneratorInterface extends BelongsToSudokuVariantInterface
{
    /**
     * Generates a new sudoku.
     */
    public function generate(): SudokuInterface;
}