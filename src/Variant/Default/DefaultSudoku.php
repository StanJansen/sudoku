<?php

namespace Stanjan\Sudoku\Variant\Default;

use Stanjan\Sudoku\AbstractSudoku;

class DefaultSudoku extends AbstractSudoku
{
    /**
     * {@inheritdoc}
     */
    public static function getVariantClassName(): string
    {
        return DefaultSudokuVariant::class;
    }
}
