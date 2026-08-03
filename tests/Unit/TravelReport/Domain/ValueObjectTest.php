<?php

declare(strict_types=1);

use App\TravelReport\Domain\ValueObject\DataVistoria;
use App\TravelReport\Domain\ValueObject\DocumentoPdf;
use App\TravelReport\Domain\ValueObject\IdExternoMensagem;
use App\TravelReport\Domain\ValueObject\Municipio;
use App\TravelReport\Domain\ValueObject\ProcessoSei;
use App\TravelReport\Domain\ValueObject\Responsavel;
use App\TravelReport\Exception\DocumentoInvalidoException;

it('normaliza e compara municípios', function () {
    $municipio = new Municipio(' São   Luís ');

    expect($municipio->value)->toBe('São Luís')
        ->and($municipio->normalizado())->toBe('sao luis')
        ->and($municipio->equals(new Municipio('sao luis')))->toBeTrue();
});

it('rejeita município vazio', function () {
    new Municipio('   ');
})->throws(InvalidArgumentException::class);

it('valida processo SEI e representa explicitamente a ausência', function () {
    $processo = new ProcessoSei(' 012.3456.2026.0001234-00 ');
    $semProcesso = ProcessoSei::semProcesso();

    expect($processo->value)->toBe('012.3456.2026.0001234-00')
        ->and($processo->temProcesso())->toBeTrue()
        ->and($semProcesso->estaSemProcesso())->toBeTrue()
        ->and($semProcesso->value)->toBeNull();
});

it('rejeita processo SEI com formato inválido', function () {
    new ProcessoSei('012-3456-2026-0001234-00');
})->throws(InvalidArgumentException::class);

it('valida data brasileira e rejeita data futura', function () {
    $data = new DataVistoria('22/07/2026', new DateTimeImmutable('2026-08-03'));

    expect($data->formatada())->toBe('22/07/2026')
        ->and($data->iso8601())->toBe('2026-07-22');

    new DataVistoria('04/08/2026', new DateTimeImmutable('2026-08-03'));
})->throws(InvalidArgumentException::class, 'A data da vistoria técnica não pode ser futura.');

it('rejeita data inexistente', function () {
    new DataVistoria('31/02/2026');
})->throws(InvalidArgumentException::class);

it('normaliza e valida o responsável', function () {
    $responsavel = new Responsavel(' João   Silva ');

    expect($responsavel->value)->toBe('João Silva');

    new Responsavel(' ');
})->throws(InvalidArgumentException::class);

it('valida o identificador externo da mensagem', function () {
    expect(new IdExternoMensagem(' message-001 ')->value)->toBe('message-001');

    new IdExternoMensagem('');
})->throws(InvalidArgumentException::class);

it('valida documento PDF já decodificado por MIME, nome, assinatura e tamanho', function () {
    $content = "%PDF-1.7\nconteúdo";
    $documento = new DocumentoPdf(
        mimeType: 'application/pdf',
        sizeBytes: strlen($content),
        originalFileName: 'relatorio.pdf',
        content: $content,
        maxSizeBytes: 1024,
    );

    expect($documento->tipoMime())->toBe(DocumentoPdf::PdfMimeType)
        ->and($documento->tamanhoEmBytes())->toBe(strlen($content))
        ->and($documento->nomeOriginal())->toBe('relatorio.pdf');
});

it('permite PDF representado por caminho temporário', function () {
    $documento = new DocumentoPdf(
        mimeType: 'application/pdf',
        sizeBytes: 256,
        originalFileName: 'relatorio.pdf',
        temporaryPath: '/tmp/relatorio.pdf',
    );

    expect($documento->caminhoTemporario())->toBe('/tmp/relatorio.pdf');
});

it('rejeita documento que não é PDF ou excede o limite', function (string $mimeType, int $maxSizeBytes) {
    $content = "%PDF-1.7\nconteúdo";

    new DocumentoPdf(
        mimeType: $mimeType,
        sizeBytes: strlen($content),
        originalFileName: 'relatorio.pdf',
        content: $content,
        maxSizeBytes: $maxSizeBytes,
    );
})->with([
    'mime inválido' => ['application/msword', 1024],
    'tamanho excedido' => ['application/pdf', 4],
])->throws(DocumentoInvalidoException::class);

it('rejeita conteúdo binário sem assinatura PDF', function () {
    new DocumentoPdf(
        mimeType: 'application/pdf',
        sizeBytes: 10,
        originalFileName: 'relatorio.pdf',
        content: 'não é PDF',
    );
})->throws(DocumentoInvalidoException::class);
