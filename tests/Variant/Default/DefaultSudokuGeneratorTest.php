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
        
        $this->assertInstanceOf(DefaultSudoku::class, $sudoku);
    }
}