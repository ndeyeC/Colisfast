<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Notification;
use Illuminate\Http\Request;
use App\Models\Commnande;
use App\Models\Zone;
use App\Models\User;
use App\Models\Tarif;
use App\Services\CinetPayService; 
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use App\Notifications\SupportColisfast;
use App\Services\GeocodingService;

class CommnandeController extends Controller
{
    const SUPPLEMENT_STANDARD = 50;  // FCFA
    const SUPPLEMENT_EXPRESS = 1000;  // FCFA
    
    protected $cinetPayService; 
    public function __construct(CinetPayService $cinetPayService, GeocodingService $geocodingService) 
    {
        $this->cinetPayService = $cinetPayService; 
        $this->geocodingService = $geocodingService;
    }
    

    public function create()
    {
        // Récupérer tous les tarifs
        $tarifs = Tarif::all();
        
        // Récupérer toutes les zones
        $zones = Zone::all();
        
        $supplements = [
            'standard' => self::SUPPLEMENT_STANDARD,
            'express' => self::SUPPLEMENT_EXPRESS
        ];
        
        return view('commnandes.create', compact('zones', 'tarifs', 'supplements'));
    }

    // public function store(Request $request)
    // {
    //     $validated = $request->validate([
    //         'adresse_depart' => 'required|string|max:255',
    //         'adresse_arrivee' => 'required|string|max:255',
    //         'details_adresse_depart' => 'nullable|string|max:255', 
    //         'details_adresse_arrivee' => 'nullable|string|max:255',
    //         'type_colis' => 'required|string',
    //         'type_livraison' => 'required|in:standard,express',
    //         'prix' => 'sometimes|numeric',
    //         'region_depart' => 'sometimes|string',
    //          'numero_telephone' => 'required|string|max:20',
    //         'region_arrivee' => 'sometimes|string',
    //         'type_zone' => 'sometimes|string',
    //         'mode_paiement' => 'required|string|in:wave,orange money,tokens',
    //         // Ajout des champs client pour CinetPay
    //         'customer_name' => 'sometimes|string|max:100',
    //         'customer_surname' => 'sometimes|string|max:100',
    //         'customer_email' => 'sometimes|email|max:100',
    //         'customer_phone' => 'sometimes|string|max:20'
    //     ]);

    //     if ($request->filled('region_depart') && $request->filled('region_arrivee')) {
    //         $regionDepart = $request->region_depart;
    //         $regionArrivee = $request->region_arrivee;
    //     } else {

    //         $regionDepart = $this->extraireRegion($validated['adresse_depart']);
    //         $regionArrivee = $this->extraireRegion($validated['adresse_arrivee']);
    //     }

    //     if (!$regionDepart || !$regionArrivee) {
    //         return redirect()->back()
    //             ->with('error', 'Impossible de déterminer les régions à partir des adresses fournies.')
    //             ->withInput();
    //     }

    //     if ($request->filled('prix')) {
    //         $prixBase = $request->prix;
    //     } else {
    //         $tarif = $this->findTarifSansLivraison($regionDepart, $regionArrivee, $validated['type_colis']);

    //         if (!$tarif) {
    //             return redirect()->back()
    //                 ->with('error', 'Aucun tarif disponible pour cette combinaison.')
    //                 ->withInput();
    //         }
            
    //         $prixBase = $tarif->prix;
    //     }

    //     $prixFinal = $this->calculerPrixAvecSupplement($prixBase, $validated['type_livraison']);


       
    //     $reference = 'CMD-' . strtoupper(Str::random(8));
        
    //     // Créer la commande en attente de paiement
    //     $commande = new Commnande();
    //     $commande->reference = $reference;
    //     $commande->adresse_depart = $validated['adresse_depart'];
    //     $commande->adresse_arrivee = $validated['adresse_arrivee'];
    //     $commande->details_adresse_depart = $validated['details_adresse_depart']; 
    //     $commande->details_adresse_arrivee = $validated['details_adresse_arrivee'];
    //     $commande->region_depart = $regionDepart;
    //     $commande->region_arrivee = $regionArrivee;
    //     $commande->type_zone = $request->type_zone ?? null;
    //     $commande->type_colis = $validated['type_colis'];
    //     $commande->type_livraison = $validated['type_livraison'];
    //     $commande->prix_base = $prixBase;
    //      $commande->numero_telephone = $validated['numero_telephone']; // Stocker le téléphone
    //     $commande->prix_final = $prixFinal;
    //     $commande->status = 'en_attente_paiement';
    //     $commande->mode_paiement = $validated['mode_paiement'];
    //     $commande->user_id = Auth::id();
    //     $commande->save();

       
    //     // Rediriger vers CinetPay
    //     return $this->redirectToCinetPay($commande, $validated);
    // }


   public function store(Request $request)
{
    $validated = $request->validate([
        'adresse_depart' => 'required|string|max:255',
        'adresse_arrivee' => 'required|string|max:255',
        'details_adresse_depart' => 'nullable|string|max:255',
        'details_adresse_arrivee' => 'nullable|string|max:255',
        'type_colis' => 'required|string|in:0-5 kg,5-20 kg,20-50 kg,50+ kg',
        'type_livraison' => 'required|in:standard,express',
        'prix' => 'sometimes|numeric|min:0',
        'region_depart' => 'sometimes|string|max:255',
        'region_arrivee' => 'sometimes|string|max:255',
        'type_zone' => 'sometimes|string|max:255',
        'numero_telephone' => 'required|string|max:20',
        'mode_paiement' => 'required|string|in:wave,orange,tokens',
        'customer_name' => 'sometimes|string|max:100',
        'customer_surname' => 'sometimes|string|max:100',
        'customer_email' => 'sometimes|email|max:100',
        'customer_phone' => 'sometimes|string|max:20'
    ]);

    DB::beginTransaction();
    try {
        // Extraire les régions si non fournies
        $regionDepart = $request->filled('region_depart') ? $request->region_depart : $this->extraireRegion($validated['adresse_depart']);
        $regionArrivee = $request->filled('region_arrivee') ? $request->region_arrivee : $this->extraireRegion($validated['adresse_arrivee']);

        if (!$regionDepart || !$regionArrivee) {
            throw new \Exception('Impossible de déterminer les régions à partir des adresses fournies.');
        }

        // Calculer le prix de base
        $prixBase = $request->filled('prix') ? $request->prix : ($this->findTarifSansLivraison($regionDepart, $regionArrivee, $validated['type_colis'])->prix ?? null);
        if (!$prixBase) {
            throw new \Exception('Aucun tarif disponible pour cette combinaison.');
        }

        $prixFinal = $this->calculerPrixAvecSupplement($prixBase, $validated['type_livraison']);

        // Géocoder les adresses
        $departCoordinates = $this->geocodingService->geocode($validated['adresse_depart']);
        if (!$departCoordinates) {
            throw new \Exception("Impossible de géocoder l'adresse de départ : {$validated['adresse_depart']}");
        }

        $arriveeCoordinates = $this->geocodingService->geocode($validated['adresse_arrivee']);
        if (!$arriveeCoordinates) {
            throw new \Exception("Impossible de géocoder l'adresse de destination : {$validated['adresse_arrivee']}");
        }

        // Créer la commande
        $reference = 'CMD-' . strtoupper(Str::random(8));
        $commande = Commnande::create([
            'reference' => $reference,
            'adresse_depart' => $validated['adresse_depart'],
            'details_adresse_depart' => $validated['details_adresse_depart'],
            'adresse_arrivee' => $validated['adresse_arrivee'],
            'details_adresse_arrivee' => $validated['details_adresse_arrivee'],
            'region_depart' => $regionDepart,
            'region_arrivee' => $regionArrivee,
            'type_zone' => $request->type_zone ?? null,
            'type_colis' => $validated['type_colis'],
            'type_livraison' => $validated['type_livraison'],
            'prix_base' => $prixBase,
            'prix_final' => $prixFinal,
            'numero_telephone' => $validated['numero_telephone'],
            'status' => 'en_attente_paiement',
            'mode_paiement' => $validated['mode_paiement'],
            'user_id' => Auth::id(),
            'lat_depart' => $departCoordinates['lat'],
            'lng_depart' => $departCoordinates['lon'],
            'lat_arrivee' => $arriveeCoordinates['lat'],
            'lng_arrivee' => $arriveeCoordinates['lon'],
        ]);

        // Log de l'activité
        Log::info('Commande créée avec succès', [
            'commnande_id' => $commande->id,
            'user_id' => Auth::id(),
            'reference' => $reference,
            'adresse_depart' => $validated['adresse_depart'],
            'adresse_arrivee' => $validated['adresse_arrivee'],
            'lat_depart' => $departCoordinates['lat'],
            'lng_depart' => $departCoordinates['lon'],
            'lat_arrivee' => $arriveeCoordinates['lat'],
            'lng_arrivee' => $arriveeCoordinates['lon'],
        ]);

        DB::commit();

        // Rediriger vers CinetPay pour le paiement
        return $this->redirectToCinetPay($commande, $validated);
    } catch (\Exception $e) {
        DB::rollBack();
        Log::error('Erreur lors de la création de la commande', [
            'error' => $e->getMessage(),
            'request_data' => $request->all(),
        ]);
        return redirect()->back()
            ->with('error', 'Erreur lors de la création de la commande : ' . $e->getMessage())
            ->withInput();
    }
}

    

    /**
     * Rediriger vers CinetPay pour le paiement
     */
//  private function redirectToCinetPay(Commnande $commande, array $customerData = [])
// {
//     try {
//         // Vérifier la validité de la commande
//         if (!$commande || !$commande->id) {
//             return back()->with('error', 'Commande invalide');
//         }

//         // 🔁 Utilise ngrok en local, sinon l’URL réelle du site
//         $baseUrl = app()->environment('local')
//             ? 'https://b49175698ab2.ngrok-free.app' 
//             : url('/');

        
//         $urls = [
//             'success' => $baseUrl . '/commnandes/payment/success',
//             'ipn'     => $baseUrl . '/commnandes/payment/ipn',
//             'cancel'  => $baseUrl . '/commnandes/payment/cancel',
//         ];

//         // Log pour vérification
//         foreach ($urls as $key => $url) {
//             if (!filter_var($url, FILTER_VALIDATE_URL)) {
//                 Log::error("Invalid URL format for $key: $url");
//                 return back()->with('error', "Configuration incorrecte: URL de $key invalide");
//             }
//         }

//         // Préparer les données à envoyer à CinetPay
//         $paymentData = [
//             'item_name'      => 'Livraison ' . $commande->reference,
//             'item_price'     => (float)$commande->prix_final,
//             'ref_command'    => $commande->reference,
//             'success_url'    => $urls['success'],
//             'ipn_url'        => $urls['ipn'],
//             'cancel_url'     => $urls['cancel'],
//             'custom_field'   => [
//                 'commande_id' => $commande->id,
//                 'user_id'     => $commande->user_id
//             ],
//             'customer_name'    => $customerData['customer_name'] ?? Auth::user()->name ?? '',
//             'customer_surname' => $customerData['customer_surname'] ?? '',
//             'customer_email'   => $customerData['customer_email'] ?? Auth::user()->email ?? '',
//             'customer_phone'   => $customerData['customer_phone'] ?? '',
//             'customer_address' => $commande->adresse_depart ?? '',
//             'customer_city'    => $commande->region_depart ?? '',
//             'customer_country' => 'SN',
//             'customer_state'   => '',
//             'customer_zip'     => ''
//         ];

//         Log::info('CinetPay payment request data:', $paymentData);

//         // Vérification des clés de config
//         if (
//             empty(config('cinetpay.api_key')) ||
//             empty(config('cinetpay.site_id')) ||
//             empty(config('cinetpay.secret_key'))
//         ) {
//             Log::error('Configuration CinetPay manquante');
//             return back()->with('error', 'Erreur de configuration du système de paiement');
//         }

//         // Envoie vers CinetPay via le service
//         $response = $this->cinetPayService->createPaymentRequest($paymentData);

//         if (!$response['success']) {
//             Log::error('Erreur CinetPay:', $response);

//             $errorMessage = $response['message'] ?? 'Erreur de paiement inconnue';

//             if (isset($response['status_code']) && $response['status_code'] === 422) {
//                 $errors = $response['errors'] ?? [];
//                 $errorList = is_array($errors) ? implode(', ', $errors) : (string)$errors;
//                 return back()->with('error', "Erreur de validation: $errorList");
//             }

//             return back()->with('error', $errorMessage);
//         }

//         if (empty($response['data']['token']) || empty($response['data']['redirect_url'])) {
//             Log::error('Réponse CinetPay incomplète:', $response);
//             return back()->with('error', 'Réponse incomplète du système de paiement');
//         }

//         // Sauvegarde du token
//         $commande->payment_token = $response['data']['token'];
//         $commande->save();

//         // 🔁 Redirection vers la page de paiement CinetPay
//         return redirect()->away($response['data']['redirect_url']);

//     } catch (\Exception $e) {
//         Log::error('Exception CinetPay:', [
//             'message' => $e->getMessage(),
//             'file' => $e->getFile(),
//             'line' => $e->getLine()
//         ]);

//         return back()->with('error', 'Erreur système: ' . $e->getMessage());
//     }
// }

private function redirectToCinetPay(Commnande $commande, array $validated)
    {
        try {
            // Construire les URLs avec config('app.url') et route()
            $successUrl = rtrim(config('app.url'), '/') . route('commnandes.payment.success', [], false);
            $ipnUrl = rtrim(config('app.url'), '/') . route('commnandes.payment.ipn', [], false);
            $cancelUrl = rtrim(config('app.url'), '/') . route('commnandes.payment.cancel', [], false);

            // Valider les URLs
            if (!filter_var($successUrl, FILTER_VALIDATE_URL) || 
                !filter_var($ipnUrl, FILTER_VALIDATE_URL) || 
                !filter_var($cancelUrl, FILTER_VALIDATE_URL)) {
                throw new \Exception('Invalid URL format for CinetPay: success=' . $successUrl . ', ipn=' . $ipnUrl . ', cancel=' . $cancelUrl);
            }

            // Données pour la requête CinetPay
            $paymentData = [
                'item_name' => "Livraison {$commande->reference}",
                'item_price' => $commande->prix_final,
                'ref_command' => $commande->reference,
                'success_url' => $successUrl,
                'ipn_url' => $ipnUrl,
                'cancel_url' => $cancelUrl,
                'custom_field' => [
                    'commande_id' => $commande->id,
                    'user_id' => Auth::id()
                ],
                'customer_name' => $validated['customer_name'] ?? '',
                'customer_surname' => $validated['customer_surname'] ?? '',
                'customer_email' => $validated['customer_email'] ?? '',
                'customer_phone' => $validated['customer_phone'] ?? '',
                'customer_address' => $validated['adresse_depart'],
                'customer_city' => $validated['region_depart'] ?? '',
                'customer_country' => 'SN',
                'customer_state' => '',
                'customer_zip' => ''
            ];

            Log::info('CinetPay payment request data', $paymentData);

            // Appeler le service CinetPay
            $response = $this->cinetPayService->createPaymentRequest($paymentData);

            if (!$response['success']) {
                throw new \Exception($response['message']);
            }

            Log::info('CinetPay payment initiated', [
                'commande_id' => $commande->id,
                'transaction_id' => $response['data']['transaction_id'],
                'payment_url' => $response['data']['redirect_url']
            ]);

            return redirect($response['data']['redirect_url']);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la redirection vers CinetPay', [
                'error' => $e->getMessage(),
                'commande_id' => $commande->id,
                'payment_data' => $paymentData ?? []
            ]);
            return redirect()->back()
                ->with('error', 'Erreur lors de la redirection vers le paiement : ' . $e->getMessage())
                ->withInput();
        }
    }



    /**
     * Callback IPN (Instant Payment Notification) de CinetPay
     */
   
public function ipnCallback(Request $request)
{
    Log::info('IPN CinetPay reçu', $request->all());
    
    if (!app()->environment('local', 'testing')) {
        if (!$this->cinetPayService->verifySignature($request->all())) {
            Log::error('Signature IPN CinetPay invalide');
            return response()->json(['status' => 'error', 'message' => 'Signature invalide'], 400);
        }
    }

    $transactionId = $request->input('cpm_trans_id');
    
    if (!$transactionId) {
        return response()->json(['status' => 'error', 'message' => 'Transaction ID manquant'], 400);
    }
    
    $statusResponse = $this->cinetPayService->checkPaymentStatus($transactionId);
    
    if (!$statusResponse['success']) {
        Log::error('Erreur vérification statut CinetPay', $statusResponse);
        return response()->json(['status' => 'error', 'message' => 'Erreur vérification statut'], 500);
    }
    
    $paymentStatus = $statusResponse['data']['payment_status'] ?? null;
    
    $metadata = $request->input('cpm_custom') ?? $statusResponse['data']['custom'] ?? '{}';
    
    $customData = json_decode($metadata, true);
    $commandeId = $customData['commande_id'] ?? null;
    
    if (!$commandeId) {
        return response()->json(['status' => 'error', 'message' => 'ID commande manquant'], 400);
    }
    
    $commande = Commnande::find($commandeId);
    
    if (!$commande) {
        return response()->json(['status' => 'error', 'message' => 'Commande non trouvée'], 404);
    }
    
    if ($paymentStatus === 'completed') {
        $commande->status = 'payee';
        $commande->date_paiement = now();
        $commande->save();
        
        Log::info('Paiement complété pour la commande ' . $commande->reference);
    }
    
    return response()->json(['status' => 'success', 'message' => 'IPN traité']);
}
    
    /**
     * Page de confirmation après paiement réussi
     */
    public function paymentSuccess(Request $request)
{
    $transactionId = $request->query('transaction_id') ?? $request->query('token');
    
    if (!$transactionId) {
        return redirect()->route('commnandes.index')
            ->with('error', 'Information de paiement manquante');
    }
    
    $statusResponse = $this->cinetPayService->checkPaymentStatus($transactionId);
    
    if (!$statusResponse['success']) {
        return redirect()->route('commnandes.index')
            ->with('error', 'Erreur lors de la vérification du paiement');
    }
    
    $paymentStatus = $statusResponse['data']['payment_status'] ?? null;
    
    $customData = json_decode($statusResponse['data']['custom'] ?? '{}', true);
    $commandeId = $customData['commande_id'] ?? null;
    
    if (!$commandeId) {
        return redirect()->route('commnandes.index')
            ->with('error', 'Commande non trouvée');
    }
    
    $commande = Commnande::find($commandeId);
    
    if (!$commande) {
        return redirect()->route('commnandes.index')
            ->with('error', 'Commande non trouvée');
    }
    
    if ($paymentStatus === 'completed') {
        return redirect()->route('commnandes.confirmation', $commande->id)
            ->with('success', 'Paiement effectué avec succès!');
    }
    
    return redirect()->route('commnandes.index')
        ->with('warning', 'Le statut de votre paiement est en attente de confirmation');
}


    public function paymentCancel(Request $request)
    {
        $transactionId = $request->query('transaction_id') ?? $request->query('token');
        
        if ($transactionId) {
            $statusResponse = $this->cinetPayService->checkPaymentStatus($transactionId);
            if ($statusResponse['success']) {
                $customData = json_decode($statusResponse['data']['custom'] ?? '{}', true);
                $commandeId = $customData['commande_id'] ?? null;
                
                if ($commandeId) {
                    Log::info("Paiement annulé pour la commande $commandeId");
                }
            }
        }
        
        return redirect()->route('commnandes.index')
            ->with('error', 'Le paiement a été annulé');
    }

    /**
     * Diagnostic CinetPay (pour le débogage)
     */
    public function diagnosticCinetPay()
    {
        if (app()->environment('production')) {
            abort(403, 'Non autorisé en production');
        }

        $config = [
            'site_id_present' => !empty(config('cinetpay.site_id')),
            'api_key_present' => !empty(config('cinetpay.api_key')),
            'secret_key_present' => !empty(config('cinetpay.secret_key')),
            'base_url' => config('cinetpay.base_url'),
            'currency' => config('cinetpay.currency'),
            'env' => config('cinetpay.env'),
            'app_env' => app()->environment(),
            'app_url' => config('app.url'),
        ];

        $testData = [
            'item_name' => 'Diagnostic CinetPay',
            'item_price' => '10.00',
            'ref_command' => 'DIAG-' . uniqid(),
            'command_name' => 'Test Diagnostic',
            'success_url' => url('/diagnostic-success'),
            'ipn_url' => url('/diagnostic-ipn'),
            'cancel_url' => url('/diagnostic-cancel'),
            'custom_field' => [
                'test' => true,
                'timestamp' => time()
            ],
            'customer_name' => 'Test',
            'customer_surname' => 'User',
            'customer_email' => 'test@example.com',
            'customer_phone' => '770000000'
        ];

        $results = [];

        $results['config_check'] = $this->checkCinetPayConfig();

        $results['connectivity'] = $this->testConnectivity(config('cinetpay.base_url'));

        $results['json_test'] = $this->testCinetPayRequest($testData, 'json');

        return view('diagnostics.cinetpay', [
            'config' => $config,
            'results' => $results,
            'testData' => $testData,
        ]);
    }

    /**
     * Vérifie la configuration CinetPay
     */
    private function checkCinetPayConfig()
    {
        $issues = [];
        
        if (empty(config('cinetpay.site_id'))) {
            $issues[] = "Site ID manquant";
        }
        
        if (empty(config('cinetpay.api_key'))) {
            $issues[] = "Clé API manquante";
        }
        
        if (empty(config('cinetpay.secret_key'))) {
            $issues[] = "Secret API manquant";
        }
        
        if (empty(config('cinetpay.base_url'))) {
            $issues[] = "URL de base non configurée";
        }
        
        if (!empty(config('cinetpay.base_url'))) {
            try {
                $result = Http::timeout(5)->get(config('cinetpay.base_url'));
                if ($result->failed()) {
                    $issues[] = "L'URL de base ne répond pas: " . $result->status();
                }
            } catch (\Exception $e) {
                $issues[] = "Impossible de se connecter à l'URL de base: " . $e->getMessage();
            }
        }
        
        return [
            'success' => empty($issues),
            'issues' => $issues
        ];
    }

    /**
     * Teste une requête CinetPay
     */
    private function testCinetPayRequest($data, $method = 'json')
    {
        try {
            $start = microtime(true);
            
            $result = $this->makeJsonCinetPayRequest($data);
            
            $time = round((microtime(true) - $start) * 1000);
            
            return array_merge($result, [
                'time_ms' => $time
            ]);
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Exception: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Crée une requête JSON CinetPay
     */
    private function makeJsonCinetPayRequest($data)
    {
        $endpoint = config('cinetpay.base_url') . '/payment/request-payment';
        
        $payload = [
            'amount' => $data['item_price'],
            'currency' => config('cinetpay.currency', 'XOF'),
            'site_id' => config('cinetpay.site_id'),
            'transaction_id' => $data['ref_command'],
            'description' => $data['command_name'],
            'return_url' => $data['success_url'],
            'notify_url' => $data['ipn_url'],
            'customer_name' => $data['customer_name'],
            'customer_surname' => $data['customer_surname'],
            'customer_email' => $data['customer_email'],
            'customer_phone' => $data['customer_phone'],
            'metadata' => json_encode($data['custom_field'])
        ];
        
        $response = Http::withOptions([
            'verify' => app()->environment('local') ? false : true,
            'timeout' => 10,
        ])->withHeaders([
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer '.config('cinetpay.api_key')
        ])->post($endpoint, $payload);
        
        return [
            'success' => $response->successful(),
            'status' => $response->status(),
            'response' => $response->json() ?: ['raw' => $response->body()]
        ];
    }

    /**
     * Teste la connectivité à une URL
     */
    private function testConnectivity($url)
    {
        try {
            $start = microtime(true);
            $response = Http::timeout(5)->get($url);
            $time = round((microtime(true) - $start) * 1000); 
            
            return [
                'success' => $response->successful(),
                'status' => $response->status(),
                'time_ms' => $time,
                'message' => $response->successful() ? 'Connexion réussie' : 'Échec de connexion'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage()
            ];
        }
    }

    private function calculerPrixAvecSupplement($prixBase, $typeLivraison)
    {
        if ($typeLivraison === 'express') {
            return $prixBase + self::SUPPLEMENT_EXPRESS;
        } else {
            return $prixBase + self::SUPPLEMENT_STANDARD;
        }
    }

    /**
     * Trouve un tarif sans tenir compte du type de livraison.
     */
    private function findTarifSansLivraison($regionDepart, $regionArrivee, $typeColis)
    {
        $zone = Zone::where('region_depart', $regionDepart)
                   ->where('region_arrivee', $regionArrivee)
                   ->first();
        
        if (!$zone) {
            return null;
        }
        
        $tarif = Tarif::where(function($query) use ($zone, $regionDepart, $regionArrivee) {
                if (!empty($zone->type_zone)) {
                    $query->where('type_zone', $zone->type_zone)
                          ->orWhere('zone', $zone->type_zone);
                }
                $query->orWhere('zone', $regionDepart)
                      ->orWhere('zone', $regionArrivee);
            })
            ->where('tranche_poids', $typeColis)
            ->first();
            
        return $tarif;
    }

    /**
     * Extrait la région à partir d'une adresse.
     */
    private function normaliserTexte($texte)
    {
        $texte = strtolower($texte);
        $texte = iconv('UTF-8', 'ASCII//TRANSLIT', $texte); 
        $texte = preg_replace('/[^a-z0-9\s]/', '', $texte); 
        return trim($texte);
    }
    
    private function extraireRegion($adresse)
{
    
    $regions = Zone::select('region_depart')
        ->union(Zone::select('region_arrivee'))
        ->pluck('region_depart')
        ->unique()
        ->toArray();

    $adresseNorm = $this->normaliserTexte($adresse);
    $meilleureCorrespondance = null;
    $scoreMax = 0;

    foreach ($regions as $region) {
        $regionNorm = $this->normaliserTexte($region);
        similar_text($adresseNorm, $regionNorm, $pourcentage);

        if ($pourcentage > $scoreMax) {
            $scoreMax = $pourcentage;
            $meilleureCorrespondance = $region;
        }
    }

    if ($meilleureCorrespondance === null || $scoreMax < 70) {
        $distanceMin = PHP_INT_MAX;

        foreach ($regions as $region) {
            $regionNorm = $this->normaliserTexte($region);
            $distance = levenshtein($adresseNorm, $regionNorm);

            // Si la distance est acceptable (maximum 3 erreurs)
            if ($distance < $distanceMin && $distance <= 3) {
                $distanceMin = $distance;
                $meilleureCorrespondance = $region;
            }
        }
    }

    
    if ($meilleureCorrespondance === null) {
        return null; 
    }

    
    return $meilleureCorrespondance;
}


    public function index()
    {
        $commnandes = Commnande::where('user_id', Auth::id())->latest()->get();
    
        return view('commnandes.index', compact('commnandes'));
    }
    
public function show(Commnande $commnande)
{
    if ($commnande->user_id !== auth()->id()) {
        abort(403);
    }

    return view('commnandes.show', compact('commnande'));
}

 public function confirmation($id)
    {
        $commande = Commnande::findOrFail($id);
        
        if ($commande->user_id !== Auth::id()) {
            abort(403, 'Non autorisé');
        }
        
        return view('commnandes.confirmation', compact('commande'));
    }

public function indexLivreur()
{
    $commandes = Commnande::where('driver_id', Auth::id())
                 ->whereIn('status', ['acceptée', 'en cours'])
                 ->latest()
                 ->get();

    return view('livreur.commandes.index', compact('commandes'));
}
    
}