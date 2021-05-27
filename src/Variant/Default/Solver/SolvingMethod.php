<?php

namespace Stanjan\Sudoku\Variant\Default\Solver;

use InvalidArgumentException;

/**
 * Enum containing solving methods and their difficulty ratings.
 */
class SolvingMethod
{
    const SINGLE_POSSIBLE_ANSWER = 'single_possible_answer';
    const SINGLE_ELIMINATION = 'single_elimination';
    const PAIR_ELIMINATION = 'pair_elimination';
    const COMBINED_PAIR_ELIMINATION = 'combined_pair_elimination';
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
            self::SINGLE_POSSIBLE_ANSWER,
            self::SINGLE_ELIMINATION,
            self::PAIR_ELIMINATION,
            self::COMBINED_PAIR_ELIMINATION,
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
            self::SINGLE_POSSIBLE_ANSWER,
        ];

        if ($difficultyLevel >= self::DIFFICULTY_MEDIUM) {
            $methods = array_merge($methods, [
                self::SINGLE_ELIMINATION,
                self::PAIR_ELIMINATION,
            ]);
        }
        if ($difficultyLevel >= self::DIFFICULTY_HARD) {
            $methods = array_merge($methods, [
                self::COMBINED_PAIR_ELIMINATION,
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
            case self::SINGLE_POSSIBLE_ANSWER:
                return 10;

            case self::SINGLE_ELIMINATION:
                return 30;

            case self::PAIR_ELIMINATION:
                return 60;

            case self::COMBINED_PAIR_ELIMINATION:
                return 75;

            case self::UNIQUE_RECTANGLE:
                return 300;

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
            case self::SINGLE_POSSIBLE_ANSWER:
                return 10;

            case self::SINGLE_ELIMINATION:
                return 25;

            case self::PAIR_ELIMINATION:
                return 30;

            case self::COMBINED_PAIR_ELIMINATION:
                return 50;

            case self::UNIQUE_RECTANGLE:
                return 100;

            case self::X_WING:
                return 200;

            case self::SWORDFISH:
                return 600;
        }

        throw new InvalidArgumentException(sprintf('Difficulty rating for method "%s" not found.', $method));
    }
}
