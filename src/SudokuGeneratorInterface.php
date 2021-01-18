<?php

namespace Stanjan\Sudoku;

use Stanjan\Sudoku\Exception\GeneratorException;

interface SudokuGeneratorInterface extends BelongsToSudokuVariantInterface
{
    /**
     * Generates a new sudoku.
     *
     * @throws GeneratorException When the sudoku could not be generated.
     */
    public function generate(): SudokuInterface;
}
