<?php

namespace Stanjan\Sudoku;

/**
 * Variant of sudoku.
 */
interface SudokuVariantInterface
{
    /**
     * Returns the generator for this variant of sudoku.
     */
    public function getGenerator(): SudokuGeneratorInterface;

    /**
     * Returns the solver for this variant of sudoku.
     */
    public function getSolver(): SudokuSolverInterface;
}