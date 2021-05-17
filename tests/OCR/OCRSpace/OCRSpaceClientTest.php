<?php

namespace Stanjan\Sudoku\Tests\OCR\OCRSpace;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Stanjan\Sudoku\Exception\OCRException;
use Stanjan\Sudoku\OCR\OCRSpace\OCRSpaceClient;

/**
 * @covers \Stanjan\Sudoku\OCR\OCRSpace\OCRSpaceClient
 */
class OCRSpaceClientTest extends TestCase
{
    public function testRequestDataForImage(): void
    {
        $testData = ['test' => 'test'];
        /** @var string $testDataJson */
        $testDataJson = json_encode($testData);

        $guzzleClientMock = $this->createMock(ClientInterface::class);
        $guzzleClientMock->expects($this->once())
            ->method('request')
            ->willReturn(new Response(200, [], $testDataJson));

        $client = new OCRSpaceClient('apikey', $guzzleClientMock);

        $data = $client->requestDataForImage(__DIR__.'/../../images/random-image.jpg');

        $this->assertSame($testData, $data);
    }

    public function testApiKeyThroughConstructor(): void
    {
        /** @var string $testJson */
        $testJson = json_encode(['test' => 'test']);

        $guzzleClientMock = $this->createMock(ClientInterface::class);
        $guzzleClientMock->expects($this->once())
            ->method('request')
            ->willReturn(new Response(200, [], $testJson));

        $client = new OCRSpaceClient('apikey', $guzzleClientMock);

        $client->requestDataForImage(__DIR__.'/../../images/random-image.jpg');
    }

    public function testApiKeyThroughSetter(): void
    {
        /** @var string $testJson */
        $testJson = json_encode(['test' => 'test']);

        $guzzleClientMock = $this->createMock(ClientInterface::class);
        $guzzleClientMock->expects($this->once())
            ->method('request')
            ->willReturn(new Response(200, [], $testJson));

        $client = new OCRSpaceClient(null, $guzzleClientMock);
        $client->setApiKey('apikey');

        $client->requestDataForImage(__DIR__.'/../../images/random-image.jpg');
    }

    public function testWithoutApiKey(): void
    {
        $client = new OCRSpaceClient();

        $this->expectException(OCRException::class);
        $this->expectExceptionMessage('The API key must be set before requesting any data from this client.');

        $client->requestDataForImage('testFilePath');
    }

    public function testInvalidJsonResponse(): void
    {
        $guzzleClientMock = $this->createMock(ClientInterface::class);
        $guzzleClientMock->expects($this->once())
            ->method('request')
            ->willReturn(new Response());

        $client = new OCRSpaceClient('apikey', $guzzleClientMock);
        
        $this->expectException(OCRException::class);
        $this->expectExceptionMessage('Invalid OCR Space API JSON result.');

        $client->requestDataForImage(__DIR__.'/../../images/random-image.jpg');
    }

    public function testAPIError(): void
    {
        /** @var string $errorJson */
        $errorJson = json_encode([
            'IsErroredOnProcessing' => true,
            'ErrorMessage' => [
                0 => 'Test error message.',
            ],
        ]);

        $guzzleClientMock = $this->createMock(ClientInterface::class);
        $guzzleClientMock->expects($this->once())
            ->method('request')
            ->willReturn(new Response(200, [], $errorJson));

        $this->expectException(OCRException::class);
        $this->expectExceptionMessage('Error calling the OCR Space API: Test error message.');

        $client = new OCRSpaceClient('apikey', $guzzleClientMock);

        $client->requestDataForImage(__DIR__.'/../../images/random-image.jpg');
    }
}
