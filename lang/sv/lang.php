<?php

return [
    'widgets' => [
        'title_browsers' => 'Webbläsare',
        'description_browsers' => 'Denna rapporten låter dig se antalet besökare från de olika webbläsare folk använder för att besöka din sajt.',
        'title_toppages' => 'Toppsidor',
        'noresult_toppages' => 'Det fanns inga sidvisningar i det valda intervallet.',
        'title_traffic_goal' => 'Trafikmål',
        'title_traffic_overview' => 'Trafiköversikt',
        'title_traffic_sources' => 'Trafikkällor',
        'description_traffic_sources' => 'Rapporten över trafikkällorna visar vart hänvisningarna till din sajt kommer ifrån.',
    ],
    'permissions' => [
        'tab'      => 'Google Analytics Plugin',
        'settings' => 'Åtkomstinställningar',
    ],
    'strings'     => [
        'plugin_desc'    => 'Föreser rapporter från Google Analytics spårning.',
        'notconfigured'  => 'API-åtkomsten för Google Analytics är inte konfigurerad. Var vänligen konfigurera det i sidan under System / Inställningar / Google Analytics.',
        'keynotuploaded' => 'Den privata API-nyckeln för Google Analytics är inte uppladdad. Var vänligen konfigurera det i sidan under System / Inställningar / Google Analytics.',
        'tracker'        => 'Google Analytics spårning',
        'tracking'       => 'Spårning',
        'tracker_desc'   => 'Utmatar en spårningskod på en sida.',
        'settings_desc'  => 'Konfigurera API-koden och spårningsinställningar för Google Analytics.',
        'page_url'       => 'Sid-URL',
        'pageviews'      => 'Sidvisningar',
        'current'        => 'Nuvarande',
        'goal'           => 'Mål'
    ],
    'settings'    => [
        'project_name'         => 'Google API projektnamn',
        'client_id'            => 'Google API klient-ID',
        'app_email'            => 'E-postadress',
        'profile_id'           => 'ID-nummer för Analytics Vy/Profil',
        'gapi_key'             => 'Privat nyckel',
        'tracking_id'          => 'Spårnings-ID',
        'domain_name'          => 'Domännamn',
        'project_name_comment' => 'Namnet du tilldelade projektet när det skapades i Googles API-konsol',
        'client_id_comment'    => 'Du kan hitta klient-ID:t på projektsida in Googles API-konsol',
        'app_email_comment'    => 'E-postadressen som skapades av Googles API-konsol',
        'profile_id_comment'   => 'Du kan hitta det på sidan under Google Analytics Admin / Visa Inställningar',
        'gapi_key_comment'     => 'Den privata nyckelfillen du laddade ner från Googles API-konsol',
        'tracking_id_comment'  => 'Du kan hitta spårnings-ID:t på sidan under Admin / Egendomsinställningar',
        'domain_name_comment'  => 'Specificera domännamnet du ska spåra',
    ],
];
