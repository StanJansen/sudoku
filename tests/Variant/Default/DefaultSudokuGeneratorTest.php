<?php

namespace Stanjan\Sudoku\Tests\Variant\Default;

use PHPUnit\Framework\TestCase;
use Stanjan\Sudoku\Variant\Default\DefaultSudoku;
use Stanjan\Sudoku\Variant\Default\DefaultSudokuGenerator;
use Stanjan\Sudoku\Variant\Default\DefaultSudokuVariant;

/**
 * @covers DefaultSudokuGenerator::class
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
        
        $this->assertInstanceOf(DefaultSudoku::class, $sudoku);

        // Check the dimensions.
        $this->assertSame(9, $gridSize->getRowCount());
        $this->assertSame(9, $gridSize->getColumnCount());
        $this->assertSame(3, $subGridSize->getRowCount());
        $this->assertSame(3, $subGridSize->getColumnCount());

        // Index the possible answers.
        $highestAnswer = $subGridSize->getRowCount() * $subGridSize->getColumnCount();
        $possibleAnswers = range(1,$highestAnswer);

        // Make sure every column contains all possible answers.
        for ($row = 1; $row <= $gridSize->getRowCount(); $row++) {
            $columnAnswers = [];
            for ($column = 1; $column <= $gridSize->getColumnCount(); $column++) {
                $columnAnswers[] = $sudoku->getAnswer($row, $column);
            }

            sort($columnAnswers, SORT_NUMERIC);
            $this->assertSame($possibleAnswers, $columnAnswers);
        }

        // Make sure every row contains all possible answers.
        for ($column = 1; $column <= $gridSize->getRowCount(); $column++) {
            $rowAnswers = [];
            for ($row = 1; $row <= $gridSize->getColumnCount(); $row++) {
                $rowAnswers[] = $sudoku->getAnswer($column, $row);
            }

            sort($rowAnswers, SORT_NUMERIC);
            $this->assertSame($possibleAnswers, $rowAnswers);
        }

        // Make sure every subgrid contains all possible answers.
        for ($subGridHorizontal = 1; $subGridHorizontal <= $sudoku->getGrid()->getHorizontalSubGridCount(); $subGridHorizontal++) {
            $horizontalOffset = $subGridSize->getColumnCount() * $subGridHorizontal - $subGridSize->getColumnCount();
            for ($subGridVertical = 1; $subGridVertical <= $sudoku->getGrid()->getVerticalSubGridCount(); $subGridVertical++) {
                $verticalOffset = $subGridSize->getRowCount() * $subGridVertical - $subGridSize->getRowCount();
                for ($row = 1 + $verticalOffset; $row <= $subGridSize->getRowCount() + $verticalOffset; $row++) {
                    for ($column = 1 + $horizontalOffset; $column <= $subGridSize->getColumnCount() + $horizontalOffset; $column++) {
                        $subGridAnswers = [];
                        for ($row = 1; $row <= $gridSize->getColumnCount(); $row++) {
                            $subGridAnswers[] = $sudoku->getAnswer($column, $row);
                        }

                        sort($subGridAnswers, SORT_NUMERIC);
                        $this->assertSame($possibleAnswers, $subGridAnswers);
                    }
                }
            }
        }
    }
}