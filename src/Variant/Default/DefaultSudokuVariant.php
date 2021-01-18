<?php

namespace Stanjan\Sudoku\Variant\Default;

use Stanjan\Sudoku\SudokuGeneratorInterface;
use Stanjan\Sudoku\SudokuSolverInterface;
use Stanjan\Sudoku\SudokuVariantInterface;

class DefaultSudokuVariant implements SudokuVariantInterface
{
    /**
     * {@inheritdoc}
     */
    public function getGenerator(): SudokuGeneratorInterface
    {
        return new DefaultSudokuGenerator();
    }

    /**
     * {@inheritdoc}
     */
    public function getSolver(): SudokuSolverInterface
    {
        return new DefaultSudokuSolver();
    }
}
