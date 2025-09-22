<?php

if (!function_exists('number_to_uzbek_text')) {
    function number_to_uzbek_text($number) {
        return app(\App\Services\NumberToTextService::class)->convert($number);
    }
}
