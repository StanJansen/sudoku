<?php

namespace Stanjan\Sudoku;

interface BelongsToSudokuVariantInterface
{
    /**
     * Returns the class name of the variant of this sudoku component.
     */
    public static function getVariantClassName(): string;
}
