<?php

/**
 * Données par défaut canoniques, partagées par tous les installeurs/seeders
 * (install.php, setup.php, scripts/migrate.php).
 *
 * Volontairement SANS dépendance (ni config, ni DB) : ces fonctions ne
 * renvoient que des tableaux, afin de pouvoir être utilisées avant même que
 * l'application ne soit configurée.
 */

/**
 * Réglages par défaut du site.
 *
 * @param string $siteName Nom du site (saisi au moment de l'installation).
 * @param string $email    Email de contact (souvent l'email admin ; vide si inconnu).
 * @return array<string,string>
 */
function defaultSettings(string $siteName = 'Cabinet Infirmier Garnier', string $email = ''): array
{
    return [
        'site_name'        => $siteName,
        'site_description' => 'Cabinet infirmier à Nice - Soins à domicile et au cabinet',
        'address'          => '123 Avenue Jean Médecin, 06000 Nice',
        'phone'            => '',
        'email'            => $email,
        'facebook_url'     => '',
        'instagram_url'    => '',
        'whatsapp_number'  => '',
        'opening_hours'    => 'Lundi - Vendredi : 7h00 - 19h00 | Samedi : 8h00 - 12h00',
        'slot_duration'    => '30',
        'max_advance_days' => '30',
        'rgpd_text'        => 'Vos données personnelles sont collectées uniquement pour la gestion '
            . 'de vos rendez-vous et sont conservées conformément au RGPD. Vous pouvez exercer vos '
            . 'droits d\'accès, de rectification et de suppression en nous contactant.',
    ];
}

/**
 * Créneaux de disponibilité par défaut : Lun-Ven 07:00-19:00, Sam 08:00-12:00.
 *
 * @return list<array{0:int,1:string,2:string}> [jour (1=lundi), début, fin]
 */
function defaultSlots(): array
{
    $slots = [];
    for ($day = 1; $day <= 5; $day++) {
        $slots[] = [$day, '07:00', '19:00'];
    }
    $slots[] = [6, '08:00', '12:00'];

    return $slots;
}
