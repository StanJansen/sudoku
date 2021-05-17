<?php

namespace Stanjan\Sudoku\OCR;

use Stanjan\Sudoku\Exception\OCRException;
use Stanjan\Sudoku\SudokuInterface;

interface OCRInterface
{
    /**
     * Fills the sudoku fields based on the given image using OCR.
     *
     * @throws OCRException
     */
    public function fillSudokuFromImage(SudokuInterface $sudoku, string $filePath): void;
}
