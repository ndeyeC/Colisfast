<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeocodingService
{
    public function geocode($adresse)
    {
        try {
            // Ajouter un contexte géographique si l'adresse est vague
            $adresseFormatee = str_contains(strtolower($adresse), 'senegal') ? $adresse : $adresse . ', Senegal';

            // Configurer le client HTTP avec User-Agent et désactivation SSL en local
            $httpClient = Http::withOptions([
                'verify' => app()->environment('local') ? false : true,
            ])->withHeaders([
                'User-Agent' => 'ColisFastApp/1.0 (contact: votre-email@example.com)',
            ]);

            $response = $httpClient->get("https://nominatim.openstreetmap.org/search", [
                'q' => $adresseFormatee,
                'format' => 'json',
                'limit' => 1
            ]);

            if ($response->successful() && !empty($response->json())) {
                $result = $response->json()[0];
                Log::info("Géocodage réussi pour l'adresse: {$adresseFormatee}", [
                    'original' => $adresse,
                    'lat' => $result['lat'],
                    'lon' => $result['lon'],
                    'response' => $result
                ]);
                return [
                    'lat' => (float) $result['lat'],
                    'lon' => (float) $result['lon']
                ];
            }

            Log::warning("Échec du géocodage pour l'adresse: {$adresseFormatee}", [
                'original' => $adresse,
                'response' => $response->json() ?? 'Réponse vide',
                'status' => $response->status()
            ]);
            return null;
        } catch (\Exception $e) {
            Log::error("Erreur lors du géocodage de l'adresse: {$adresseFormatee}", [
                'original' => $adresse,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }
}