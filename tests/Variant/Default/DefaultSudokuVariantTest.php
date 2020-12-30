<?php

namespace Stanjan\Sudoku\Tests\Variant\Default;

use PHPUnit\Framework\TestCase;
use Stanjan\Sudoku\Variant\Default\DefaultSudokuGenerator;
use Stanjan\Sudoku\Variant\Default\DefaultSudokuSolver;
use Stanjan\Sudoku\Variant\Default\DefaultSudokuVariant;

/**
 * @covers DefaultSudokuVariant::class
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
}