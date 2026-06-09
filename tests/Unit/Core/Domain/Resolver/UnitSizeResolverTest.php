<?php

use App\Core\Domain\Resolver\UnitSizeResolver;

it('resolves unit sizes from the tamanhos sheet values', function () {
    $resolver = new UnitSizeResolver;

    expect($resolver->findByCode('1B PC'))->toMatchArray([
        'code' => '1B PM',
        'standard_size' => '38 x22',
        'standard_area' => '850m²',
    ])
        ->and($resolver->findByCode('Central de Custódia DPT'))->toMatchArray([
            'standard_size' => '23,8 x 8,6',
            'standard_area' => '205 m²',
        ])
        ->and($resolver->search('conjugada'))->toHaveCount(2);
});
