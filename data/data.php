<?php
// Tableau des annonces de maisons
$maisons = [
    [
        'image' => 'https://images.unsplash.com/photo-1564013799919-ab600027ffc6?w=400&h=250&fit=crop',
        'titre' => 'Maison de campagne',
        'prix' => '2,000€ /mois',
        'localisation' => 'Lyon, France',
        'description' => 'Charmante maison de campagne offrant tranquillité et espace, idéale pour une escapade familiale avec un grand jardin et une terrasse.',
        'type' => 'Rent'
    ],
    [
        'image' => 'https://images.unsplash.com/photo-1512917774080-9991f1c4c750?w=400&h=250&fit=crop',
        'titre' => 'Villa moderne',
        'prix' => '850,000€',
        'localisation' => 'Cannes, France',
        'description' => 'Magnifique villa contemporaine avec piscine et vue panoramique sur la mer. Architecture moderne et finitions haut de gamme.',
        'type' => 'Sale'
    ],
    [
        'image' => 'https://images.unsplash.com/photo-1449844908441-8829872d2607?w=400&h=250&fit=crop',
        'titre' => 'Chalet en montagne',
        'prix' => '750,000€',
        'localisation' => 'Chamonix, France',
        'description' => 'Beau chalet traditionnel en bois, niché dans les montagnes, parfait pour les amateurs de ski et de nature. Vue imprenable sur les Alpes.',
        'type' => 'Sale'
    ]
];

// Tableau des annonces d'appartements
$appartements = [
    [
        'image' => 'https://images.unsplash.com/photo-1522708323590-d24dbb6b0267?w=400&h=250&fit=crop',
        'titre' => 'Loft moderne',
        'prix' => '1,200€ /mois',
        'localisation' => 'Paris, France',
        'description' => 'Spacieux loft avec mezzanine, situé dans un quartier branché de Paris. Lumineux et entièrement rénové avec des matériaux de qualité.',
        'type' => 'Rent'
    ],
    [
        'image' => 'https://images.unsplash.com/photo-1502672260266-1c1ef2d93688?w=400&h=250&fit=crop',
        'titre' => 'Appartement de luxe',
        'prix' => '520,000€',
        'localisation' => 'Nice, France',
        'description' => 'Superbe appartement avec terrasse et vue mer, finitions haut de gamme. Proche de la promenade des Anglais et des commerces.',
        'type' => 'Sale'
    ],
    [
        'image' => 'https://images.unsplash.com/photo-1560448204-e02f11c3d0e2?w=400&h=250&fit=crop',
        'titre' => 'Studio centre-ville',
        'prix' => '800€ /mois',
        'localisation' => 'Marseille, France',
        'description' => 'Charmant studio rénové au cœur de la ville, proche de tous les commerces et transports. Idéal pour étudiant ou jeune actif.',
        'type' => 'Rent'
    ]
];

// Données utilisateur simulées (pour les tests)
$users_simulation = [
    [
        'id' => 1,
        'email' => 'test@example.com',
        'password' => 'password123', 
        'created_at' => date('Y-m-d H:i:s')
    ],
    [
        'id' => 2,
        'email' => 'user@demo.com',
        'password' => 'demo123', 
        'created_at' => date('Y-m-d H:i:s')
    ]
];
?>