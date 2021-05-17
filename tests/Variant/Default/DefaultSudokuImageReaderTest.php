<?php

namespace Stanjan\Sudoku\Tests\Variant\Default;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Stanjan\Sudoku\Exception\OCRException;
use Stanjan\Sudoku\Exception\ReaderException;
use Stanjan\Sudoku\OCR\OCRInterface;
use Stanjan\Sudoku\Variant\Default\DefaultSudoku;
use Stanjan\Sudoku\Variant\Default\DefaultSudokuGenerator;
use Stanjan\Sudoku\Variant\Default\DefaultSudokuImageReader;
use Stanjan\Sudoku\Variant\Default\DefaultSudokuVariant;

/**
 * @covers \Stanjan\Sudoku\Variant\Default\DefaultSudokuImageReader
 */
final class DefaultSudokuImageReaderTest extends TestCase
{
    public function testGetVariantClassName(): void
    {
        $this->assertSame(DefaultSudokuVariant::class, DefaultSudokuGenerator::getVariantClassName());
    }

    public function testReadDefault(): void
    {
        $reader = new DefaultSudokuImageReader($this->createOCRMock(__DIR__.'/../../images/sudoku-default-ocr.jpg'));
        $reader->read(__DIR__.'/../../images/sudoku-default.jpg');
    }

    public function testReadPhoto1(): void
    {
        $reader = new DefaultSudokuImageReader($this->createOCRMock(__DIR__.'/../../images/sudoku-photo-1-ocr.jpg'));
        $reader->read(__DIR__.'/../../images/sudoku-photo-1.jpg');
    }

    public function testReadPhoto2(): void
    {
        $reader = new DefaultSudokuImageReader($this->createOCRMock(__DIR__.'/../../images/sudoku-photo-2-ocr.jpg'));
        $reader->read(__DIR__.'/../../images/sudoku-photo-2.jpg');
    }

    public function testReadPhoto3(): void
    {
        $reader = new DefaultSudokuImageReader($this->createOCRMock(__DIR__.'/../../images/sudoku-photo-3-ocr.jpg'));
        $reader->read(__DIR__.'/../../images/sudoku-photo-3.jpg');
    }

    public function testReadInvalidFilePath(): void
    {
        $ocrMock = $this->createMock(OCRInterface::class);
        $reader = new DefaultSudokuImageReader($ocrMock);

        $this->expectException(ReaderException::class);
        $this->expectExceptionMessage('Could not load image: INVALID FILE PATH');

        $reader->read('INVALID FILE PATH');
    }

    public function testReadBlank(): void
    {
        $ocrMock = $this->createMock(OCRInterface::class);
        $reader = new DefaultSudokuImageReader($ocrMock);

        $this->expectException(ReaderException::class);
        $this->expectExceptionMessage('Not enough cells with a number recognized to generate a sudoku.');

        $reader->read(__DIR__.'/../../images/sudoku-blank.jpg');
    }

    public function testReadInsufficientAnswers(): void
    {
        $ocrMock = $this->createMock(OCRInterface::class);
        $reader = new DefaultSudokuImageReader($ocrMock);

        $this->expectException(ReaderException::class);
        $this->expectExceptionMessage('Not enough answers could be read to generate a sudoku.');

        $reader->read(__DIR__.'/../../images/random-image.jpg');
    }

    public function testCatchOCRException(): void
    {
        $ocrMock = $this->createMock(OCRInterface::class);
        $ocrMock->expects($this->once())
            ->method('fillSudokuFromImage')
            ->willReturnCallback(function () {
                throw new OCRException('OCRException test.');
            });

        $reader = new DefaultSudokuImageReader($ocrMock);

        $this->expectException(ReaderException::class);
        $this->expectExceptionMessage('Could not apply OCR: OCRException test.');

        $reader->read(__DIR__.'/../../images/random-image.jpg');
    }

    /**
     * Creates an OCR mock that expects the content of the given file path.
     * This makes sure the reader prepared for the OCR as expected.
     *
     * @return OCRInterface&MockObject
     */
    private function createOCRMock(string $duplicateFilePath): MockObject
    {
        $ocrMock = $this->createMock(OCRInterface::class);
        $ocrMock->expects($this->once())
            ->method('fillSudokuFromImage')
            ->with(
                $this->isInstanceOf(DefaultSudoku::class),
                $this->callback(fn ($filePath) => file_get_contents($filePath) === file_get_contents($duplicateFilePath))
            )
            ->willReturnCallback(function (DefaultSudoku $sudoku) {
                // Fill at least 17 answers so it passes the requirement checks.
                for ($row = 1; $row <= 9; $row++) {
                    for ($column = 1; $column <= 2; $column++) { // Just 2 columns so 2x9=18 answers will be set.
                        $sudoku->setAnswer($row, $column, 1); // The answer does not matter.
                    }
                }
            });

        return $ocrMock;
    }
}
