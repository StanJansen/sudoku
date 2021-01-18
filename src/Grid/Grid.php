<?php

namespace Stanjan\Sudoku\Grid;

use Stanjan\Sudoku\Exception\InvalidGridException;

/**
 * Base information for a sudoku grid.
 */
final class Grid
{
    /**
     * @throws InvalidGridException
     */
    public function __construct(
        /** The size of the full grid */
        private GridSize $size,
        /** The size of each sub grid */
        private GridSize $subGridSize,
    ) {
        // Make sure the subgrid size is not bigger than the base size.
        if ($this->size->getRowCount() < $this->subGridSize->getRowCount()) {
            throw new InvalidGridException(sprintf(
                'The amount of rows (%d) on the subgrid are higher than the amount of rows (%d) on the base grid.',
                $this->subGridSize->getRowCount(),
                $this->size->getRowCount(),
            ));
        }
        if ($this->size->getColumnCount() < $this->subGridSize->getColumnCount()) {
            throw new InvalidGridException(sprintf(
                'The amount of columns (%d) on the subgrid are higher than the amount of columns (%d) on the base grid.',
                $this->subGridSize->getColumnCount(),
                $this->size->getColumnCount(),
            ));
        }

        // Make sure the base size is divisible by the subgrid size.
        if ($this->size->getRowCount() % $this->subGridSize->getRowCount() !== 0) {
            throw new InvalidGridException(sprintf(
                'The amount of rows (%d) on the grid must be divisible by the amount of rows (%d) on the subgrid.',
                $this->size->getRowCount(),
                $this->subGridSize->getRowCount(),
            ));
        }
        if ($this->size->getColumnCount() % $this->subGridSize->getColumnCount() !== 0) {
            throw new InvalidGridException(sprintf(
                'The amount of columns (%d) on the grid must be divisible by the amount of columns (%d) on the subgrid.',
                $this->size->getColumnCount(),
                $this->subGridSize->getColumnCount(),
            ));
        }
    }

    public function getSize(): GridSize
    {
        return $this->size;
    }

    public function getSubGridSize(): GridSize
    {
        return $this->subGridSize;
    }

    /**
     * Returns the amount of times the subgrid size fits in the base size horizontally.
     */
    public function getHorizontalSubGridCount(): int
    {
        return $this->size->getRowCount() / $this->subGridSize->getRowCount();
    }

    /**
     * Returns the amount of times the subgrid size fits in the base size vertically.
     */
    public function getVerticalSubGridCount(): int
    {
        return $this->size->getColumnCount() / $this->subGridSize->getColumnCount();
    }
}
