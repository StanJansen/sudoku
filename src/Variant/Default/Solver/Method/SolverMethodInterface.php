<?php

namespace Stanjan\Sudoku\Variant\Default\Solver\Method;

use Stanjan\Sudoku\Variant\Default\DefaultSudoku;
use Stanjan\Sudoku\Variant\Default\Solver\PossibleAnswersCollection;

/**
 * A method for \Stanjan\Sudoku\Variant\Default\DefaultSudokuSolver.
 */
interface SolverMethodInterface
{
    /**
     * Attempts to add an answer to the sudoku. Returns true if an answer was added, false if not.
     *
     * @param PossibleAnswersCollection $cachedPossibleAnswers This collection must be fully filled before using this function.
     */
    public static function tryAddAnswer(DefaultSudoku $sudoku, PossibleAnswersCollection $cachedPossibleAnswers): bool;
}
