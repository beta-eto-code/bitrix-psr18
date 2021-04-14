<?php


namespace BitrixPSR18;


use Bitrix\Main\Web\HttpClient;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class Client implements ClientInterface
{
    /**
     * @var HttpClient
     */
    private $httpClient;

    /**
     * Client constructor.
     * @param HttpClient|null $httpClient
     */
    public function __construct(HttpClient $httpClient = null)
    {
        $this->httpClient = $httpClient ?? new HttpClient();
    }

    /**
     * @param HttpClient $httpClient
     * @param RequestInterface $request
     */
    private function loadHeaders(HttpClient $httpClient, RequestInterface $request)
    {
        $httpClient->clearHeaders();
        foreach ($request->getHeaders() as $name => $values) {
            $httpClient->setHeader($name, implode(", ", $values));
        }
    }

    /**
     * @link https://github.com/php-http/multipart-stream-builder use for multipart request
     *
     * @param RequestInterface $request
     * @return ResponseInterface
     */
    public function sendRequest(RequestInterface $request): ResponseInterface
    {
        $method = strtolower($request->getMethod());
        $bxClient = clone $this->httpClient;
        $this->loadHeaders($bxClient, $request);

        $body = (string)$request->getBody();
        if (empty($body)) {
            $body = null;
        }

        $bxClient->query($method, (string)$request->getUri(), $body);
        $responseBody = $bxClient->getResult();
        if (empty($responseBody)) {
            $responseBody = null;
        }

        return new Response($bxClient->getStatus(), $bxClient->getHeaders()->toArray(), $responseBody);
    }
}