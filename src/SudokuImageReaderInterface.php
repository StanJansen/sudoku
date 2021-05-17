<?php

namespace Stanjan\Sudoku;

use Stanjan\Sudoku\Exception\ReaderException;

interface SudokuImageReaderInterface extends BelongsToSudokuVariantInterface
{
    /**
     * Reads the given sudoku and creates a sudoku instance for it.
     *
     * @throws ReaderException When the sudoku could not be read.
     */
    public function read(string $filePath): SudokuInterface;
}
