<?php

use App\BuildPanel\Application\Service\MunicipalityExtractorService;
use App\BuildPanel\Domain\Repository\TechnicalNotebookRepositoryInterface;
use App\BuildPanel\Infra\Mapper\TechnicalNotebookSheetMapper;
use App\Contract\Application\Interfaces\Adapter\ContractSheetAdapterInterface;
use App\Contract\Application\Interfaces\Mapper\ContractSheetMapperInterface;
use App\Contract\Domain\Entity\ContractEntity;
use App\Contract\Infra\Repository\SheetRepository\FindContractRecordsGoogleSheetRepository;
use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "pest()" function to bind a different classes or traits.
|
*/

pest()->extend(TestCase::class)
 // ->use(Illuminate\Foundation\Testing\RefreshDatabase::class)
    ->in('Feature');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

function something()
{
    // ..
}

/**
 * @param  list<string>  $municipalities
 * @param  list<string>  $contractMunicipalities
 */
function municipalityExtractorForTests(
    array $municipalities = ['Salvador', 'Andaraí', 'Antas', 'São Francisco do Conde', 'Ibotirama', 'Feira de Santana', 'Várzea Grande'],
    array $contractMunicipalities = [],
): MunicipalityExtractorService {
    $notebooks = Mockery::mock(TechnicalNotebookRepositoryInterface::class);
    $mapper = new TechnicalNotebookSheetMapper;
    $notebooks->shouldReceive('all')->andReturn(array_map(
        fn (string $municipality) => $mapper->fromRow(['MUNICIPIO' => $municipality]),
        $municipalities,
    ));
    $adapter = Mockery::mock(ContractSheetAdapterInterface::class);
    $adapter->shouldReceive('map')->andReturn($contractMunicipalities === [] ? [] : [
        new ContractEntity('01/2026', null, null, $contractMunicipalities),
    ]);

    return new MunicipalityExtractorService(
        $notebooks,
        new FindContractRecordsGoogleSheetRepository(
            $adapter,
            Mockery::mock(ContractSheetMapperInterface::class),
        ),
    );
}
