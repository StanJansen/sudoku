<?php

namespace Stanjan\Sudoku\Tests\Variant\Default;

use PHPUnit\Framework\TestCase;
use Stanjan\Sudoku\Grid\Grid;
use Stanjan\Sudoku\Grid\GridSize;
use Stanjan\Sudoku\Variant\Default\DefaultSudoku;
use Stanjan\Sudoku\Variant\Default\DefaultSudokuVariant;
use Stanjan\Sudoku\Variant\Default\Solver\SolvingMethod;

/**
 * @covers \Stanjan\Sudoku\Variant\Default\DefaultSudoku
 * @covers \Stanjan\Sudoku\AbstractSudoku
 */
final class DefaultSudokuTest extends TestCase
{
    public function testGetVariantClassName(): void
    {
        $this->assertSame(DefaultSudokuVariant::class, DefaultSudoku::getVariantClassName());
    }
    
    public function testGrid(): void
    {
        $gridSize = new GridSize(1, 2);
        $subGridSize = new GridSize(1, 2);

        $grid = new Grid($gridSize, $subGridSize);

        $sudoku = new DefaultSudoku($grid);

        $this->assertSame($grid, $sudoku->getGrid());
    }

    public function testSolutions(): void
    {
        $gridSize = new GridSize(1, 2);
        $subGridSize = new GridSize(1, 2);

        $grid = new Grid($gridSize, $subGridSize);

        $sudoku = new DefaultSudoku($grid);

        $this->assertNull($sudoku->getSolution(1, 2));

        $sudoku->setSolution(1, 2, 6);

        $this->assertSame(6, $sudoku->getSolution(1, 2));
    }

    public function testAnswers(): void
    {
        $gridSize = new GridSize(1, 2);
        $subGridSize = new GridSize(1, 2);

        $grid = new Grid($gridSize, $subGridSize);

        $sudoku = new DefaultSudoku($grid);

        $this->assertNull($sudoku->getAnswer(1, 2));

        $sudoku->setAnswer(1, 2, 6);

        $this->assertSame(6, $sudoku->getAnswer(1, 2));
    }

    public function testIsFullyAnswered(): void
    {
        $gridSize = new GridSize(1, 2);
        $subGridSize = new GridSize(1, 2);

        $grid = new Grid($gridSize, $subGridSize);

        $sudoku = new DefaultSudoku($grid);

        $this->assertFalse($sudoku->isFullyAnswered());

        $sudoku->setAnswer(1, 1, 1);
        $sudoku->setAnswer(1, 2, 1);

        $this->assertTrue($sudoku->isFullyAnswered());
    }

    public function testDifficultyRating(): void
    {
        $gridSize = new GridSize(1, 2);
        $subGridSize = new GridSize(1, 2);

        $grid = new Grid($gridSize, $subGridSize);

        $sudoku = new DefaultSudoku($grid);

        $this->assertNull($sudoku->getDifficultyRating());

        $sudoku->addToDifficultyRating(10);
        $this->assertSame(10, $sudoku->getDifficultyRating());

        $sudoku->addToDifficultyRating(10);
        $this->assertSame(20, $sudoku->getDifficultyRating());

        $sudoku->setDifficultyRating(10);
        $this->assertSame(10, $sudoku->getDifficultyRating());
    }

    public function testAddSolvingMethodToDifficultyRating(): void
    {
        $gridSize = new GridSize(1, 2);
        $subGridSize = new GridSize(1, 2);

        $grid = new Grid($gridSize, $subGridSize);

        $sudoku = new DefaultSudoku($grid);

        $this->assertNull($sudoku->getDifficultyRating());
        $this->assertEmpty($sudoku->getUsedSolvingMethods());

        $sudoku->addSolvingMethodToDifficultyRating(SolvingMethod::SWORDFISH);

        $this->assertSame(SolvingMethod::getFirstMethodRating(SolvingMethod::SWORDFISH), $sudoku->getDifficultyRating());
        $this->assertSame([SolvingMethod::SWORDFISH], $sudoku->getUsedSolvingMethods());

        $sudoku->addSolvingMethodToDifficultyRating(SolvingMethod::SWORDFISH);

        $this->assertSame(SolvingMethod::getFirstMethodRating(SolvingMethod::SWORDFISH) + SolvingMethod::getSubsequentMethodRating(SolvingMethod::SWORDFISH), $sudoku->getDifficultyRating());
        $this->assertSame([SolvingMethod::SWORDFISH], $sudoku->getUsedSolvingMethods());
    }

    public function testSetUsedSolvingMethods(): void
    {
        $gridSize = new GridSize(1, 2);
        $subGridSize = new GridSize(1, 2);

        $grid = new Grid($gridSize, $subGridSize);

        $sudoku = new DefaultSudoku($grid);

        $this->assertEmpty($sudoku->getUsedSolvingMethods());

        $sudoku->setUsedSolvingMethods(['test']);

        $this->assertSame(['test'], $sudoku->getUsedSolvingMethods());
    }
}
