<?php
return [
    'permissions' => [
        'tab'      => 'Wtyczka GoogleAnalytics',
        'settings' => 'Dostęp do ustawień',
    ],
    'strings'     => [
        'plugin_desc'    => 'Umożliwia śledzenie i raportowanie za pomocą Google Analytics.',
        'notconfigured'  => 'Dostęp do API Google Analytics API jest nieskonfigurowany. Skonfiguruj go w zakładce Ustawienia / Google Analytics',
        'keynotuploaded' => 'Klucz prywatny Google Analytics API nie został załadowany. Skonfiguruj wtyczkę w zakładce Ustawienia / Google Analytics',
        'tracker'        => 'Tracker Google Analytics',
        'tracking'       => 'Śledzenie',
        'tracker_desc'   => 'Umieszcza kod śledzący na stronie.',
        'settings_desc'  => 'Skonfiguruj API Google Analytics oraz opcja śledzenia.',
    ],
    'settings'    => [
        'project_name'         => 'Nazwa projektu Google API',
        'client_id'            => 'ID klienta Google API',
        'app_email'            => 'Adres e-mail',
        'profile_id'           => 'ID Widoku / Profilu Google Analytics',
        'gapi_key'             => 'Klucz prywatny',
        'tracking_id'          => 'ID śledzenia',
        'domain_name'          => 'Nazwa domeny',
        'project_name_comment' => 'Nazwa projektu, wybrana podczas tworzenia go w konsoli Google API',
        'client_id_comment'    => 'ID klienta można znaleźć na stronie projektu w konsoli Google API',
        'app_email_comment'    => 'Adres e-mail wygenerowany przez konsolę Google API',
        'profile_id_comment'   => 'Można go znaleźć w panelu Google Analytics, w ustawieniach widoku.',
        'gapi_key_comment'     => 'Klucz prywatny pobrany z konsoli Google API',
        'tracking_id_comment'  => 'ID śledzenia można znaleźć w ustawieniach Google Analytics',
        'domain_name_comment'  => 'Domena, która będzie śledzona.',
    ],
];