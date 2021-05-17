<?php

namespace Stanjan\Sudoku\Variant\Default;

use CV;
use GuzzleHttp\Exception\GuzzleException;
use Stanjan\Sudoku\Exception\OCRException;
use Stanjan\Sudoku\Exception\ReaderException;
use Stanjan\Sudoku\Grid\Grid;
use Stanjan\Sudoku\Grid\GridSize;
use Stanjan\Sudoku\OCR\OCRInterface;
use Stanjan\Sudoku\SudokuImageReaderInterface;

class DefaultSudokuImageReader implements SudokuImageReaderInterface
{
    public function __construct(
        protected OCRInterface $ocr,
        protected string $tempDirPath = __DIR__.'/../../../tmp/sudoku/read',
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public static function getVariantClassName(): string
    {
        return DefaultSudokuVariant::class;
    }

    /**
     * {@inheritdoc}
     */
    public function read(string $filePath): DefaultSudoku
    {
        // Read the image.
        try {
            $image = CV\imread($filePath);
        } catch (CV\Exception) {
            throw new ReaderException('Could not load image: '.$filePath);
        }

        // Prepare the image for OCR.
        $image = $this->prepareImageForOCR($image);

        // Write the image to a temp image for the OCR to work with.
        if (!is_dir($this->tempDirPath)) {
            // Create the temp dir if it does not exist yet.
            mkdir($this->tempDirPath, 0755, true);
        }
        $tempImagePath = $this->tempDirPath.'/'.uniqid().'.jpg';
        CV\imwrite($tempImagePath, $image);

        // Create the base sudoku.
        $gridSize = new GridSize(9, 9);
        $subGridSize = new GridSize(3, 3);
        $grid = new Grid($gridSize, $subGridSize);
        $sudoku = new DefaultSudoku($grid);

        // Use the OCR.
        try {
            $this->ocr->fillSudokuFromImage($sudoku, $tempImagePath);
        } catch (OCRException|GuzzleException $exception) {
            // Remove the temp image before throwing an error.
            unlink($tempImagePath);
            throw new ReaderException('Could not apply OCR: '.$exception->getMessage());
        }

        // Remove the temp image.
        unlink($tempImagePath);

        // Make sure there are at least 17 answers.
        $answerCount = 0;
        for ($row = 1; $row <= 9; $row++) {
            for ($column = 1; $column <= 9; $column++) {
                if ($sudoku->getAnswer($row, $column) !== null) {
                    $answerCount++;
                }
            }
        }
        if ($answerCount < 17) {
            // There are less than 17 answers. At least 17 answers are required to solve a sudoku.
            throw new ReaderException('Not enough answers could be read to generate a sudoku.');
        }

        return $sudoku;
    }

    /**
     * Prepares the given image to be used in OCR.
     *
     * @throws ReaderException When no (valid) sudoku could be read.
     */
    private function prepareImageForOCR(CV\Mat $image): CV\Mat
    {
        // Clone the image before processing it for later reference.
        $originalImage = $image->clone();

        // Transform the image to grayscale.
        $image = CV\cvtColor($image, CV\COLOR_BGR2GRAY);

        // Blur the image and apply adaptive treshold  to find the outlines.
        CV\GaussianBlur($image, $image, new CV\Size(5, 5), 0);
        CV\adaptiveThreshold($image, $image, 255, CV\ADAPTIVE_THRESH_MEAN_C, CV\THRESH_BINARY_INV, 5, 2);

        // Get all contours of the image.
        $contours = [];
        CV\findContoursWithoutHierarchy($image, $contours, CV\RETR_EXTERNAL, CV\CHAIN_APPROX_SIMPLE);

        // Find the largest rectangle contour, this is most likely the sudoku.
        $largestContour = null;
        $largestSize = 0;
        foreach ($contours as $contour) {
            $rect = CV\boundingRect($contour);
            $size = $rect->width * $rect->height;
            if ($size > $largestSize) {
                $largestSize = $size;
                $largestContour = $contour;
            }
        }

        // Find the four corners of the largest contour.
        $contour = [];
        foreach ($largestContour as $index => $point) {
            if (count($contour) === 0) {
                $contour['TL'] = $point;
                $contour['TR'] = $point;
                $contour['BR'] = $point;
                $contour['BL'] = $point;
                continue;
            }

            $sum = $point->x + $point->y;
            $min = $point->x - $point->y;
            if ($sum < $contour['TL']->x + $contour['TL']->y) {
                $contour['TL'] = $point;
            }
            if ($sum > $contour['BR']->x + $contour['BR']->y) {
                $contour['BR'] = $point;
            }
            if ($min < $contour['BL']->x - $contour['BL']->y) {
                $contour['BL'] = $point;
            }
            if ($min > $contour['TR']->x - $contour['TR']->y) {
                $contour['TR'] = $point;
            }
        }

        $size = 720;

        // Warp the four corners to a new image.
        $destinationContour = [
            new CV\Point(0, 0),
            new CV\Point($size - 1, 0),
            new CV\Point($size - 1, $size - 1),
            new CV\Point(0, $size - 1),
        ];
        $M = CV\getPerspectiveTransform($contour, $destinationContour);

        // Return to the original image and warp it to the new perspective so only the sudoku is in the image.
        $image = $originalImage;
        CV\warpPerspective($image, $image, $M, new CV\Size($size, $size));

        // Transform the image to grayscale.
        $image = CV\cvtColor($image, CV\COLOR_BGR2GRAY);

        // Edit every cell seperately.
        $cellSize = $size / 9;
        $numberCount = 0;
        for ($row = 1; $row <= 9; $row++) {
            for ($column = 1; $column <= 9; $column++) {
                // Isolate the cell.
                $x = floor($cellSize * ($column - 1));
                $y = floor($cellSize * ($row - 1));
                $cellImage = $image->getImageROI(new CV\Rect($x, $y, $cellSize, $cellSize));

                // Apply otsu thresholding.
                CV\threshold($cellImage, $cellImage, CV\ADAPTIVE_THRESH_MEAN_C, 255, CV\THRESH_BINARY_INV | CV\THRESH_OTSU);

                // Remove borders and noise.
                $contours = [];
                CV\findContoursWithoutHierarchy($cellImage, $contours, CV\RETR_LIST, CV\CHAIN_APPROX_NONE);
                $numberRect = null;
                $numberContours = null;
                foreach ($contours as $contour) {
                    $rect = CV\boundingRect($contour);
                    if (
                        (!$numberRect || ($rect->size() > $numberRect->size())) // Only one rectangle can be the number.
                        && $rect->width >= $cellSize * 0.05 && $rect->width <= $cellSize * 0.7 // Between min and max width.
                        && $rect->height >= $cellSize * 0.3 && $rect->height <= $cellSize * 0.9 // Between min and max height.
                        && $rect->x <= $cellSize * 0.6 && $rect->y <= $cellSize * 0.5 // Make sure it starts in the top left.
                    ) {
                        // This is the number, or it contains the previously found number.
                        if (!$numberRect) {
                            $numberCount++;
                        }
                        $numberRect = $rect;
                        $numberContours = [$contour];
                    } elseif (
                        !$numberRect
                        || $rect->x < $numberRect->x || $rect->x + $rect->width > $numberRect->x + $numberRect->width
                        || $rect->y < $numberRect->y || $rect->y + $rect->height > $numberRect->y + $numberRect->height
                    ) {
                        // This rectangle is outside the number rectangle, or there is no number rectangle yet, delete it.
                        $removeContours = [$contour];
                        CV\drawContours($cellImage, $removeContours, 0, new CV\Scalar(0, 0, 0), CV\FILLED);
                    }
                }
                if ($numberContours) {
                    // Re-draw the number.
                    CV\drawContours($cellImage, $numberContours, 0, new CV\Scalar(255, 255, 255), CV\FILLED);

                    // Fill contours inside this number with black (for example the holes in the 0, 4, 6, 8 and 9).
                    foreach ($contours as $contour) {
                        $rect = CV\boundingRect($contour);
                        if ($rect->x > $numberRect->x && $rect->x + $rect->width < $numberRect->x + $numberRect->width
                            && $rect->x > $numberRect->x && $rect->x + $rect->width < $numberRect->x + $numberRect->width
                        ) {
                            $removeContours = [$contour];
                            CV\drawContours($cellImage, $removeContours, 0, new CV\Scalar(0, 0, 0), CV\FILLED);
                        }
                    }
                }
            }
        }

        if ($numberCount < 17) {
            // There are less than 17 number cells. At least 17 numbers are required to solve a sudoku.
            throw new ReaderException('Not enough cells with a number recognized to generate a sudoku.');
        }

        return $image;
    }
}
