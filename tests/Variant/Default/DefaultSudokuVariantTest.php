<?php

namespace Stanjan\Sudoku\Tests\Variant\Default;

use PHPUnit\Framework\TestCase;
use Stanjan\Sudoku\Variant\Default\DefaultSudokuGenerator;
use Stanjan\Sudoku\Variant\Default\DefaultSudokuImageReader;
use Stanjan\Sudoku\Variant\Default\DefaultSudokuSolver;
use Stanjan\Sudoku\Variant\Default\DefaultSudokuVariant;

/**
 * @covers \Stanjan\Sudoku\Variant\Default\DefaultSudokuVariant
 */
final class DefaultSudokuVariantTest extends TestCase
{
    public function testGetGenerator(): void
    {
        $variant = new DefaultSudokuVariant();

        $this->assertInstanceOf(DefaultSudokuGenerator::class, $variant->getGenerator());
    }

    public function testGetSolver(): void
    {
        $variant = new DefaultSudokuVariant();

        $this->assertInstanceOf(DefaultSudokuSolver::class, $variant->getSolver());
    }

    public function testGetImageReaderClassName(): void
    {
        $variant = new DefaultSudokuVariant();

        $this->assertSame(DefaultSudokuImageReader::class, $variant->getImageReaderClassName());
    }

    /**
     * Test if the variant's solver can solve a sudoku generated by the variant's generator.
     */
    public function testSolveGenerator(): void
    {
        $variant = new DefaultSudokuVariant();

        $sudoku = $variant->getGenerator()->generate();
        $variant->getSolver()->solve($sudoku);

        $this->expectNotToPerformAssertions();
    }
}
