<?php

namespace Stanjan\Sudoku\Variant\Default;

use Stanjan\Sudoku\SudokuInterface;
use Stanjan\Sudoku\SudokuSolverInterface;

class DefaultSudokuSolver implements SudokuSolverInterface
{
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
    public function solve(SudokuInterface $sudoku): void
    {
        // TODO: Implement solve() method.
    }
}
