<?php

namespace Stanjan\Sudoku;

use Stanjan\Sudoku\Exception\SolverException;

interface SudokuSolverInterface extends BelongsToSudokuVariantInterface
{
    /**
     * Solves an existing sudoku.
     *
     * @throws SolverException When the sudoku could not be solved.
     */
    public function solve(SudokuInterface $sudoku): void;
}
