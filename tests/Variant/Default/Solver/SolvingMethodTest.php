<?php

namespace Stanjan\Sudoku\Tests\Variant\Default\Solver;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Stanjan\Sudoku\Variant\Default\Solver\SolvingMethod;

/**
 * @covers \Stanjan\Sudoku\Variant\Default\Solver\SolvingMethod
 */
final class SolvingMethodTest extends TestCase
{
    /**
     * Test if the enum functions support all methods.
     */
    public function testSupportForAllMethods(): void
    {
        $allMethods = SolvingMethod::getAll();

        // Test if all methods are included in the hardest difficulty.
        $this->assertSame($allMethods, SolvingMethod::getForDifficultyLevel(SolvingMethod::DIFFICULTY_VERY_HARD));

        // Test if all methods have difficulty ratings, make sure the subsequent rating is equal or lower than the first one.
        foreach ($allMethods as $method) {
            $this->assertLessThanOrEqual(SolvingMethod::getFirstMethodRating($method), SolvingMethod::getSubsequentMethodRating($method));
        }
    }

    public function testInvalidLowerDifficultyLevel(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Difficulty level must be between 1 and 4, 0 given.');

        SolvingMethod::getForDifficultyLevel(0);
    }

    public function testInvalidHigherDifficultyLevel(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Difficulty level must be between 1 and 4, 5 given.');

        SolvingMethod::getForDifficultyLevel(5);
    }

    public function testInvalidFirstRatingMethod(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Difficulty rating for method "test" not found.');

        SolvingMethod::getFirstMethodRating('test');
    }

    public function testInvalidSubsequentRatingMethod(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Difficulty rating for method "test" not found.');

        SolvingMethod::getSubsequentMethodRating('test');
    }
}
