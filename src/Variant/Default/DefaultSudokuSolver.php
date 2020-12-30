<?php

namespace Stanjan\Sudoku\Variant\Default;

use Stanjan\Sudoku\SudokuInterface;
use Stanjan\Sudoku\SudokuSolverInterface;

class DefaultSudokuSolver implements SudokuSolverInterface
{
    public static function getVariantClassName(): string
    {
        return DefaultSudokuVariant::class;
    }

    public function solve(SudokuInterface $sudoku): void
    {
        // TODO: Implement solve() method.
    }
}