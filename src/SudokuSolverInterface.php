<?php

namespace Stanjan\Sudoku;

interface SudokuSolverInterface extends BelongsToSudokuVariantInterface
{
    /**
     * Solves an existing sudoku.
     */
    public function solve(SudokuInterface $sudoku): void;
}
