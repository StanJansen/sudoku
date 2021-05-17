<?php

namespace Stanjan\Sudoku\OCR\OCRSpace;

use GuzzleHttp\Exception\GuzzleException;
use Stanjan\Sudoku\OCR\OCRInterface;
use Stanjan\Sudoku\SudokuInterface;

/**
 * OCR Space implementation.
 */
class OCRSpaceOCR implements OCRInterface
{
    private OCRSpaceClient $client;

    public function __construct(
        ?OCRSpaceClient $client = null,
    ) {
        $this->client = $client ?: new OCRSpaceClient();
    }

    /**
     * {@inheritDoc}
     *
     * @throws GuzzleException
     */
    public function fillSudokuFromImage(SudokuInterface $sudoku, string $filePath): void
    {
        // Get the OCRSpace data for this image.
        $data = $this->client->requestDataForImage($filePath);

        // Get the sudoku cell size in pixels relative to the image for calculating the cell by top-left pixels.
        [$width] = getimagesize($filePath);
        $cellSize = $width / $sudoku->getGrid()->getSize()->getColumnCount();

        // Loop every word to extract the numbers into the sudoku.
        foreach ($data['ParsedResults'][0]['TextOverlay']['Lines'] as $line) {
            foreach ($line['Words'] as $word) {
                // Reverse the characters as they are right aligned and might have whitespace to the left.
                $characters = array_reverse(str_split($word['WordText']));
                $row = (int) (floor(($word['Top'] + $word['Height'] - ($cellSize / 2)) / $cellSize) + 1);
                $column = (int) (floor(($word['Left'] + $word['Width'] - ($cellSize / 10)) / $cellSize) + 1);

                foreach ($characters as $character) {
                    $answer = $this->getAnswerForCharacter($character);
                    $sudoku->setAnswer($row, $column, $answer);

                    // Subtract to the previous column in case there are multiple characters placed in one word.
                    $column--;
                }
            }
        }
    }

    /**
     * Returns the answer for the given character.
     */
    private function getAnswerForCharacter(string $character): ?int
    {
        if (in_array($character, range('1', '9'))) {
            // Simple number, return it.
            return (int) $character;
        }

        // Handle special characters that can be mistaken.
        switch ($character) {
            case 'I':
                return 1;
            case 'A':
                return 4;
            case 'S':
                return 5;
            case 'B':
                return 8;
        }

        return null;
    }
}
