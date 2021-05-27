<?php

namespace Stanjan\Sudoku\Variant\Default\Solver;

use InvalidArgumentException;

/**
 * Enum containing solving methods and their difficulty ratings.
 */
class SolvingMethod
{
    const SINGLE_CANDITATE = 'single_candidate';
    const SINGLE_POSITION = 'single_position';
    const CANDIDATE_LINES = 'candidate_lines';
    const MULTIPLE_LINES = 'multiple_lines';
    const NAKED_PAIR = 'naked_pair';
    const HIDDEN_PAIR = 'hidden_pair';
    const NAKED_TRIPLE = 'naked_triple';
    const HIDDEN_TRIPLE = 'hidden_triple';
    const NAKED_QUAD = 'naked_quad';
    const HIDDEN_QUAD = 'hidden_quad';
    const UNIQUE_RECTANGLE = 'unique_rectangle';
    const X_WING = 'x_wing';
    const SWORDFISH = 'swordfish';

    const DIFFICULTY_EASY = 1;
    const DIFFICULTY_MEDIUM = 2;
    const DIFFICULTY_HARD = 3;
    const DIFFICULTY_VERY_HARD = 4;

    /**
     * Returns all methods.
     *
     * @return array<string>
     */
    public static function getAll(): array
    {
        return [
            self::SINGLE_CANDITATE,
            self::SINGLE_POSITION,
            self::CANDIDATE_LINES,
            self::MULTIPLE_LINES,
            self::NAKED_PAIR,
            self::HIDDEN_PAIR,
            self::NAKED_TRIPLE,
            self::HIDDEN_TRIPLE,
            self::NAKED_QUAD,
            self::HIDDEN_QUAD,
            self::UNIQUE_RECTANGLE,
            self::X_WING,
            self::SWORDFISH,
        ];
    }

    /**
     * Returns all methods for the given difficulty level.
     *
     * @return array<string>
     *
     * @throws InvalidArgumentException Thrown when an invalid difficulty level was given.
     */
    public static function getForDifficultyLevel(int $difficultyLevel): array
    {
        if ($difficultyLevel < self::DIFFICULTY_EASY || $difficultyLevel > self::DIFFICULTY_VERY_HARD) {
            throw new InvalidArgumentException(sprintf(
                'Difficulty level must be between %d and %d, %d given.',
                self::DIFFICULTY_EASY,
                self::DIFFICULTY_VERY_HARD,
                $difficultyLevel
            ));
        }

        $methods = [
            self::SINGLE_CANDITATE,
            self::SINGLE_POSITION,
            self::CANDIDATE_LINES,
        ];

        if ($difficultyLevel >= self::DIFFICULTY_MEDIUM) {
            $methods = array_merge($methods, [
                self::MULTIPLE_LINES,
                self::NAKED_PAIR,
                self::HIDDEN_PAIR,
            ]);
        }
        if ($difficultyLevel >= self::DIFFICULTY_HARD) {
            $methods = array_merge($methods, [
                self::NAKED_TRIPLE,
                self::HIDDEN_TRIPLE,
                self::NAKED_QUAD,
                self::HIDDEN_QUAD,
                self::UNIQUE_RECTANGLE,
            ]);
        }
        if ($difficultyLevel >= self::DIFFICULTY_VERY_HARD) {
            $methods = array_merge($methods, [
                self::X_WING,
                self::SWORDFISH,
            ]);
        }

        return $methods;
    }

    /**
     * Returns the rating for the method when first used.
     *
     * @throws InvalidArgumentException Thrown when the method was unknown.
     */
    public static function getFirstMethodRating(string $method): int
    {
        switch ($method) {
            case self::SINGLE_CANDITATE:
            case self::SINGLE_POSITION:
                return 10;

            case self::CANDIDATE_LINES:
                return 35;

            case self::MULTIPLE_LINES:
                return 40;

            case self::NAKED_PAIR:
                return 50;

            case self::HIDDEN_PAIR:
            case self::NAKED_TRIPLE:
                return 70;

            case self::HIDDEN_TRIPLE:
            case self::NAKED_QUAD:
                return 100;

            case self::HIDDEN_QUAD:
                return 200;

            case self::UNIQUE_RECTANGLE:
                return 400;

            case self::X_WING:
                return 500;

            case self::SWORDFISH:
                return 800;
        }

        throw new InvalidArgumentException(sprintf('Difficulty rating for method "%s" not found.', $method));
    }

    /**
     * Returns the rating for the method when subsequently used.
     *
     * @throws InvalidArgumentException Thrown when the method was unknown.
     */
    public static function getSubsequentMethodRating(string $method): int
    {
        switch ($method) {
            case self::SINGLE_CANDITATE:
            case self::SINGLE_POSITION:
                return 10;

            case self::CANDIDATE_LINES:
                return 20;

            case self::MULTIPLE_LINES:
            case self::NAKED_PAIR:
                return 25;

            case self::HIDDEN_PAIR:
            case self::NAKED_TRIPLE:
                return 40;

            case self::HIDDEN_TRIPLE:
            case self::NAKED_QUAD:
                return 65;

            case self::HIDDEN_QUAD:
                return 100;

            case self::UNIQUE_RECTANGLE:
                return 200;

            case self::X_WING:
                return 300;

            case self::SWORDFISH:
                return 600;
        }

        throw new InvalidArgumentException(sprintf('Difficulty rating for method "%s" not found.', $method));
    }
}
