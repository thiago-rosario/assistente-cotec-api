<?php

declare(strict_types=1);

use App\TravelReport\Domain\Entity\RelatorioVistoriaTecnicaEntity;
use App\TravelReport\Domain\Enum\EstadoCadastroRelatorio;
use App\TravelReport\Domain\Event\ArmazenamentoDoRelatorioFalhou;
use App\TravelReport\Domain\Event\RelatorioVistoriaTecnicaArmazenadoComSucesso;
use App\TravelReport\Domain\ValueObject\DataVistoria;
use App\TravelReport\Domain\ValueObject\DocumentoPdf;
use App\TravelReport\Domain\ValueObject\IdExternoMensagem;
use App\TravelReport\Domain\ValueObject\Municipio;
use App\TravelReport\Domain\ValueObject\ProcessoSei;
use App\TravelReport\Domain\ValueObject\Responsavel;
use App\TravelReport\Exception\TransicaoDeEstadoInvalidaException;

function documentoDoRelatorio(): DocumentoPdf
{
    $content = "%PDF-1.7\nrelatório técnico";

    return new DocumentoPdf(
        mimeType: 'application/pdf',
        sizeBytes: strlen($content),
        originalFileName: 'relatorio.pdf',
        content: $content,
    );
}

function relatorioComMetadados(): RelatorioVistoriaTecnicaEntity
{
    return RelatorioVistoriaTecnicaEntity::iniciar(new IdExternoMensagem('message-001'))
        ->informarMunicipio(new Municipio('Salvador'))
        ->informarProcessoSei(ProcessoSei::semProcesso())
        ->informarData(new DataVistoria('22/07/2026', new DateTimeImmutable('2026-08-03')))
        ->informarResponsavel(new Responsavel('João Silva'));
}

it('avança o cadastro na ordem definida pelo fluxo', function () {
    $relatorio = RelatorioVistoriaTecnicaEntity::iniciar(new IdExternoMensagem('message-001'));

    expect($relatorio->estado())->toBe(EstadoCadastroRelatorio::AguardandoMunicipio);

    $relatorio->informarMunicipio(new Municipio('Salvador'));
    expect($relatorio->estado())->toBe(EstadoCadastroRelatorio::AguardandoProcesso);

    $relatorio->informarProcessoSei(ProcessoSei::semProcesso());
    expect($relatorio->estado())->toBe(EstadoCadastroRelatorio::AguardandoData);

    $relatorio->informarData(new DataVistoria('22/07/2026', new DateTimeImmutable('2026-08-03')));
    expect($relatorio->estado())->toBe(EstadoCadastroRelatorio::AguardandoResponsavel);

    $relatorio->informarResponsavel(new Responsavel('João Silva'));
    expect($relatorio->estado())->toBe(EstadoCadastroRelatorio::AguardandoDocumento)
        ->and($relatorio->estaProntoParaDocumento())->toBeTrue();
});

it('impede transições fora de ordem', function () {
    RelatorioVistoriaTecnicaEntity::iniciar(new IdExternoMensagem('message-001'))
        ->anexarDocumento(documentoDoRelatorio());
})->throws(TransicaoDeEstadoInvalidaException::class);

it('mantém o relatório pendente até Drive e Sheets confirmarem sucesso', function () {
    $relatorio = relatorioComMetadados()->anexarDocumento(documentoDoRelatorio());

    expect($relatorio->estado())->toBe(EstadoCadastroRelatorio::PendenteDeArmazenamento);

    $relatorio->confirmarArmazenamento();
})->throws(TransicaoDeEstadoInvalidaException::class);

it('armazena o relatório somente após as duas confirmações', function () {
    $relatorio = relatorioComMetadados()
        ->anexarDocumento(documentoDoRelatorio())
        ->registrarArmazenamentoNoDrive();

    expect($relatorio->estado())->toBe(EstadoCadastroRelatorio::PendenteDeCatalogo);

    $relatorio->confirmarCatalogacao()->confirmarArmazenamento();

    expect($relatorio->estado())->toBe(EstadoCadastroRelatorio::Armazenado)
        ->and($relatorio->estaArmazenado())->toBeTrue()
        ->and($relatorio->eventosPendentes())->toHaveCount(1)
        ->and($relatorio->eventosPendentes()[0])->toBeInstanceOf(RelatorioVistoriaTecnicaArmazenadoComSucesso::class);
});

it('registra falha de armazenamento e publica evento de domínio', function () {
    $relatorio = relatorioComMetadados()
        ->anexarDocumento(documentoDoRelatorio())
        ->registrarFalhaDeArmazenamento('Google Drive indisponível.');

    expect($relatorio->estado())->toBe(EstadoCadastroRelatorio::Falha)
        ->and($relatorio->motivoDaFalha())->toBe('Google Drive indisponível.')
        ->and($relatorio->eventosPendentes()[0])->toBeInstanceOf(ArmazenamentoDoRelatorioFalhou::class);
});
