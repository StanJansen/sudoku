<?php

namespace Stanjan\Sudoku\Variant\Default;

use Stanjan\Sudoku\SudokuGeneratorInterface;
use Stanjan\Sudoku\SudokuSolverInterface;
use Stanjan\Sudoku\SudokuVariantInterface;

class DefaultSudokuVariant implements SudokuVariantInterface
{
    public function getGenerator(): SudokuGeneratorInterface
    {
        return new DefaultSudokuGenerator();
    }

    public function getSolver(): SudokuSolverInterface
    {
        return new DefaultSudokuSolver();
    }
}