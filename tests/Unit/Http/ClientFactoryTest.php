<?php

namespace Nfse\Tests\Unit\Http;

use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Psr7\Response;
use Nfse\Enums\TipoAmbiente;
use Nfse\Http\ClientFactory;
use Nfse\Http\NfseContext;

function opcoesDaRequisicao(NfseContext $context, callable $caminhoDoPfx): array
{
    $opcoes = [];
    $mock = new MockHandler([new Response(200, [], '{}')]);
    $context->httpHandler = function ($request, array $options) use ($mock, &$opcoes) {
        $opcoes = $options;

        return $mock($request, $options);
    };

    ClientFactory::make($context, 'https://example.test/', $caminhoDoPfx)->get('ping');

    return $opcoes;
}

it('usa as opções curl com o PFX quando não há httpOptions', function () {
    $context = new NfseContext(TipoAmbiente::Homologacao, 'fake/path.pfx', 'segredo');

    $opcoes = opcoesDaRequisicao($context, fn (): string => '/tmp/fake.pfx');

    expect($opcoes['curl'][CURLOPT_SSLCERT])->toBe('/tmp/fake.pfx')
        ->and($opcoes['curl'][CURLOPT_SSLCERTPASSWD])->toBe('segredo');
});

it('usa httpOptions no lugar das opções curl e não pede o caminho do PFX', function () {
    $context = new NfseContext(
        TipoAmbiente::Homologacao,
        'fake/path.pfx',
        'segredo',
        httpOptions: ['cert' => '/tmp/cert.pem', 'ssl_key' => '/tmp/chave.pem'],
    );

    $opcoes = opcoesDaRequisicao($context, function (): string {
        throw new \LogicException('O caminho do PFX não deveria ser resolvido.');
    });

    expect($opcoes)->not->toHaveKey('curl')
        ->and($opcoes['cert'])->toBe('/tmp/cert.pem')
        ->and($opcoes['ssl_key'])->toBe('/tmp/chave.pem')
        ->and($opcoes['verify'])->toBeTrue();
});
