<?php

namespace Stanjan\Sudoku\Variant\Default;

use Stanjan\Sudoku\SudokuVariantInterface;

class DefaultSudokuVariant implements SudokuVariantInterface
{
    /**
     * {@inheritdoc}
     */
    public function getGenerator(): DefaultSudokuGenerator
    {
        return new DefaultSudokuGenerator();
    }

    /**
     * {@inheritdoc}
     */
    public function getSolver(): DefaultSudokuSolver
    {
        return new DefaultSudokuSolver();
    }

    /**
     * {@inheritdoc}
     */
    public function getImageReaderClassName(): ?string
    {
        return DefaultSudokuImageReader::class;
    }
}
