<?php

use Tahdir\Arb\Events\ArbPaymentFailedEvent;
use Tahdir\Arb\Events\ArbPaymentSuccessEvent;
use Tahdir\Arb\Facades\Arb;
use Tahdir\Arb\Objects\Responses\SuccessPaymentResponse;

Route::post('/arb/response', function () {
    if (request()->has('trandata')) {
        $data = request()->trandata;
        $result = Arb::result($data);
        if ($result->success) {
            // payment success
            event(new ArbPaymentSuccessEvent(new SuccessPaymentResponse($result->data)));
        } else {
            // payment failed
            event(new ArbPaymentFailedEvent($result->data));
        }
        dd($result);
    }
});
