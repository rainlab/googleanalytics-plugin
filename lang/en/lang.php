<?php

return [
    'widgets' => [
        'title_browsers' => 'Browsers',
        'description_browsers' => 'This report lets you see the number of visits from different browsers people use to reach your site.',
        'title_toppages' => 'Top Pages',
        'noresult_toppages' => 'There were no pageviews in the selected interval.',
        'title_traffic_goal' => 'Traffic Goal',
        'title_traffic_overview' => 'Traffic overview',
        'title_traffic_sources' => 'Traffic Sources',
        'description_traffic_sources' => 'The traffic sources report displays the source of referrals to your website.',
    ],
    'permissions' => [
        'tab'      => 'Google Analytics Plugin',
        'settings' => 'Settings access',
    ],
    'strings'     => [
        'plugin_desc'    => 'Provides the Google Analytics tracking and reporting.',
        'notconfigured'  => 'Google Analytics API access is not configured. Please configure it on the System / Settings / Google Analytics page.',
        'keynotuploaded' => 'Google Analytics API private key is not uploaded. Please configure Google Analytics access on the System / Settings / Google Analytics page.',
        'tracker'        => 'Google Analytics tracker',
        'tracking'       => 'Tracking',
        'tracker_desc'   => 'Outputs a tracking code on a page.',
        'settings_desc'  => 'Configure Google Analytics API code and tracking options.',
        'page_url'       => 'Page URL',
        'pageviews'      => 'Pageviews',
        'current'        => 'Current',
        'goal'           => 'Goal'
    ],
    'settings'    => [
        'project_name'         => 'Google API Project name',
        'client_id'            => 'Google API Client ID',
        'app_email'            => 'Email address',
        'profile_id'           => 'Analytics View/Profile ID number',
        'gapi_key'             => 'Private key',
        'tracking_id'          => 'Tracking ID',
        'domain_name'          => 'Domain name',
        'project_name_comment' => 'The name you assigned to the project when created in Google API Console',
        'client_id_comment'    => 'You can find the Client ID on the project page in Google API Console',
        'app_email_comment'    => 'The email address generated by Google API Console',
        'profile_id_comment'   => 'You can find it on the Google Analytics Admin / View Settings page',
        'gapi_key_comment'     => 'The private key file you downloaded from Google API Console',
        'tracking_id_comment'  => 'You can find the Tracking ID on the Admin / Property Settings page',
        'domain_name_comment'  => 'Specify the domain name you are going to track',
    ],
];
