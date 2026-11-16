<?php

return [
    /*
     * Regra nova da ficha de custo (por m²/chapa):
     *  - não soma itens abaixo de um componente que já tem custo próprio;
     *  - deduz m²/m³ da chapa pelos componentes da ficha quando a descrição não traz dimensões.
     *
     * false = regra antiga (custo por chapa, soma todos os níveis, bases só pela descrição).
     */
    'regra_nova' => (bool) env('FICHA_REGRA_NOVA', false),
];
