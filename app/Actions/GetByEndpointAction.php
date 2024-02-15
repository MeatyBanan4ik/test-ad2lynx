<?php

namespace App\Actions;

use Illuminate\Support\Facades\Http;

class GetByEndpointAction {
    public function handle(string $endpoint) {
        $response = Http::get($endpoint);

        if($response->failed()) {
            abort(500);
        }

        return $response->json();
    }
}
