@extends('layouts.template')

@section('title', 'Nouvelle Commande')


@section('content')

<div class="max-w-xl mx-auto bg-white p-6 rounded-lg shadow">

<div class="mb-4">
        <a href="{{ url()->previous() }}" class="text-gray-600 hover:text-gray-900 inline-flex items-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd" />
            </svg>
            Retour
        </a>
    </div>
    <h2 class="text-2xl font-bold mb-4">Créer une nouvelle commande</h2>

    {{-- Messages de session --}}
    @if(session('success'))
        <div class="bg-green-100 text-green-800 p-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="bg-red-100 text-red-800 p-3 rounded mb-4">
            {{ session('error') }}
        </div>
    @endif

    {{-- Erreurs de validation --}}
    @if ($errors->any())
        <div class="bg-red-100 text-red-800 p-3 rounded mb-4">
            <ul class="list-disc pl-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('commnandes.store') }}" method="POST">
        @csrf

        {{-- Champ départ avec suggestions --}}
<div class="mb-4 relative">
    <label class="block font-medium">Adresse de départ</label>
    <input type="text" name="adresse_depart" id="adresse_depart" class="w-full p-3 border rounded" value="{{ old('adresse_depart') }}" required>
    <ul id="suggestions_depart" class="absolute z-10 bg-white border rounded w-full mt-1"></ul>
</div>

{{-- Champ pour les détails de l'adresse de départ --}}
<div id="precisions_depart_box" class="mb-4" style="display: none;">
    <label class="block font-medium">Précisez l'adresse exacte à <span id="ville_depart_label"></span></label>
    <input type="text" name="details_adresse_depart" class="w-full p-3 border rounded" value="{{ old('details_adresse_depart') }}">
</div>

{{-- Champ arrivée avec suggestions --}}
<div class="mb-4 relative">
    <label class="block font-medium">Adresse de destination</label>
    <input type="text" name="adresse_arrivee" id="adresse_arrivee" class="w-full p-3 border rounded" value="{{ old('adresse_arrivee') }}" required>
    <ul id="suggestions_arrivee" class="absolute z-10 bg-white border rounded w-full mt-1"></ul>
</div>

{{-- Champ pour les détails de l'adresse d'arrivée --}}
<div id="precisions_arrivee_box" class="mb-4" style="display: none;">
    <label class="block font-medium">Précisez l'adresse exacte à <span id="ville_arrivee_label"></span></label>
    <input type="text" name="details_adresse_arrivee" class="w-full p-3 border rounded" value="{{ old('details_adresse_arrivee') }}">
</div>

        @if(session('adresse_error'))
    <div class="bg-yellow-100 text-yellow-800 p-3 rounded mb-4">
        {{ session('adresse_error') }}
    </div>
@endif


        <div class="mb-4">
            <label class="block font-medium">Type de colis</label>
            <select name="type_colis" class="w-full p-3 border rounded" required>
                <option value="">-- Sélectionner --</option>
                <option value="0-5 kg">0-5 kg</option>
                <option value="5-20 kg">5-20 kg</option>
                <option value="20-50 kg">20-50 kg</option>
                <option value="50+ kg">50+ kg</option>
            </select>
        </div>

        <div class="mb-4">
    <label class="block font-medium">Numéro de téléphone</label>
    <input type="tel" name="numero_telephone" class="w-full p-3 border rounded" 
           value="{{ old('numero_telephone') }}" 
           placeholder="Ex: 77 123 45 67" required>
</div>

        <div class="mb-4">
            <label class="block font-medium">Type de livraison</label>
            <select name="type_livraison" class="w-full p-3 border rounded" required>
                <option value="standard">Standard (+{{ $supplements['standard'] ?? 500 }} FCFA)</option>
                <option value="express">Express (+{{ $supplements['express'] ?? 1000 }} FCFA)</option>
            </select>
        </div>

        <div class="mb-4">
            <label class="block font-medium">Prix estimé</label>
            <input type="text" id="prix_affichage" class="w-full p-3 border rounded bg-gray-100" readonly>
            <!-- Champ caché pour envoyer le prix de base au serveur -->
            <input type="hidden" name="prix" id="prix_hidden">
            <!-- Champs cachés pour envoyer les informations de région -->
            <input type="hidden" name="region_depart" id="region_depart">
            <input type="hidden" name="region_arrivee" id="region_arrivee">
            <input type="hidden" name="type_zone" id="type_zone">
        </div>

          <!-- Section de paiement -->
        <!-- <div class="mb-6">
            <h3 class="font-medium mb-3 flex items-center">
                Méthode de paiement
            </h3>
            
            <div class="space-y-3">
                <label class="block border rounded-lg p-4 hover:border-green-500 cursor-pointer transition-colors">
                    <input type="radio" name="mode_paiement" value="wave" class="mr-2" required>
                    <span class="inline-flex items-center">
                        <i class="fab fa-wave-square text-purple-500 mr-2 text-xl"></i> 
                        <span>
                            <span class="font-medium">Wave</span>
                            <span class="text-xs block text-gray-500">Paiement mobile via Wave</span>
                        </span>
                    </span>
                </label>
                
                <label class="block border rounded-lg p-4 hover:border-green-500 cursor-pointer transition-colors">
                    <input type="radio" name="mode_paiement" value="orange" class="mr-2">
                    <span class="inline-flex items-center">
                        <i class="fas fa-mobile-alt text-orange-500 mr-2 text-xl"></i> 
                        <span>
                            <span class="font-medium">Orange Money</span>
                            <span class="text-xs block text-gray-500">Paiement mobile via Orange Money</span>
                        </span>
                    </span>
                </label>
                 -->
                <!-- <label class="block border rounded-lg p-4 hover:border-green-500 cursor-pointer transition-colors">
                    <input type="radio" name="payment" value="tokens" class="mr-2">
                    <span class="inline-flex items-center">
                        <i class="fas fa-coins text-yellow-500 mr-2 text-xl"></i> 
                        <span>
                            <span class="font-medium">Jetons</span>
                            <span class="text-xs block text-gray-500">Solde actuel: {{ Auth::user()->tokens ?? 0 }} jetons</span>
                        </span>
                    </span>
                </label> -->
            <!-- </div> -->
             <!-- Section de paiement -->
<div class="mb-6">
    <h3 class="font-medium mb-3 flex items-center">
        Méthode de paiement
    </h3>
    
    <div class="space-y-3">
        <label class="block border rounded-lg p-4 hover:border-green-500 cursor-pointer transition-colors">
            <input type="radio" name="mode_paiement" value="wave" class="mr-2" required>
            <span class="inline-flex items-center">
                <i class="fab fa-wave-square text-purple-500 mr-2 text-xl"></i> 
                <span>
                    <span class="font-medium">Wave</span>
                    <span class="text-xs block text-gray-500">Paiement mobile via Wave</span>
                </span>
            </span>
        </label>
        
        <!-- CORRECTION: Changer la valeur de 'orange money' à 'orange' -->
        <label class="block border rounded-lg p-4 hover:border-green-500 cursor-pointer transition-colors">
            <input type="radio" name="mode_paiement" value="orange" class="mr-2">
            <span class="inline-flex items-center">
                <i class="fas fa-mobile-alt text-orange-500 mr-2 text-xl"></i> 
                <span>
                    <span class="font-medium">Orange Money</span>
                    <span class="text-xs block text-gray-500">Paiement mobile via Orange Money</span>
                </span>
            </span>
        </label>
        
        <!-- Option pour les jetons si nécessaire -->
        <label class="block border rounded-lg p-4 hover:border-green-500 cursor-pointer transition-colors">
            <input type="radio" name="mode_paiement" value="tokens" class="mr-2">
            <span class="inline-flex items-center">
                <i class="fas fa-coins text-yellow-500 mr-2 text-xl"></i> 
                <span>
                    <span class="font-medium">Jetons</span>
                    <span class="text-xs block text-gray-500">Solde actuel: {{ Auth::user()->tokens ?? 0 }} jetons</span>
                </span>
            </span>
        </label>
    </div>
</div>
            
           
        </div>
     <button type="submit" class="w-full bg-red-600 text-white py-3 rounded font-bold">
    Confirmer la commande
    </button>

    </form>
</div>




<script>

document.addEventListener('DOMContentLoaded', function() {
    // 1. Initialisation des données avec vérification de null
    const tarifs = @json($tarifs ?? []);
    const zones = @json($zones ?? []);
    const supplements = @json($supplements ?? ['standard' => 500, 'express' => 1000]);
    
    // Debug - afficher les données dans la console
    console.log('Tarifs:', tarifs);
    console.log('Zones:', zones);
    console.log('Suppléments:', supplements);
    
    // 2. Définition des régions connues
    const allRegions = new Set();
    zones.forEach(zone => {
        allRegions.add(zone.region_depart);
        allRegions.add(zone.region_arrivee);
    });
    const regionsConnues = Array.from(allRegions);
    console.log('Régions connues:', regionsConnues);

    // 3. Fonction de normalisation
    const normaliserTexte = (texte) => {
        if (!texte) return '';
        return texte.toLowerCase().normalize("NFD").replace(/[\u0300-\u036f]/g, "");
    };

    // 4. Fonction d'extraction de région
    function extraireRegionDepuisAdresse(adresse) {
        if (!adresse) return null;
        
        const adresseNorm = normaliserTexte(adresse);
        
        for (const region of regionsConnues) {
            if (adresseNorm.includes(normaliserTexte(region))) {
                return region;
            }
        }
        
        return null;
    }

    // 5. Fonction de calcul de prix
    function calculerPrix() {
        const elements = {
            typeColis: document.querySelector('[name="type_colis"]'),
            typeLivraison: document.querySelector('[name="type_livraison"]'),
            adresseDepart: document.querySelector('[name="adresse_depart"]'),
            adresseArrivee: document.querySelector('[name="adresse_arrivee"]'),
            prixAffichage: document.getElementById('prix_affichage'),
            prixHidden: document.getElementById('prix_hidden'),
            regionDepart: document.getElementById('region_depart'),
            regionArrivee: document.getElementById('region_arrivee'),
            typeZone: document.getElementById('type_zone')
        };

        // Validation des éléments
        if (!Object.values(elements).every(el => el)) {
            console.error("Certains éléments du formulaire n'ont pas été trouvés");
            return;
        }
        
        // Vérifier que les champs ne sont pas vides
        if (!elements.typeColis.value || !elements.typeLivraison.value || 
            !elements.adresseDepart.value || !elements.adresseArrivee.value) {
            elements.prixAffichage.value = 'Veuillez remplir tous les champs';
            return;
        }

        const regionDepart = extraireRegionDepuisAdresse(elements.adresseDepart.value);
        const regionArrivee = extraireRegionDepuisAdresse(elements.adresseArrivee.value);
        
        console.log('Région départ:', regionDepart);
        console.log('Région arrivée:', regionArrivee);

        // Stockage des régions dans les champs cachés
        elements.regionDepart.value = regionDepart;
        elements.regionArrivee.value = regionArrivee;

        if (!regionDepart || !regionArrivee) {
            elements.prixAffichage.value = 'Adresse non reconnue';
            return;
        }

        // Trouver la zone correspondante
        const zone = zones.find(z => 
            z.region_depart === regionDepart && 
            z.region_arrivee === regionArrivee
        );
        
        console.log('Zone trouvée:', zone);

        if (!zone) {
            elements.prixAffichage.value = 'Nous ne livrons pas actuellement dans ces zone.';
            return;
        }
        
        // Stocker le type de zone
        elements.typeZone.value = zone.type_zone;
        
        // Récupérer le type de livraison sélectionné
        const typeLivraison = elements.typeLivraison.value.toLowerCase();
        
        // MODIFICATION: Rechercher le tarif sans tenir compte du type de livraison
        console.log('Recherche de tarif avec:', {
            'type_zone': zone.type_zone,
            'tranche_poids': elements.typeColis.value
        });

        // Vérifier tous les tarifs disponibles
        tarifs.forEach((t, index) => {
            console.log(`Tarif ${index + 1}:`, {
                'zone (BD)': t.zone,
                'type_zone (attendu)': zone.type_zone,
                'match zone?': t.zone === zone.type_zone,
                'tranche_poids (BD)': t.tranche_poids,
                'tranche_poids (formulaire)': elements.typeColis.value,
                'match poids?': t.tranche_poids === elements.typeColis.value
            });
        });

        // Recherche du tarif sans tenir compte du type de livraison
        const tarif = tarifs.find(t => {
            // Option 1: Vérifier si t.type_zone existe et correspond
            if (t.type_zone && t.type_zone === zone.type_zone) {
                return t.tranche_poids === elements.typeColis.value;
            }
            
            // Option 2: Vérifier si t.zone correspond au type_zone de notre zone
            if (t.zone === zone.type_zone) {
                return t.tranche_poids === elements.typeColis.value;
            }
            
            // Option 3: Vérifier directement par région
            if (t.zone === regionDepart || t.zone === regionArrivee) {
                return t.tranche_poids === elements.typeColis.value;
            }
            
            return false;
        });
        
        console.log('Tarif trouvé:', tarif);

        let prixBase = 0;
        let prixFinal = 0;
        let prixAffichage = '';

        if (tarif) {
            prixBase = Number(tarif.prix);
            
            // Ajouter le supplément selon le type de livraison
            const supplement = supplements[typeLivraison] || 0;
            prixFinal = prixBase + supplement;
            
            prixAffichage = `${prixFinal} FCFA (Base: ${prixBase} + ${supplement} FCFA)`;
        } else {
            // Si aucun tarif exact n'est trouvé, chercher le plus proche
            const tarifsZone = tarifs.filter(t => 
                t.zone === zone.type_zone || 
                t.type_zone === zone.type_zone ||
                t.zone === regionDepart || 
                t.zone === regionArrivee
            );
            
            console.log('Tarifs pour cette zone:', tarifsZone);
            
            if (tarifsZone.length > 0) {
                // Prendre le premier tarif disponible comme solution de secours
                const tarifFallback = tarifsZone[0];
                prixBase = Number(tarifFallback.prix);
                
                // Ajouter le supplément selon le type de livraison
                const supplement = supplements[typeLivraison] || 0;
                prixFinal = prixBase + supplement;
                
                prixAffichage = `${prixFinal} FCFA (Base approx.: ${prixBase} + ${supplement} FCFA)`;
                console.log('Tarif approximatif utilisé:', tarifFallback);
            } else {
                // Dernier recours: prendre n'importe quel tarif
                if (tarifs.length > 0) {
                    const tarifDefault = tarifs[0];
                    prixBase = Number(tarifDefault.prix);
                    
                    // Ajouter le supplément selon le type de livraison
                    const supplement = supplements[typeLivraison] || 0;
                    prixFinal = prixBase + supplement;
                    
                    prixAffichage = `${prixFinal} FCFA (Base défaut: ${prixBase} + ${supplement} FCFA)`;
                    console.log('Tarif par défaut utilisé:', tarifDefault);
                } else {
                    prixAffichage = 'Tarif non disponible';
                }
            }
        }

        elements.prixAffichage.value = prixAffichage;
        
        // Stocker le prix de base (sans supplément) dans le champ caché pour envoi au serveur
        if (prixBase > 0) {
            elements.prixHidden.value = prixBase;
        }
    }

    // 6. Écouteurs d'événements pour le calcul de prix
    const inputs = [
        '[name="type_colis"]',
        '[name="type_livraison"]', 
        '[name="adresse_depart"]',
        '[name="adresse_arrivee"]'
    ];
    
    inputs.forEach(selector => {
        const el = document.querySelector(selector);
        if (el) {
            el.addEventListener('change', calculerPrix);
            el.addEventListener('input', calculerPrix);
        }
    });

    // 7. Calcul initial avec délai pour s'assurer que tout est chargé
    setTimeout(calculerPrix, 500);

    // 8. Suggestions d'adresse basées sur les régions connues
    function setupSuggestion(inputId, suggestionsBoxId) {
        const input = document.getElementById(inputId);
        const suggestionsBox = document.getElementById(suggestionsBoxId);

        input.addEventListener('input', function () {
            const valeur = normaliserTexte(input.value);
            suggestionsBox.innerHTML = '';

            if (valeur.length < 2) return;

            const suggestions = regionsConnues.filter(region =>
                normaliserTexte(region).includes(valeur)
            );

            suggestions.forEach(region => {
                const item = document.createElement('li');
                item.textContent = region;
                item.className = 'p-2 hover:bg-gray-100 cursor-pointer';

                // Utiliser mousedown au lieu de click
                item.addEventListener('mousedown', function (e) {
                    e.preventDefault(); // empêche le blur de s'exécuter avant
                    input.value = region;
                    suggestionsBox.innerHTML = '';
                    
                    // Déclencher manuellement l'événement change pour activer l'affichage du champ de précision
                    const changeEvent = new Event('change');
                    input.dispatchEvent(changeEvent);
                    
                    calculerPrix(); // recalcul après sélection
                });

                suggestionsBox.appendChild(item);
            });
        });

        // Cacher les suggestions quand on sort du champ
        input.addEventListener('blur', () => {
            setTimeout(() => {
                suggestionsBox.innerHTML = '';
            }, 200); // délai pour permettre le clic
        });
    }

    // Appel pour les deux champs
    setupSuggestion('adresse_depart', 'suggestions_depart');
    setupSuggestion('adresse_arrivee', 'suggestions_arrivee');

    // 9. Fonction pour afficher ou cacher le champ de précision
    function afficherChampPrecision(adresse, boxId, labelId) {
        const region = extraireRegionDepuisAdresse(adresse);
        console.log("Région trouvée :", region); // Debug
        const box = document.getElementById(boxId);
        const label = document.getElementById(labelId);

        if (region) {
            // Mise à jour du span, pas du label entier
            label.textContent = region;
            box.style.display = 'block';
        } else {
            box.style.display = 'none';
        }
    }

    // 10. Configurez les écouteurs d'événements pour les champs de précision
    function setupPrecisions(inputId, boxId, labelId) {
        const input = document.getElementById(inputId);
        
        // Réagir à la saisie
        input.addEventListener('input', function() {
            afficherChampPrecision(this.value, boxId, labelId);
        });
        
        // Réagir au changement (utile après sélection dans la liste)
        input.addEventListener('change', function() {
            afficherChampPrecision(this.value, boxId, labelId);
        });
        
        // Vérifier également lors du chargement initial si une valeur existe déjà
        if (input.value) {
            afficherChampPrecision(input.value, boxId, labelId);
        }
    }

    // Appliquer aux deux champs
    setupPrecisions('adresse_depart', 'precisions_depart_box', 'ville_depart_label');
    setupPrecisions('adresse_arrivee', 'precisions_arrivee_box', 'ville_arrivee_label');

    // 11. Vérifier s'il y a des valeurs déjà présentes au chargement (par exemple après validation)
    window.addEventListener('load', function() {
        const departValue = document.getElementById('adresse_depart').value;
        const arriveeValue = document.getElementById('adresse_arrivee').value;
        
        if (departValue) {
            afficherChampPrecision(departValue, 'precisions_depart_box', 'ville_depart_label');
        }
        
        if (arriveeValue) {
            afficherChampPrecision(arriveeValue, 'precisions_arrivee_box', 'ville_arrivee_label');
        }
    });
});
</script>


@endsection