<?php

namespace Stanjan\Sudoku\Variant\Default;

use Stanjan\Sudoku\AbstractSudoku;
use Stanjan\Sudoku\Grid\Grid;
use Stanjan\Sudoku\Variant\Default\Solver\SolvingMethod;

class DefaultSudoku extends AbstractSudoku
{
    public function __construct(
        Grid $grid,
        /** @var array<string> $usedSolvingMethods */
        protected array $usedSolvingMethods = [],
    ) {
        parent::__construct($grid);
    }

    /**
     * {@inheritdoc}
     */
    public static function getVariantClassName(): string
    {
        return DefaultSudokuVariant::class;
    }

    /**
     * Adds the difficulty rating for the given solving method to the total difficulty rating.
     * Also tracks and keeps track of the used solving methods so the first or subsequent method rating will be used.
     */
    public function addSolvingMethodToDifficultyRating(string $method): void
    {
        if (in_array($method, $this->usedSolvingMethods)) {
            // This method has already been used before, add subsequent rating.
            $this->addToDifficultyRating(SolvingMethod::getSubsequentMethodRating($method));
        } else {
            // This is the first time this method has been used, add the first rating.
            $this->usedSolvingMethods[] = $method;
            $this->addToDifficultyRating(SolvingMethod::getFirstMethodRating($method));
        }
    }

    /**
     * Returns all used solving methods.
     *
     * @return array<string>
     */
    public function getUsedSolvingMethods(): array
    {
        return $this->usedSolvingMethods;
    }
}
