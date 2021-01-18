<?php

namespace Stanjan\Sudoku\Exception;

/**
 * Thrown when the description of a grid is invalid.
 */
class InvalidGridException extends \InvalidArgumentException implements SudokuExceptionInterface
{
}
