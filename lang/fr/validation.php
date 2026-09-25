<?php

/*
 | Messages de validation en francais. Sans ce fichier, la langue de repli
 | etant elle aussi le francais (APP_FALLBACK_LOCALE=fr), chaque erreur
 | s'affichait sous la forme de sa cle brute (« validation.min.string »).
 */

return [

    'accepted' => 'Le champ :attribute doit être accepté.',
    'active_url' => 'Le champ :attribute n’est pas une adresse web valide.',
    'after' => 'Le champ :attribute doit être une date postérieure au :date.',
    'after_or_equal' => 'Le champ :attribute doit être une date postérieure ou égale au :date.',
    'alpha' => 'Le champ :attribute ne peut contenir que des lettres.',
    'alpha_dash' => 'Le champ :attribute ne peut contenir que des lettres, des chiffres, des tirets et des tirets bas.',
    'alpha_num' => 'Le champ :attribute ne peut contenir que des lettres et des chiffres.',
    'array' => 'Le champ :attribute doit être une liste.',
    'before' => 'Le champ :attribute doit être une date antérieure au :date.',
    'before_or_equal' => 'Le champ :attribute doit être une date antérieure ou égale au :date.',
    'between' => [
        'array' => 'Le champ :attribute doit contenir entre :min et :max éléments.',
        'file' => 'Le fichier :attribute doit peser entre :min et :max Ko.',
        'numeric' => 'Le champ :attribute doit être compris entre :min et :max.',
        'string' => 'Le champ :attribute doit contenir entre :min et :max caractères.',
    ],
    'boolean' => 'Le champ :attribute doit être vrai ou faux.',
    'confirmed' => 'La confirmation du champ :attribute ne correspond pas.',
    'current_password' => 'Le mot de passe est incorrect.',
    'date' => 'Le champ :attribute n’est pas une date valide.',
    'date_format' => 'Le champ :attribute ne correspond pas au format :format.',
    'decimal' => 'Le champ :attribute doit comporter :decimal décimales.',
    'different' => 'Les champs :attribute et :other doivent être différents.',
    'digits' => 'Le champ :attribute doit contenir :digits chiffres.',
    'digits_between' => 'Le champ :attribute doit contenir entre :min et :max chiffres.',
    'dimensions' => 'Les dimensions de l’image :attribute ne conviennent pas.',
    'distinct' => 'Le champ :attribute a une valeur en double.',
    'email' => 'Le champ :attribute doit être une adresse mail valide.',
    'ends_with' => 'Le champ :attribute doit se terminer par : :values.',
    'exists' => 'La valeur choisie pour :attribute n’est pas valide.',
    'file' => 'Le champ :attribute doit être un fichier.',
    'filled' => 'Le champ :attribute doit avoir une valeur.',
    'gt' => [
        'array' => 'Le champ :attribute doit contenir plus de :value éléments.',
        'file' => 'Le fichier :attribute doit peser plus de :value Ko.',
        'numeric' => 'Le champ :attribute doit être supérieur à :value.',
        'string' => 'Le champ :attribute doit contenir plus de :value caractères.',
    ],
    'gte' => [
        'array' => 'Le champ :attribute doit contenir au moins :value éléments.',
        'file' => 'Le fichier :attribute doit peser au moins :value Ko.',
        'numeric' => 'Le champ :attribute doit être supérieur ou égal à :value.',
        'string' => 'Le champ :attribute doit contenir au moins :value caractères.',
    ],
    'image' => 'Le champ :attribute doit être une image.',
    'in' => 'La valeur choisie pour :attribute n’est pas valide.',
    'integer' => 'Le champ :attribute doit être un nombre entier.',
    'ip' => 'Le champ :attribute doit être une adresse IP valide.',
    'json' => 'Le champ :attribute doit être un document JSON valide.',
    'lt' => [
        'array' => 'Le champ :attribute doit contenir moins de :value éléments.',
        'file' => 'Le fichier :attribute doit peser moins de :value Ko.',
        'numeric' => 'Le champ :attribute doit être inférieur à :value.',
        'string' => 'Le champ :attribute doit contenir moins de :value caractères.',
    ],
    'lte' => [
        'array' => 'Le champ :attribute ne doit pas contenir plus de :value éléments.',
        'file' => 'Le fichier :attribute ne doit pas peser plus de :value Ko.',
        'numeric' => 'Le champ :attribute doit être inférieur ou égal à :value.',
        'string' => 'Le champ :attribute ne doit pas dépasser :value caractères.',
    ],
    'max' => [
        'array' => 'Le champ :attribute ne doit pas contenir plus de :max éléments.',
        'file' => 'Le fichier :attribute ne doit pas dépasser :max Ko.',
        'numeric' => 'Le champ :attribute ne doit pas dépasser :max.',
        'string' => 'Le champ :attribute ne doit pas dépasser :max caractères.',
    ],
    'mimes' => 'Le fichier :attribute doit être de type : :values.',
    'mimetypes' => 'Le fichier :attribute doit être de type : :values.',
    'min' => [
        'array' => 'Le champ :attribute doit contenir au moins :min éléments.',
        'file' => 'Le fichier :attribute doit peser au moins :min Ko.',
        'numeric' => 'Le champ :attribute doit être au moins égal à :min.',
        'string' => 'Le champ :attribute doit contenir au moins :min caractères.',
    ],
    'not_in' => 'La valeur choisie pour :attribute n’est pas valide.',
    'numeric' => 'Le champ :attribute doit être un nombre.',
    'present' => 'Le champ :attribute doit être présent.',
    'regex' => 'Le format du champ :attribute n’est pas valide.',
    'required' => 'Le champ :attribute est obligatoire.',
    'required_if' => 'Le champ :attribute est obligatoire quand :other vaut :value.',
    'required_with' => 'Le champ :attribute est obligatoire quand :values est renseigné.',
    'same' => 'Les champs :attribute et :other doivent être identiques.',
    'size' => [
        'array' => 'Le champ :attribute doit contenir :size éléments.',
        'file' => 'Le fichier :attribute doit peser :size Ko.',
        'numeric' => 'Le champ :attribute doit valoir :size.',
        'string' => 'Le champ :attribute doit contenir :size caractères.',
    ],
    'starts_with' => 'Le champ :attribute doit commencer par : :values.',
    'string' => 'Le champ :attribute doit être un texte.',
    'timezone' => 'Le champ :attribute doit être un fuseau horaire valide.',
    'unique' => 'Cette valeur de :attribute est déjà utilisée.',
    'uploaded' => 'Le fichier :attribute n’a pas pu être envoyé.',
    'url' => 'Le champ :attribute doit être une adresse web valide (http:// ou https://).',
    'uuid' => 'Le champ :attribute doit être un identifiant UUID valide.',

    'custom' => [
        'email' => [
            'unique' => 'Cette adresse mail est déjà utilisée par un autre compte.',
        ],
    ],

    /*
     | Noms des champs tels qu'ils paraissent a l'ecran, plutot que leur
     | nom technique.
     */
    'attributes' => [
        'firstname' => 'prénom',
        'lastname' => 'nom',
        'company' => 'société',
        'address' => 'adresse',
        'zipcode' => 'code postal',
        'city' => 'ville',
        'country' => 'pays',
        'phone' => 'téléphone',
        'mobile' => 'mobile',
        'website' => 'site web',
        'facebook_url' => 'Facebook',
        'instagram_url' => 'Instagram',
        'twitter_url' => 'X (Twitter)',
        'email' => 'adresse mail',
        'motDePasse' => 'mot de passe',
        'motDePasseActuel' => 'mot de passe actuel',
        'confirmationSuppression' => 'identifiant',
        'categorie' => 'métier',
        'statut' => 'statut',
        'titre' => 'titre',
        'description' => 'description',
        'piedDePage' => 'pied de page',
        'avatarTemp' => 'photo',
        'siret' => 'SIRET',
    ],

];
