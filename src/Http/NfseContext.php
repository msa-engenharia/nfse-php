<?php

namespace Nfse\Http;

use Nfse\Enums\TipoAmbiente;

final class NfseContext
{
    public function __construct(
        public TipoAmbiente $ambiente,
        public ?string $certificatePath,
        public string $certificatePassword,
        public ?string $codigoMunicipio = null,
        public ?\Nfse\Dto\Http\Endpoint $endpoint = null,
        public ?string $certificateContent = null,
        /**
         * Opções extras do Guzzle (`cert`, `ssl_key`, `verify`...). Quando informadas,
         * o cliente HTTP NÃO usa as opções `curl` legadas do PFX (que o Guzzle 8 recusa)
         * e o PFX não é gravado em disco: quem chama traz o TLS mútuo pronto.
         */
        public ?array $httpOptions = null,
        /** Handler do Guzzle (ex.: `MockHandler` nos testes). */
        public mixed $httpHandler = null,
    ) {
        if ($certificatePath === null && $certificateContent === null) {
            throw new \InvalidArgumentException('Informe certificatePath ou certificateContent.');
        }
    }
}