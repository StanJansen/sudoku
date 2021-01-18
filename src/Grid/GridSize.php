<?php

namespace Stanjan\Sudoku\Grid;

use Stanjan\Sudoku\Exception\InvalidGridException;

/**
 * The size of (a section of) the grid.
 */
final class GridSize
{
    /**
     * @throws InvalidGridException
     */
    public function __construct(
        /** The amount of rows (vertical) */
        private int $rowCount,
        /** The amount of columns (horizontal) */
        private int $columnCount,
    ) {
        // Make sure the row and column count are greater than zero.
        if ($this->rowCount <= 0) {
            throw new InvalidGridException(sprintf('The amount of rows must be greater than 0, %d given.', $this->rowCount));
        }
        if ($this->columnCount <= 0) {
            throw new InvalidGridException(sprintf('The amount of columns must be greater than 0, %d given.', $this->columnCount));
        }
    }

    public function getRowCount(): int
    {
        return $this->rowCount;
    }

    public function getColumnCount(): int
    {
        return $this->columnCount;
    }
}
