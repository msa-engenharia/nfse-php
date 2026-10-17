<?php

namespace Nfse\Http;

use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;

/**
 * Monta o cliente Guzzle dos serviços (SEFIN, ADN, CNC) num lugar só.
 *
 * Com `NfseContext::$httpOptions` o TLS mútuo vem pronto de quem chama (compatível com
 * Guzzle 7 e 8); sem ele, vale o caminho legado, por `curl` + PFX (somente Guzzle 7).
 */
final class ClientFactory
{
    /**
     * @param  callable(): string  $caminhoDoPfx  só é chamado no caminho legado
     */
    public static function make(NfseContext $context, string $baseUri, callable $caminhoDoPfx): Client
    {
        $opcoes = [
            'base_uri' => $baseUri,
            'handler' => Retry::stack($context->httpHandler),
            RequestOptions::HEADERS => [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ],
        ];

        if ($context->httpOptions !== null) {
            return new Client(array_replace([
                RequestOptions::CONNECT_TIMEOUT => 30,
                RequestOptions::TIMEOUT => 60,
                RequestOptions::VERIFY => true,
            ], $opcoes, $context->httpOptions));
        }

        $opcoes['curl'] = [
            CURLOPT_SSLCERTTYPE => 'P12',
            CURLOPT_SSLCERT => $caminhoDoPfx(),
            CURLOPT_SSLCERTPASSWD => $context->certificatePassword,
            CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4,
            CURLOPT_CONNECTTIMEOUT => 30,
            CURLOPT_TIMEOUT => 60,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_SSL_VERIFYPEER => 0,
        ];

        return new Client($opcoes);
    }
}
