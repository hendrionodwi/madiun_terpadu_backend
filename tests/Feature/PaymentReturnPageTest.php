<?php

test('payment return page is publicly accessible', function () {
    $this->get(route('payment.return'))
        ->assertOk()
        ->assertViewIs('payment-return')
        ->assertSee('Pembayaran telah diproses')
        ->assertSee('Periksa status pembayaran');
});
