<?php

namespace Stanjan\Sudoku\OCR\OCRSpace;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use Stanjan\Sudoku\Exception\OCRException;

/**
 * See: https://ocr.space/ocrapi
 */
class OCRSpaceClient
{
    private ClientInterface $client;

    public function __construct(
        private ?string $apiKey = null,
        ?ClientInterface $client = null,
    ) {
        $this->client = $client ?: new Client();
    }

    /**
     * Requests the OCR data for a given image.
     *
     * @return array<mixed>
     **
     * @throws OCRException
     * @throws GuzzleException
     */
    public function requestDataForImage(string $filePath): array
    {
        if (!$this->apiKey) {
            throw new OCRException('The API key must be set before requesting any data from this client.');
        }

        $fileType = pathinfo($filePath, PATHINFO_EXTENSION);
        /** @var string $content */
        $content = file_get_contents($filePath);
        $base64 = base64_encode($content);

        $response = $this->client->request('POST', 'https://api.ocr.space/parse/image', [
            'timeout' => 5,
            'headers' => [
                'apikey' => $this->apiKey,
            ],
            'form_params' => [
                'base64image' => 'data:image/'.$fileType.';base64,'.$base64,
                'isOverlayRequired' => 'true',
                'isTable' => 'true',
                'OCREngine' => 2,
            ],
        ]);

        $data = json_decode($response->getBody()->getContents(), true);
        if (!$data) {
            throw new OCRException('Invalid OCR Space API JSON result.');
        }

        if ($data['IsErroredOnProcessing'] ?? false) {
            throw new OCRException('Error calling the OCR Space API: '.($data['ErrorMessage'][0] ?? 'Unknown'));
        }

        return $data;
    }

    public function setApiKey(string $apiKey): void
    {
        $this->apiKey = $apiKey;
    }
}
