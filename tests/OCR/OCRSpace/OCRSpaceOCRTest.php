<?php

namespace Stanjan\Sudoku\Tests\OCR\OCRSpace;

use PHPUnit\Framework\TestCase;
use Stanjan\Sudoku\Grid\Grid;
use Stanjan\Sudoku\Grid\GridSize;
use Stanjan\Sudoku\OCR\OCRSpace\OCRSpaceClient;
use Stanjan\Sudoku\OCR\OCRSpace\OCRSpaceOCR;
use Stanjan\Sudoku\Variant\Default\DefaultSudoku;

/**
 * @covers \Stanjan\Sudoku\OCR\OCRSpace\OCRSpaceOCR
 */
class OCRSpaceOCRTest extends TestCase
{
    public function testFillDefault(): void
    {
        // Mock the actual response for this image (17-05-2021 10:12).
        $clientMock = $this->createMock(OCRSpaceClient::class);
        $clientMock->expects($this->once())
            ->method('requestDataForImage')
            ->willReturn($this->mockDataForWords([
                [
                    'WordText' => '6',
                    'Left' => 671,
                    'Top' => 28,
                    'Height' => 27,
                    'Width' => 18,
                ], [
                    'WordText' => '1',
                    'Left' => 33,
                    'Top' => 28,
                    'Height' => 28,
                    'Width' => 16,
                ], [
                    'WordText' => '6',
                    'Left' => 192,
                    'Top' => 105,
                    'Height' => 32,
                    'Width' => 21,
                ], [
                    'WordText' => '2',
                    'Left' => 348,
                    'Top' => 104,
                    'Height' => 34,
                    'Width' => 25,
                ], [
                    'WordText' => '7',
                    'Left' => 507,
                    'Top' => 103,
                    'Height' => 34,
                    'Width' => 23,
                ], [
                    'WordText' => '1',
                    'Left' => 511,
                    'Top' => 183,
                    'Height' => 31,
                    'Width' => 17,
                ], [
                    'WordText' => '3',
                    'Left' => 671,
                    'Top' => 183,
                    'Height' => 33,
                    'Width' => 22,
                ], [
                    'WordText' => '789',
                    'Left' => 34,
                    'Top' => 186,
                    'Height' => 30,
                    'Width' => 178,
                ], [
                    'WordText' => '4',
                    'Left' => 271,
                    'Top' => 185,
                    'Height' => 31,
                    'Width' => 21,
                ], [
                    'WordText' => '5',
                    'Left' => 350,
                    'Top' => 184,
                    'Height' => 32,
                    'Width' => 20,
                ], [
                    'WordText' => '4',
                    'Left' => 671,
                    'Top' => 262,
                    'Height' => 33,
                    'Width' => 22,
                ], [
                    'WordText' => '8',
                    'Left' => 270,
                    'Top' => 267,
                    'Height' => 29,
                    'Width' => 24,
                ], [
                    'WordText' => '7',
                    'Left' => 431,
                    'Top' => 264,
                    'Height' => 31,
                    'Width' => 20,
                ], [
                    'WordText' => '3',
                    'Left' => 347,
                    'Top' => 342,
                    'Height' => 35,
                    'Width' => 27,
                ], [
                    'WordText' => '4',
                    'Left' => 431,
                    'Top' => 422,
                    'Height' => 31,
                    'Width' => 20,
                ], [
                    'WordText' => '2',
                    'Left' => 511,
                    'Top' => 422,
                    'Height' => 31,
                    'Width' => 20,
                ], [
                    'WordText' => '1',
                    'Left' => 672,
                    'Top' => 422,
                    'Height' => 31,
                    'Width' => 15,
                ], [
                    'WordText' => '9',
                    'Left' => 113,
                    'Top' => 423,
                    'Height' => 32,
                    'Width' => 19,
                ], [
                    'WordText' => '4',
                    'Left' => 590,
                    'Top' => 508,
                    'Height' => 26,
                    'Width' => 20,
                ], [
                    'WordText' => '3',
                    'Left' => 34,
                    'Top' => 504,
                    'Height' => 32,
                    'Width' => 18,
                ], [
                    'WordText' => '1',
                    'Left' => 114,
                    'Top' => 504,
                    'Height' => 32,
                    'Width' => 14,
                ], [
                    'WordText' => '2',
                    'Left' => 194,
                    'Top' => 504,
                    'Height' => 32,
                    'Width' => 18,
                ], [
                    'WordText' => '9',
                    'Left' => 272,
                    'Top' => 504,
                    'Height' => 31,
                    'Width' => 20,
                ], [
                    'WordText' => '7',
                    'Left' => 352,
                    'Top' => 504,
                    'Height' => 30,
                    'Width' => 20,
                ], [
                    'WordText' => '1',
                    'Left' => 354,
                    'Top' => 580,
                    'Height' => 35,
                    'Width' => 14,
                ], [
                    'WordText' => '2',
                    'Left' => 433,
                    'Top' => 583,
                    'Height' => 32,
                    'Width' => 20,
                ], [
                    'WordText' => '78',
                    'Left' => 591,
                    'Top' => 581,
                    'Height' => 34,
                    'Width' => 100,
                ], [
                    'WordText' => '4',
                    'Left' => 111,
                    'Top' => 586,
                    'Height' => 27,
                    'Width' => 20,
                ], [
                    'WordText' => '9',
                    'Left' => 29,
                    'Top' => 661,
                    'Height' => 35,
                    'Width' => 28,
                ], [
                    'WordText' => '8',
                    'Left' => 192,
                    'Top' => 663,
                    'Height' => 31,
                    'Width' => 23,
                ],
            ]));

        $ocr = new OCRSpaceOCR($clientMock);
        $gridSize = new GridSize(9, 9);
        $subGridSize = new GridSize(3, 3);
        $grid = new Grid($gridSize, $subGridSize);
        $sudoku = new DefaultSudoku($grid);

        $ocr->fillSudokuFromImage($sudoku, __DIR__.'/../../images/sudoku-default-ocr.jpg');

        $answers = [
            [1, null, null, null, null, null, null, null, 6],
            [null, null, 6, null, 2, null, 7, null, null],
            [7, 8, 9, 4, 5, null, 1, null, 3],
            [null, null, null, 8, null, 7, null, null, 4],
            [null, null, null, null, 3, null, null, null, null],
            [null, 9, null, null, null, 4, 2, null, 1],
            [3, 1, 2, 9, 7, null, null, 4, null],
            [null, 4, null, null, 1, 2, null, 7, 8],
            [9, null, 8, null, null, null, null, null, null],
        ];
        foreach ($answers as $row => $columns) {
            foreach ($columns as $column => $answer) {
                $this->assertSame($answer, $sudoku->getAnswer($row + 1, $column + 1));
            }
        }
    }

    public function testFillPhoto1(): void
    {
        // Mock the actual response for this image (17-05-2021 10:12).
        $clientMock = $this->createMock(OCRSpaceClient::class);
        $clientMock->expects($this->once())
            ->method('requestDataForImage')
            ->willReturn($this->mockDataForWords([
                [
                    'WordText' => '9',
                    'Left' => 20,
                    'Top' => 18,
                    'Height' => 54,
                    'Width' => 32,
                ], [
                    'WordText' => '3',
                    'Left' => 98,
                    'Top' => 16,
                    'Height' => 52,
                    'Width' => 32,
                ], [
                    'WordText' => '6',
                    'Left' => 174,
                    'Top' => 16,
                    'Height' => 52,
                    'Width' => 32,
                ], [
                    'WordText' => '2',
                    'Left' => 500,
                    'Top' => 10,
                    'Height' => 50,
                    'Width' => 32,
                ], [
                    'WordText' => '4',
                    'Left' => 1,
                    'Top' => 176,
                    'Height' => 75,
                    'Width' => 123,
                ], [
                    'WordText' => '8',
                    'Left' => 255,
                    'Top' => 169,
                    'Height' => 57,
                    'Width' => 32,
                ], [
                    'WordText' => '2',
                    'Left' => 336,
                    'Top' => 171,
                    'Height' => 52,
                    'Width' => 32,
                ], [
                    'WordText' => '1',
                    'Left' => 416,
                    'Top' => 169,
                    'Height' => 52,
                    'Width' => 20,
                ], [
                    'WordText' => '4',
                    'Left' => 17,
                    'Top' => 256,
                    'Height' => 63,
                    'Width' => 37,
                ], [
                    'WordText' => '7',
                    'Left' => 96,
                    'Top' => 249,
                    'Height' => 62,
                    'Width' => 39,
                ], [
                    'WordText' => '2',
                    'Left' => 168,
                    'Top' => 249,
                    'Height' => 61,
                    'Width' => 32,
                ], [
                    'WordText' => '9',
                    'Left' => 334,
                    'Top' => 88,
                    'Height' => 54,
                    'Width' => 34,
                ], [
                    'WordText' => '3',
                    'Left' => 412,
                    'Top' => 86,
                    'Height' => 56,
                    'Width' => 32,
                ], [
                    'WordText' => '7',
                    'Left' => 502,
                    'Top' => 86,
                    'Height' => 52,
                    'Width' => 30,
                ], [
                    'WordText' => '4',
                    'Left' => 576,
                    'Top' => 86,
                    'Height' => 50,
                    'Width' => 36,
                ], [
                    'WordText' => '9',
                    'Left' => 658,
                    'Top' => 163,
                    'Height' => 54,
                    'Width' => 37,
                ], [
                    'WordText' => '6',
                    'Left' => 658,
                    'Top' => 242,
                    'Height' => 55,
                    'Width' => 37,
                ], [
                    'WordText' => '7',
                    'Left' => 256,
                    'Top' => 335,
                    'Height' => 54,
                    'Width' => 31,
                ], [
                    'WordText' => '5',
                    'Left' => 336,
                    'Top' => 334,
                    'Height' => 54,
                    'Width' => 31,
                ], [
                    'WordText' => '9',
                    'Left' => 412,
                    'Top' => 331,
                    'Height' => 58,
                    'Width' => 31,
                ], [
                    'WordText' => '3',
                    'Left' => 496,
                    'Top' => 411,
                    'Height' => 59,
                    'Width' => 36,
                ], [
                    'WordText' => '7',
                    'Left' => 576,
                    'Top' => 408,
                    'Height' => 57,
                    'Width' => 37,
                ], [
                    'WordText' => '8',
                    'Left' => 658,
                    'Top' => 404,
                    'Height' => 59,
                    'Width' => 36,
                ], [
                    'WordText' => '2',
                    'Left' => 575,
                    'Top' => 489,
                    'Height' => 57,
                    'Width' => 36,
                ], [
                    'WordText' => '1',
                    'Left' => 23,
                    'Top' => 416,
                    'Height' => 57,
                    'Width' => 24,
                ], [
                    'WordText' => '5',
                    'Left' => 20,
                    'Top' => 498,
                    'Height' => 54,
                    'Width' => 35,
                ], [
                    'WordText' => '4',
                    'Left' => 252,
                    'Top' => 498,
                    'Height' => 56,
                    'Width' => 38,
                ], [
                    'WordText' => '1',
                    'Left' => 322,
                    'Top' => 487,
                    'Height' => 93,
                    'Width' => 37,
                ], [
                    'WordText' => '6',
                    'Left' => 398,
                    'Top' => 483,
                    'Height' => 94,
                    'Width' => 53,
                ], [
                    'WordText' => '2',
                    'Left' => 97,
                    'Top' => 578,
                    'Height' => 61,
                    'Width' => 36,
                ], [
                    'WordText' => '1',
                    'Left' => 176,
                    'Top' => 580,
                    'Height' => 54,
                    'Width' => 22,
                ], [
                    'WordText' => '3',
                    'Left' => 254,
                    'Top' => 578,
                    'Height' => 58,
                    'Width' => 34,
                ], [
                    'WordText' => '7',
                    'Left' => 334,
                    'Top' => 578,
                    'Height' => 56,
                    'Width' => 34,
                ], [
                    'WordText' => '4',
                    'Left' => 172,
                    'Top' => 657,
                    'Height' => 54,
                    'Width' => 37,
                ], [
                    'WordText' => '1',
                    'Left' => 501,
                    'Top' => 653,
                    'Height' => 56,
                    'Width' => 24,
                ], [
                    'WordText' => '5',
                    'Left' => 576,
                    'Top' => 651,
                    'Height' => 56,
                    'Width' => 36,
                ], [
                    'WordText' => '7',
                    'Left' => 657,
                    'Top' => 651,
                    'Height' => 55,
                    'Width' => 34,
                ],
            ]));

        $ocr = new OCRSpaceOCR($clientMock);
        $gridSize = new GridSize(9, 9);
        $subGridSize = new GridSize(3, 3);
        $grid = new Grid($gridSize, $subGridSize);
        $sudoku = new DefaultSudoku($grid);

        $ocr->fillSudokuFromImage($sudoku, __DIR__.'/../../images/sudoku-photo-1-ocr.jpg');

        $answers = [
            [9, 3, 6, null, null, null, 2, null, null],
            [null, null, null, null, 9, 3, 7, 4, null],
            [null, 4, null, 8, 2, 1, null, null, 9],
            [4, 7, 2, null, null, null, null, null, 6],
            [null, null, null, 7, 5, 9, null, null, null],
            [1, null, null, null, null, null, 3, 7, 8],
            [5,  null, null, 4, 1, 6, null, 2, null],
            [null, 2, 1, 3, 7, null, null, null, null],
            [null, null, 4, null, null, null, 1, 5, 7],
        ];
        foreach ($answers as $row => $columns) {
            foreach ($columns as $column => $answer) {
                $this->assertSame($answer, $sudoku->getAnswer($row + 1, $column + 1));
            }
        }
    }


    public function testFillPhoto2(): void
    {
        // Mock the actual response for this image (17-05-2021 10:12).
        $clientMock = $this->createMock(OCRSpaceClient::class);
        $clientMock->expects($this->once())
            ->method('requestDataForImage')
            ->willReturn($this->mockDataForWords([
                [
                    'WordText' => '6',
                    'Left' => 269,
                    'Top' => 32,
                    'Height' => 47,
                    'Width' => 35,
                ], [
                    'WordText' => '4',
                    'Left' => 427,
                    'Top' => 38,
                    'Height' => 42,
                    'Width' => 35,
                ], [
                    'WordText' => '7',
                    'Left' => 506,
                    'Top' => 36,
                    'Height' => 44,
                    'Width' => 34,
                ], [
                    'WordText' => '9',
                    'Left' => 661,
                    'Top' => 102,
                    'Height' => 53,
                    'Width' => 38,
                ], [
                    'WordText' => '7',
                    'Left' => 15,
                    'Top' => 96,
                    'Height' => 56,
                    'Width' => 46,
                ], [
                    'WordText' => '6',
                    'Left' => 186,
                    'Top' => 107,
                    'Height' => 51,
                    'Width' => 40,
                ], [
                    'WordText' => '5',
                    'Left' => 428,
                    'Top' => 191,
                    'Height' => 49,
                    'Width' => 36,
                ], [
                    'WordText' => '8',
                    'Left' => 583,
                    'Top' => 190,
                    'Height' => 50,
                    'Width' => 36,
                ], [
                    'WordText' => '7',
                    'Left' => 96,
                    'Top' => 253,
                    'Height' => 58,
                    'Width' => 48,
                ], [
                    'WordText' => '2',
                    'Left' => 348,
                    'Top' => 264,
                    'Height' => 52,
                    'Width' => 36,
                ], [
                    'WordText' => '9',
                    'Left' => 583,
                    'Top' => 267,
                    'Height' => 51,
                    'Width' => 35,
                ], [
                    'WordText' => '3',
                    'Left' => 661,
                    'Top' => 262,
                    'Height' => 53,
                    'Width' => 36,
                ], [
                    'WordText' => '5',
                    'Left' => 632,
                    'Top' => 335,
                    'Height' => 69,
                    'Width' => 64,
                ], [
                    'WordText' => '8',
                    'Left' => 24,
                    'Top' => 336,
                    'Height' => 54,
                    'Width' => 35,
                ], [
                    'WordText' => '44',
                    'Left' => 25,
                    'Top' => 420,
                    'Height' => 50,
                    'Width' => 38,
                ], [
                    'WordText' => '3',
                    'Left' => 105,
                    'Top' => 417,
                    'Height' => 55,
                    'Width' => 35,
                ], [
                    'WordText' => '1',
                    'Left' => 354,
                    'Top' => 424,
                    'Height' => 49,
                    'Width' => 23,
                ], [
                    'WordText' => '7',
                    'Left' => 580,
                    'Top' => 422,
                    'Height' => 53,
                    'Width' => 39,
                ], [
                    'WordText' => '5',
                    'Left' => 110,
                    'Top' => 502,
                    'Height' => 50,
                    'Width' => 36,
                ], [
                    'WordText' => '2',
                    'Left' => 270,
                    'Top' => 500,
                    'Height' => 51,
                    'Width' => 38,
                ], [
                    'WordText' => '2',
                    'Left' => 502,
                    'Top' => 579,
                    'Height' => 56,
                    'Width' => 48,
                ], [
                    'WordText' => '8',
                    'Left' => 660,
                    'Top' => 578,
                    'Height' => 54,
                    'Width' => 44,
                ], [
                    'WordText' => '3',
                    'Left' => 21,
                    'Top' => 576,
                    'Height' => 56,
                    'Width' => 44,
                ], [
                    'WordText' => '2',
                    'Left' => 189,
                    'Top' => 658,
                    'Height' => 49,
                    'Width' => 36,
                ], [
                    'WordText' => '3',
                    'Left' => 271,
                    'Top' => 658,
                    'Height' => 51,
                    'Width' => 36,
                ], [
                    'WordText' => '1',
                    'Left' => 435,
                    'Top' => 658,
                    'Height' => 47,
                    'Width' => 16,
                ],
            ]));

        $ocr = new OCRSpaceOCR($clientMock);
        $gridSize = new GridSize(9, 9);
        $subGridSize = new GridSize(3, 3);
        $grid = new Grid($gridSize, $subGridSize);
        $sudoku = new DefaultSudoku($grid);

        $ocr->fillSudokuFromImage($sudoku, __DIR__.'/../../images/sudoku-photo-2-ocr.jpg');

        $answers = [
            [null, null, null, 6, null, 4, 7, null, null],
            [7, null, 6, null, null, null, null, null, 9],
            [null, null, null, null, null, 5, null, 8, null],
            [null, 7, null, null, 2, null, null, 9, 3],
            [8, null, null, null, null, null, null, null, 5],
            [4, 3, null, null, 1, null, null, 7, null],
            [null, 5, null, 2, null, null, null, null, null],
            [3, null, null, null, null, null, 2, null, 8],
            [null, null, 2, 3, null, 1, null, null, null],
        ];
        foreach ($answers as $row => $columns) {
            foreach ($columns as $column => $answer) {
                $this->assertSame($answer, $sudoku->getAnswer($row + 1, $column + 1));
            }
        }
    }

    public function testFillPhoto3(): void
    {
        // Mock the actual response for this image (17-05-2021 10:12).
        $clientMock = $this->createMock(OCRSpaceClient::class);
        $clientMock->expects($this->once())
            ->method('requestDataForImage')
            ->willReturn($this->mockDataForWords([
                [
                    'WordText' => '9',
                    'Left' => 30,
                    'Top' => 27,
                    'Height' => 29,
                    'Width' => 20,
                ], [
                    'WordText' => '5',
                    'Left' => 191,
                    'Top' => 32,
                    'Height' => 29,
                    'Width' => 18,
                ], [
                    'WordText' => 'J',
                    'Left' => 268,
                    'Top' => 26,
                    'Height' => 52,
                    'Width' => 18,
                ], [
                    'WordText' => '1',
                    'Left' => 352,
                    'Top' => 28,
                    'Height' => 32,
                    'Width' => 10,
                ], [
                    'WordText' => '6',
                    'Left' => 428,
                    'Top' => 34,
                    'Height' => 28,
                    'Width' => 16,
                ], [
                    'WordText' => '7',
                    'Left' => 508,
                    'Top' => 30,
                    'Height' => 29,
                    'Width' => 21,
                ], [
                    'WordText' => '2',
                    'Left' => 668,
                    'Top' => 24,
                    'Height' => 31,
                    'Width' => 21,
                ], [
                    'WordText' => '3',
                    'Left' => 26,
                    'Top' => 109,
                    'Height' => 50,
                    'Width' => 67,
                ], [
                    'WordText' => '3',
                    'Left' => 111,
                    'Top' => 109,
                    'Height' => 31,
                    'Width' => 21,
                ], [
                    'WordText' => '2',
                    'Left' => 186,
                    'Top' => 113,
                    'Height' => 28,
                    'Width' => 20,
                ], [
                    'WordText' => '7',
                    'Left' => 272,
                    'Top' => 116,
                    'Height' => 28,
                    'Width' => 17,
                ], [
                    'WordText' => '8',
                    'Left' => 348,
                    'Top' => 115,
                    'Height' => 26,
                    'Width' => 18,
                ], [
                    'WordText' => '4',
                    'Left' => 426,
                    'Top' => 109,
                    'Height' => 30,
                    'Width' => 21,
                ], [
                    'WordText' => '9',
                    'Left' => 507,
                    'Top' => 108,
                    'Height' => 30,
                    'Width' => 21,
                ], [
                    'WordText' => '1',
                    'Left' => 590,
                    'Top' => 106,
                    'Height' => 32,
                    'Width' => 11,
                ], [
                    'WordText' => '5',
                    'Left' => 665,
                    'Top' => 110,
                    'Height' => 29,
                    'Width' => 23,
                ], [
                    'WordText' => '7',
                    'Left' => 113,
                    'Top' => 189,
                    'Height' => 31,
                    'Width' => 20,
                ], [
                    'WordText' => '2',
                    'Left' => 271,
                    'Top' => 189,
                    'Height' => 29,
                    'Width' => 19,
                ], [
                    'WordText' => '5',
                    'Left' => 349,
                    'Top' => 190,
                    'Height' => 30,
                    'Width' => 21,
                ], [
                    'WordText' => '3',
                    'Left' => 503,
                    'Top' => 185,
                    'Height' => 33,
                    'Width' => 28,
                ], [
                    'WordText' => '6',
                    'Left' => 669,
                    'Top' => 195,
                    'Height' => 27,
                    'Width' => 22,
                ], [
                    'WordText' => '5',
                    'Left' => 31,
                    'Top' => 273,
                    'Height' => 28,
                    'Width' => 21,
                ], [
                    'WordText' => '6',
                    'Left' => 114,
                    'Top' => 268,
                    'Height' => 30,
                    'Width' => 20,
                ], [
                    'WordText' => '7',
                    'Left' => 428,
                    'Top' => 275,
                    'Height' => 27,
                    'Width' => 14,
                ], [
                    'WordText' => '8',
                    'Left' => 508,
                    'Top' => 268,
                    'Height' => 31,
                    'Width' => 20,
                ], [
                    'WordText' => '3',
                    'Left' => 582,
                    'Top' => 277,
                    'Height' => 27,
                    'Width' => 16,
                ], [
                    'WordText' => '7',
                    'Left' => 31,
                    'Top' => 345,
                    'Height' => 30,
                    'Width' => 21,
                ], [
                    'WordText' => 'a',
                    'Left' => 102,
                    'Top' => 354,
                    'Height' => 26,
                    'Width' => 26,
                ], [
                    'WordText' => '3',
                    'Left' => 192,
                    'Top' => 346,
                    'Height' => 32,
                    'Width' => 22,
                ], [
                    'WordText' => 'q',
                    'Left' => 346,
                    'Top' => 352,
                    'Height' => 28,
                    'Width' => 22,
                ], [
                    'WordText' => '?',
                    'Left' => 433,
                    'Top' => 354,
                    'Height' => 28,
                    'Width' => 22,
                ], [
                    'WordText' => '6',
                    'Left' => 508,
                    'Top' => 348,
                    'Height' => 30,
                    'Width' => 20,
                ], [
                    'WordText' => '1',
                    'Left' => 668,
                    'Top' => 347,
                    'Height' => 30,
                    'Width' => 14,
                ], [
                    'WordText' => '8',
                    'Left' => 10,
                    'Top' => 420,
                    'Height' => 50,
                    'Width' => 38,
                ], [
                    'WordText' => '9',
                    'Left' => 193,
                    'Top' => 425,
                    'Height' => 31,
                    'Width' => 21,
                ], [
                    'WordText' => '1',
                    'Left' => 272,
                    'Top' => 428,
                    'Height' => 26,
                    'Width' => 6,
                ], [
                    'WordText' => '2',
                    'Left' => 424,
                    'Top' => 425,
                    'Height' => 37,
                    'Width' => 104,
                ], [
                    'WordText' => '5',
                    'Left' => 586,
                    'Top' => 425,
                    'Height' => 31,
                    'Width' => 21,
                ], [
                    'WordText' => '7',
                    'Left' => 665,
                    'Top' => 434,
                    'Height' => 29,
                    'Width' => 19,
                ], [
                    'WordText' => '3',
                    'Left' => 13,
                    'Top' => 506,
                    'Height' => 41,
                    'Width' => 39,
                ], [
                    'WordText' => '6',
                    'Left' => 186,
                    'Top' => 507,
                    'Height' => 28,
                    'Width' => 19,
                ], [
                    'WordText' => '9',
                    'Left' => 352,
                    'Top' => 504,
                    'Height' => 31,
                    'Width' => 19,
                ], [
                    'WordText' => '2',
                    'Left' => 426,
                    'Top' => 503,
                    'Height' => 32,
                    'Width' => 25,
                ], [
                    'WordText' => '7',
                    'Left' => 587,
                    'Top' => 505,
                    'Height' => 29,
                    'Width' => 20,
                ], [
                    'WordText' => 'a',
                    'Left' => 24,
                    'Top' => 588,
                    'Height' => 26,
                    'Width' => 24,
                ], [
                    'WordText' => '5',
                    'Left' => 112,
                    'Top' => 586,
                    'Height' => 30,
                    'Width' => 20,
                ], [
                    'WordText' => '7',
                    'Left' => 193,
                    'Top' => 584,
                    'Height' => 31,
                    'Width' => 22,
                ], [
                    'WordText' => '83',
                    'Left' => 272,
                    'Top' => 584,
                    'Height' => 30,
                    'Width' => 94,
                ], [
                    'WordText' => '1',
                    'Left' => 403,
                    'Top' => 580,
                    'Height' => 48,
                    'Width' => 49,
                ], [
                    'WordText' => '6',
                    'Left' => 587,
                    'Top' => 580,
                    'Height' => 48,
                    'Width' => 25,
                ], [
                    'WordText' => '9',
                    'Left' => 651,
                    'Top' => 580,
                    'Height' => 48,
                    'Width' => 25,
                ], [
                    'WordText' => '7',
                    'Left' => 352,
                    'Top' => 664,
                    'Height' => 30,
                    'Width' => 19,
                ], [
                    'WordText' => '5',
                    'Left' => 421,
                    'Top' => 664,
                    'Height' => 28,
                    'Width' => 28,
                ], [
                    'WordText' => '3',
                    'Left' => 626,
                    'Top' => 657,
                    'Height' => 46,
                    'Width' => 69,
                ], [
                    'WordText' => '4',
                    'Left' => 30,
                    'Top' => 666,
                    'Height' => 29,
                    'Width' => 22,
                ]
            ]));

        $ocr = new OCRSpaceOCR($clientMock);
        $gridSize = new GridSize(9, 9);
        $subGridSize = new GridSize(3, 3);
        $grid = new Grid($gridSize, $subGridSize);
        $sudoku = new DefaultSudoku($grid);

        $ocr->fillSudokuFromImage($sudoku, __DIR__.'/../../images/sudoku-photo-3-ocr.jpg');

        $answers = [
            [9, null, 5, null, 1, 6, 7, null, 2],
            [null, 3, 2, 7, 8, 4, 9, 1, 5],
            [null, 7, null, 2, 5, null, 3, null, 6],
            [5, 6, null, null, null, 7, 8, 3, null],
            [7, null, 3, null, null, null, 6, null, 1],
            [8, null, 9, 1, null, null, 2, 5, 7],
            [3, null, 6, null, 9, 2, null, 7, null],
            [null, 5, 7, 8, 3, 1, null, 6, 9],
            [4, null, null, null, 7, 5, null, null, 3],
        ];
        foreach ($answers as $row => $columns) {
            foreach ($columns as $column => $answer) {
                $this->assertSame($answer, $sudoku->getAnswer($row + 1, $column + 1));
            }
        }
    }

    public function testFillSpecialCharacters(): void
    {
        $clientMock = $this->createMock(OCRSpaceClient::class);
        $clientMock->expects($this->once())
            ->method('requestDataForImage')
            ->willReturn($this->mockDataForWords([
                [
                    'WordText' => 'IASB',
                    'Left' => 20,
                    'Top' => 18,
                    'Height' => 54,
                    'Width' => 280,
                ],
            ]));

        $ocr = new OCRSpaceOCR($clientMock);
        $gridSize = new GridSize(9, 9);
        $subGridSize = new GridSize(3, 3);
        $grid = new Grid($gridSize, $subGridSize);
        $sudoku = new DefaultSudoku($grid);

        $ocr->fillSudokuFromImage($sudoku, __DIR__.'/../../images/sudoku-default-ocr.jpg');

        $answers = [
            [1, 4, 5, 8],
        ];
        foreach ($answers as $row => $columns) {
            foreach ($columns as $column => $answer) {
                $this->assertSame($answer, $sudoku->getAnswer($row + 1, $column + 1));
            }
        }
    }

    /**
     * Mocks the data of a response for the given words.
     *
     * @param array<mixed> $mockClientDataWords
     *
     * @return array<mixed>
     */
    private function mockDataForWords(array $mockClientDataWords): array
    {
        return [
            'ParsedResults' => [
                0 => [
                    'TextOverlay' => [
                        'Lines' => [[
                            'Words' => $mockClientDataWords,
                        ]],
                    ],
                ],
            ],
        ];
    }
}
