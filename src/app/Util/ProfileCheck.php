<?php

namespace App\Util;

use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
use Mf2;

class ProfileCheck {

    protected $errors = [];
    protected $card = false;

    public function __construct(string $url, string $userAgent)
    {
        $httpClient = $this->makeHTTPClient($userAgent);

        try {
            $response = $httpClient->request('GET', $url);
        } catch (ConnectException $e) {
            $this->errors[] = "Error fetching URL. {$e->getMessage()}";
            return;
        } catch (RequestException $e) {
            $this->errors[] = "Error fetching URL. {$e->getMessage()}";
            return;
        }

        if ($response->getStatusCode() !== 200) {
            $this->errors[] = "URL returned status code {$response->getStatusCode()}";
            return;
        }

        $html = $response->getBody()->getContents();
        $mf2 = Mf2\parse($html, $url);
        $this->card = Mf2\HCard\representative($mf2, $url);
    }

  public function getCard() {
        return $this->card;
    }

  public function getErrors() {
        return $this->errors;
    }

    public static function makeHTTPClient(string $userAgent): HttpClient
    {
        return new HttpClient([
            'headers' => ['User-Agent' => $userAgent],
            'http_errors' => false,
            'timeout' => 15
        ]);
    }
}
