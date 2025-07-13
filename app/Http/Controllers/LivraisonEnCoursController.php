<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use App\Models\Commnande;
use App\Models\DeliveryRoute;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;

class LivraisonEnCoursController extends Controller
{
    public function index()
    {
        $livreurId = Auth::id();
        
        $livraisonActuelle = Commnande::where('driver_id', $livreurId)
            ->where('status', 'en_cours')
            ->with(['user', 'deliveryRoute'])
            ->first();
        
        $livraisonsEnAttente = Commnande::where('driver_id', $livreurId)
            ->where('status', 'acceptee')
            ->with(['user'])
            ->get();
        
        $statistiques = $this->getStatistiquesJour($livreurId);

        $progressPercentage = 0;
        if ($livraisonActuelle && $livraisonActuelle->deliveryRoute) {
            $progressPercentage = $this->calculateProgress($livraisonActuelle->deliveryRoute);
        }

        return view('livreur.livraison-cours', compact(
            'livraisonActuelle',
            'livraisonsEnAttente', 
            'statistiques',
            'progressPercentage'
        ));
    }

    public function apiLivraisonsEnCours()
    {
        $livreurId = Auth::id();
        
        $livraisonActuelle = Commnande::where('driver_id', $livreurId)
            ->where('status', 'en_cours')
            ->with(['user', 'deliveryRoute'])
            ->first();
        
        $livraisonsEnAttente = Commnande::where('driver_id', $livreurId)
            ->where('status', 'acceptee')
            ->with(['user'])
            ->orderBy('created_at', 'asc')
            ->get();
        
        $data = [
            'livraison_actuelle' => $livraisonActuelle ? $this->formatLivraisonData($livraisonActuelle) : null,
            'livraisons_en_attente' => $livraisonsEnAttente->map(function($livraison) {
                return $this->formatLivraisonData($livraison);
            }),
            'statistiques' => $this->getStatistiquesJour($livreurId)
        ];
        
        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }

    public function demarrerLivraison(Request $request, $commandeId)
    {
        try {
            $commande = Commnande::findOrFail($commandeId);

            if (Auth::user()->role !== 'livreur') {
                return response()->json([
                    'success' => false,
                    'message' => 'Seul un livreur peut démarrer une livraison.'
                ], 403);
            }

            if (is_null($commande->lat_arrivee) || is_null($commande->lng_arrivee)) {
                Log::warning("Missing destination coordinates for commande ID: {$commandeId}", [
                    'lat_arrivee' => $commande->lat_arrivee,
                    'lng_arrivee' => $commande->lng_arrivee
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Les coordonnées de destination sont manquantes.'
                ], 400);
            }

            $latitude = $request->input('latitude');
            $longitude = $request->input('longitude');

            if (is_null($latitude) || is_null($longitude)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Les coordonnées actuelles du livreur sont requises.'
                ], 400);
            }

            if ($commande->status === 'en_attente' || $commande->status === 'acceptee') {
                $commande->update([
                    'status' => 'en_cours',
                    'driver_id' => Auth::id(),
                    'date_debut_livraison' => now()
                ]);

                $deliveryRoute = DeliveryRoute::create([
                    'commnande_id' => $commandeId,
                    'livreur_id' => Auth::id(),
                    'driver_id' => Auth::id(),
                    'start_point' => json_encode(['lat' => (float) $commande->lat_depart, 'lng' => (float) $commande->lng_depart]),
                    'end_point' => json_encode(['lat' => (float) $commande->lat_arrivee, 'lng' => (float) $commande->lng_arrivee]),
                    'current_position' => json_encode(['lat' => (float) $latitude, 'lng' => (float) $longitude]),
                    'distance_km' => 0,
                    'duration_minutes' => 0,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);

                Log::info("Livraison démarrée pour la commande ID: {$commandeId}", [
                    'livreur_id' => Auth::id(),
                    'start_point' => ['lat' => $commande->lat_depart, 'lng' => $commande->lng_depart],
                    'end_point' => ['lat' => $commande->lat_arrivee, 'lng' => $commande->lng_arrivee],
                    'current_position' => ['lat' => $latitude, 'lng' => $longitude]
                ]);
            } elseif ($commande->status === 'en_cours' && $commande->driver_id === Auth::id()) {
                $deliveryRoute = DeliveryRoute::where('commnande_id', $commandeId)->first();

                if (!$deliveryRoute) {
                    Log::warning("No DeliveryRoute found for commande ID: {$commandeId}");
                    return response()->json([
                        'success' => false,
                        'message' => 'Aucun itinéraire de livraison trouvé pour cette commande.'
                    ], 404);
                }

                $deliveryRoute->update([
                    'current_position' => json_encode(['lat' => (float) $latitude, 'lng' => (float) $longitude]),
                    'updated_at' => now()
                ]);

                Log::info("Position mise à jour pour la livraison en cours, commande ID: {$commandeId}", [
                    'livreur_id' => Auth::id(),
                    'current_position' => ['lat' => $latitude, 'lng' => $longitude]
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'La commande doit être au statut "en_attente", "acceptee" ou en cours par le même livreur.'
                ], 400);
            }

            return response()->json([
                'success' => true,
                'message' => 'Livraison démarrée ou position mise à jour avec succès.',
                'data' => [
                    'commande_id' => $commande->id,
                    'status' => $commande->status,
                    'current_position' => ['lat' => $latitude, 'lng' => $longitude]
                ]
            ]);
        } catch (\Exception $e) {
            Log::error("Erreur lors du démarrage ou de la mise à jour de la livraison pour la commande ID: {$commandeId}", [
                'error' => $e->getMessage(),
                'request_data' => $request->all()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du traitement de la livraison : ' . $e->getMessage()
            ], 500);
        }
    }

    public function updatePosition(Request $request, $commandeId)
    {
        $commande = Commnande::where('id', $commandeId)
            ->where('driver_id', Auth::id())
            ->where('status', 'en_cours')
            ->with('deliveryRoute')
            ->firstOrFail();

        $deliveryRoute = $commande->deliveryRoute;
        $currentPosition = $deliveryRoute->current_position;

        if (is_string($currentPosition)) {
            $currentPosition = json_decode($currentPosition, true);
        }

        if (!is_array($currentPosition) || !isset($currentPosition['lat']) || !isset($currentPosition['lng'])) {
            Log::warning("Format invalide pour current_position pour l'ID commande : {$commandeId}", ['current_position' => $currentPosition]);
            return response()->json(['success' => false, 'message' => 'Position invalide.'], 400);
        }

        $lat = $currentPosition['lat'];
        $lng = $currentPosition['lng'];

        $deliveryRoute->current_position = json_encode(['lat' => $lat, 'lng' => $lng]);
        $deliveryRoute->save();

        return response()->json(['success' => true, 'message' => 'Position mise à jour.']);
    }

    public function marquerLivree(Request $request, $commandeId)
    {
        $request->validate([
            'commentaire_livraison' => 'nullable|string|max:500',
            'photo_livraison' => 'sometimes|image|max:2048',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric'
        ]);

        $commande = Commnande::where('id', $commandeId)
            ->where('driver_id', Auth::id())
            ->where('status', 'en_cours')
            ->firstOrFail();

        if (!$request->has('latitude') || !$request->has('longitude')) {
            return response()->json([
                'success' => false,
                'message' => 'Position requise pour marquer la livraison'
            ], 400);
        }

        $photoPath = null;
        if ($request->hasFile('photo_livraison')) {
            $photoPath = $request->file('photo_livraison')->store('livraisons/' . date('Y/m'), 'public');
        }

        $commande->update([
            'status' => 'livree',
            'date_livraison' => now(),
            'commentaire_livraison' => $request->commentaire,
            'photo_livraison' => $photoPath,
            'lat_livraison' => $request->latitude,
            'lng_livraison' => $request->longitude
        ]);

        DeliveryRoute::where('commnande_id', $commandeId)
            ->update([
                'completed_at' => now(),
                'final_position' => json_encode(['lat' => $request->latitude, 'lng' => $request->longitude])
            ]);

        Log::info("Livraison terminée", [
            'commnande_id' => $commandeId,
            'driver_id' => Auth::id(),
            'completed_at' => now()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Livraison marquée comme livrée avec succès!',
            'commande' => $this->formatLivraisonData($commande)
        ]);
    }

    public function signalerProbleme(Request $request, $commandeId)
    {
        $validated = $request->validate([
            'type_probleme' => 'required|string|in:client_absent,adresse_incorrecte,colis_endommage,autre',
            'description' => 'required|string|max:500',
            'photo' => 'sometimes|image|max:2048'
        ]);

        DB::beginTransaction();
        try {
            $commande = Commnande::where('id', $commandeId)
                       ->where('driver_id', Auth::id())
                       ->whereIn('status', ['en_cours', 'acceptee'])
                       ->lockForUpdate()
                       ->firstOrFail();

            $photoPath = $request->hasFile('photo') 
                ? $request->file('photo')->store('problemes/'.date('Y/m'), 'public')
                : null;

            $problemeData = [
                'type' => $validated['type_probleme'],
                'description' => $validated['description'],
                'photo' => $photoPath,
                'date' => now()->toISOString(),
                'status' => 'en_attente',
                'ip' => $request->ip()
            ];

            $commande->forceFill([
                'probleme_signale' => $problemeData, 
                'status' => 'probleme_signale'
            ])->save();

            $savedData = $commande->fresh();
            if (empty($savedData->probleme_signale)) {
                throw new \Exception("Échec de persistance des données");
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'saved_data' => $savedData->probleme_signale,
                'alert' => [
                    'title' => 'Signalement enregistré',
                    'message' => 'Le problème a été signalé avec succès',
                    'type' => 'success'
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Échec signalement", [
                'error' => $e->getMessage(),
                'commande' => $commandeId,
                'data' => $request->all()
            ]);
            
            return response()->json([
                'success' => false,
                'technical' => config('app.debug') ? $e->getMessage() : null,
                'message' => "Échec de l'enregistrement"
            ], 500);
        }
    }

    public function annulerLivraison(Request $request, $commandeId)
    {
        $request->validate([
            'raison' => 'required|string|max:500'
        ]);

        $commande = Commnande::where('id', $commandeId)
            ->where('driver_id', Auth::id())
            ->whereIn('status', ['acceptee', 'en_cours'])
            ->firstOrFail();

        $commande->update([
            'driver_id' => null,
            'status' => 'payee', 
            'raison_annulation' => $request->raison,
            'date_annulation' => now()
        ]);

        DeliveryRoute::where('commnande_id', $commandeId)->delete();

        return response()->json([
            'success' => true,
            'message' => 'Livraison annulée. Elle est maintenant disponible pour d\'autres livreurs.'
        ]);
    }

    public function ouvrirNavigation($commandeId)
    {
        $commande = Commnande::where('id', $commandeId)
            ->where('driver_id', Auth::id())
            ->where('status', 'en_cours')
            ->with('deliveryRoute')
            ->firstOrFail();

        Log::info("Commande trouvée pour la navigation", ['commande_id' => $commandeId, 'commande' => $commande->toArray()]);

        $route = $commande->deliveryRoute;
        if (!$route) {
            return response()->json(['success' => false, 'message' => 'Aucune route associée.'], 404);
        }

        $startPoint = $route->start_point;
        $endPoint = $route->end_point;
        $currentPosition = $route->current_position;

        $startPoint = is_string($startPoint) ? json_decode($startPoint, true) : $startPoint;
        $endPoint = is_string($endPoint) ? json_decode($endPoint, true) : $endPoint;
        $currentPosition = is_string($currentPosition) ? json_decode($currentPosition, true) : $currentPosition;

        if (!$startPoint || !isset($startPoint['lat']) || !isset($startPoint['lng']) ||
            !$endPoint || !isset($endPoint['lat']) || !isset($endPoint['lng']) ||
            !$currentPosition || !isset($currentPosition['lat']) || !isset($currentPosition['lng'])) {
            Log::warning("Coordonnées invalides pour l'ID commande : {$commandeId}");
            return response()->json(['success' => false, 'message' => 'Coordonnées invalides.'], 400);
        }

        $routeData = [
            'start_point' => $startPoint,
            'end_point' => $endPoint,
            'current_position' => $currentPosition,
            'polyline' => $route->polyline,
            'steps' => $route->steps,
            'distance_km' => $route->distance_km,
            'duration_minutes' => $route->duration_minutes,
            'start_address' => $commande->adresse_depart,
            'end_address' => $commande->adresse_arrivee
        ];

        return response()->json(['success' => true, 'route_data' => $routeData]);
    }

    private function calculateRoute(DeliveryRoute $route)
    {
        try {
            $url = "https://api.openrouteservice.org/v2/directions/driving-car/geojson";
            $start = [$route->start_point['lng'], $route->start_point['lat']];
            $end = [$route->end_point['lng'], $route->end_point['lat']];

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . env('OPENROUTESERVICE_API_KEY'),
                'Content-Type' => 'application/json'
            ])->post($url, [
                'coordinates' => [$start, $end],
                'instructions' => true
            ]);

            if ($response->successful()) {
                $data = $response->json();
                if (isset($data['features'][0])) {
                    $feature = $data['features'][0];
                    $route->update([
                        'polyline' => $feature['geometry'],
                        'steps' => array_map(function ($step) {
                            return [
                                'instruction' => $step['instruction'] ?? 'Pas d\'instruction',
                                'distance' => $step['distance'] ?? 0,
                                'duration' => $step['duration'] ?? 0
                            ];
                        }, $feature['properties']['segments'][0]['steps'] ?? []),
                        'distance_km' => round($feature['properties']['summary']['distance'] / 1000, 2),
                        'duration_minutes' => round($feature['properties']['summary']['duration'] / 60)
                    ]);
                    Log::info("Route calculated successfully for commande ID: {$route->commnande_id}", [
                        'polyline' => $feature['geometry'],
                        'steps_count' => count($feature['properties']['segments'][0]['steps'] ?? [])
                    ]);
                    return true;
                } else {
                    Log::warning("No features in OpenRouteService response for commande ID: {$route->commnande_id}", [
                        'response' => $data
                    ]);
                    return false;
                }
            } else {
                Log::error("OpenRouteService request failed for commande ID: {$route->commnande_id}", [
                    'status' => $response->status(),
                    'response' => $response->json()
                ]);
                return false;
            }
        } catch (\Exception $e) {
            Log::error("OpenRouteService error for commande ID: {$route->commnande_id}", [
                'error' => $e->getMessage(),
                'start' => $start,
                'end' => $end
            ]);
            return false;
        }
    }

    protected function calculateDistance($lat1, $lng1, $lat2, $lng2)
    {
        $earthRadius = 6371;

        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);

        $a = sin($dLat/2) * sin($dLat/2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLng/2) * sin($dLng/2);

        $c = 2 * atan2(sqrt($a), sqrt(1-$a));
        $distance = $earthRadius * $c;

        return round($distance, 2);
    }

    private function formatLivraisonData($commande)
    {
        $route = $commande->deliveryRoute;
        
        return [
            'id' => $commande->id,
            'reference' => $commande->reference,
            'status' => $commande->status,
            'type_colis' => $commande->type_colis,
            'type_livraison' => $commande->type_livraison,
            'prix_final' => $commande->prix_final,
            'adresse_depart' => $commande->adresse_depart,
            'adresse_arrivee' => $commande->adresse_arrivee,
            'details_adresse_depart' => $commande->details_adresse_depart,
            'details_adresse_arrivee' => $commande->details_adresse_arrivee,
            'date_acceptation' => $commande->date_acceptation,
            'date_debut_livraison' => $commande->date_debut_livraison,
            'client' => [
                'name' => $commande->user->name ?? 'Client',
                'phone' => $commande->user->phone ?? null
            ],
            'route' => $route ? [
                'distance_km' => $route->distance_km,
                'duration_minutes' => $route->duration_minutes,
                'current_position' => $route->current_position,
                'progress_percentage' => $this->calculateProgress($route),
                'polyline' => $route->polyline,
                'steps' => $route->steps
            ] : null
        ];
    }

    private function calculateProgress($route)
    {
        if (!$route || !$route->current_position || !$route->start_point || !$route->end_point) {
            return 0;
        }

        $startPoint = is_string($route->start_point) ? json_decode($route->start_point, true) : $route->start_point;
        $endPoint = is_string($route->end_point) ? json_decode($route->end_point, true) : $route->end_point;
        $currentPosition = is_string($route->current_position) ? json_decode($route->current_position, true) : $route->current_position;

        if (!is_array($startPoint) || !isset($startPoint['lat']) || !isset($startPoint['lng']) ||
            !is_array($endPoint) || !isset($endPoint['lat']) || !isset($endPoint['lng']) ||
            !is_array($currentPosition) || !isset($currentPosition['lat']) || !isset($currentPosition['lng'])) {
            Log::warning('Invalid coordinate data in calculateProgress', [
                'start_point' => $startPoint,
                'end_point' => $endPoint,
                'current_position' => $currentPosition
            ]);
            return 0;
        }

        $totalDistance = $this->calculateDistance(
            $startPoint['lat'],
            $startPoint['lng'],
            $endPoint['lat'],
            $endPoint['lng']
        );

        $distanceRestante = $this->calculateDistance(
            $currentPosition['lat'],
            $currentPosition['lng'],
            $endPoint['lat'],
            $endPoint['lng']
        );

        if ($totalDistance == 0) return 100;

        $progress = (($totalDistance - $distanceRestante) / $totalDistance) * 100;
        return max(0, min(100, round($progress)));
    }

    private function getStatistiquesJour($livreurId)
    {
        return [
            'livraisons_jour' => Commnande::where('driver_id', $livreurId)
                ->whereDate('date_livraison', today())
                ->count(),
            'revenus_jour' => Commnande::where('driver_id', $livreurId)
                ->where('status', 'livree')
                ->whereDate('date_livraison', today())
                ->sum('prix_final'),
            'en_cours' => Commnande::where('driver_id', $livreurId)
                ->where('status', 'en_cours')
                ->count(),
            'en_attente' => Commnande::where('driver_id', $livreurId)
                ->where('status', 'acceptee')
                ->count()
        ];
    }

    public function getDeliveryStatus($commandeId)
    {
        $commande = Commnande::where('id', $commandeId)
            ->where('driver_id', Auth::id())
            ->where('status', 'en_cours')
            ->with('deliveryRoute')
            ->firstOrFail();

        Log::info("Commande trouvée pour le statut", ['commande_id' => $commandeId, 'commande' => $commande->toArray()]);

        $deliveryRoute = $commande->deliveryRoute;
        if (!$deliveryRoute) {
            return response()->json(['success' => false, 'message' => 'Aucune route associée.'], 404);
        }

        $currentPosition = $deliveryRoute->current_position;
        $startPoint = $deliveryRoute->start_point;
        $endPoint = $deliveryRoute->end_point;

        Log::info("Raw data before decoding", [
            'current_position' => $currentPosition,
            'start_point' => $startPoint,
            'end_point' => $endPoint
        ]);

        $currentPosition = is_string($currentPosition) ? json_decode($currentPosition, true) : $currentPosition;
        $startPoint = is_string($startPoint) ? json_decode($startPoint, true) : $startPoint;
        $endPoint = is_string($endPoint) ? json_decode($endPoint, true) : $endPoint;

        Log::info("Decoded data", [
            'current_position' => $currentPosition,
            'start_point' => $startPoint,
            'end_point' => $endPoint
        ]);

        if (!is_array($currentPosition) || !isset($currentPosition['lat']) || !isset($currentPosition['lng']) ||
            !is_array($startPoint) || !isset($startPoint['lat']) || !isset($startPoint['lng']) ||
            !is_array($endPoint) || !isset($endPoint['lat']) || !isset($endPoint['lng'])) {
            Log::warning("Coordonnées invalides pour l'ID commande : {$commandeId}", [
                'current_position' => $currentPosition,
                'start_point' => $startPoint,
                'end_point' => $endPoint
            ]);
            return response()->json(['success' => false, 'message' => 'Coordonnées invalides.'], 400);
        }

        $statusData = [
            'current_position' => $currentPosition,
            'start_point' => $startPoint,
            'end_point' => $endPoint,
            'distance_km' => $deliveryRoute->distance_km,
            'duration_minutes' => $deliveryRoute->duration_minutes,
        ];

        Log::info("Status data prepared", ['status_data' => $statusData]);

        return response()->json(['success' => true, 'data' => $statusData]);
    }
}