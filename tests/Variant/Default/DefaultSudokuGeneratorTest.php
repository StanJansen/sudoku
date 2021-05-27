<?php

namespace Stanjan\Sudoku\Tests\Variant\Default;

use PHPUnit\Framework\TestCase;
use Stanjan\Sudoku\Exception\GeneratorException;
use Stanjan\Sudoku\Variant\Default\DefaultSudoku;
use Stanjan\Sudoku\Variant\Default\DefaultSudokuGenerator;
use Stanjan\Sudoku\Variant\Default\DefaultSudokuVariant;
use Stanjan\Sudoku\Variant\Default\Solver\SolvingMethod;

/**
 * @covers \Stanjan\Sudoku\Variant\Default\DefaultSudokuGenerator
 */
final class DefaultSudokuGeneratorTest extends TestCase
{
    public function testGetVariantClassName(): void
    {
        $this->assertSame(DefaultSudokuVariant::class, DefaultSudokuGenerator::getVariantClassName());
    }
    
    public function testGenerate(): void
    {
        $generator = new DefaultSudokuGenerator();
        
        $sudoku = $generator->generate();
        $grid = $sudoku->getGrid();
        $gridSize = $grid->getSize();
        $subGridSize = $grid->getSubGridSize();

        $this->assertGreaterThan(count(SolvingMethod::getForDifficultyLevel(SolvingMethod::DIFFICULTY_EASY)), count($sudoku->getUsedSolvingMethods()));

        // Check the dimensions.
        $this->assertSame(9, $gridSize->getRowCount());
        $this->assertSame(9, $gridSize->getColumnCount());
        $this->assertSame(3, $subGridSize->getRowCount());
        $this->assertSame(3, $subGridSize->getColumnCount());

        // Index the possible solutions.
        $highestSolution = $subGridSize->getRowCount() * $subGridSize->getColumnCount();
        $possibleSolutions = range(1, $highestSolution);

        // Make sure every column contains all possible solutions.
        for ($row = 1; $row <= $gridSize->getRowCount(); $row++) {
            $columnSolutions = [];
            for ($column = 1; $column <= $gridSize->getColumnCount(); $column++) {
                $columnSolutions[] = $sudoku->getSolution($row, $column);
            }

            sort($columnSolutions, SORT_NUMERIC);
            $this->assertSame($possibleSolutions, $columnSolutions);
        }

        // Make sure every row contains all possible solutions.
        for ($column = 1; $column <= $gridSize->getRowCount(); $column++) {
            $rowSolutions = [];
            for ($row = 1; $row <= $gridSize->getColumnCount(); $row++) {
                $rowSolutions[] = $sudoku->getSolution($column, $row);
            }

            sort($rowSolutions, SORT_NUMERIC);
            $this->assertSame($possibleSolutions, $rowSolutions);
        }

        // Make sure every subgrid contains all possible solutions.
        for ($subGridHorizontal = 1; $subGridHorizontal <= $sudoku->getGrid()->getHorizontalSubGridCount(); $subGridHorizontal++) {
            $horizontalOffset = $subGridSize->getColumnCount() * $subGridHorizontal - $subGridSize->getColumnCount();
            for ($subGridVertical = 1; $subGridVertical <= $sudoku->getGrid()->getVerticalSubGridCount(); $subGridVertical++) {
                $verticalOffset = $subGridSize->getRowCount() * $subGridVertical - $subGridSize->getRowCount();
                for ($row = 1 + $verticalOffset; $row <= $subGridSize->getRowCount() + $verticalOffset; $row++) {
                    for ($column = 1 + $horizontalOffset; $column <= $subGridSize->getColumnCount() + $horizontalOffset; $column++) {
                        $subGridSolutions = [];
                        for ($row = 1; $row <= $gridSize->getColumnCount(); $row++) {
                            $subGridSolutions[] = $sudoku->getSolution($column, $row);
                        }

                        sort($subGridSolutions, SORT_NUMERIC);
                        $this->assertSame($possibleSolutions, $subGridSolutions);
                    }
                }
            }
        }
    }

    public function testGenerateEasy(): void
    {
        $generator = new DefaultSudokuGenerator();

        $sudoku = $generator->generate();

        $easySolvingMethods = SolvingMethod::getForDifficultyLevel(SolvingMethod::DIFFICULTY_EASY);
        $usedSolvingMethods = $sudoku->getUsedSolvingMethods();
        $this->assertSame(sort($easySolvingMethods), sort($usedSolvingMethods));
    }
    
    public function testGenerateOverSolutionsLimit(): void
    {
        $generator = new DefaultSudokuGenerator();
        
        $generator->setRetrySolutionsLimit(0);
        
        $this->expectException(GeneratorException::class);
        $this->expectExceptionMessage('Generator retry limit of 0 exceeded.');

        $generator->generate();
    }
}
