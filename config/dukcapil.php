<?php

return [
    'name' => 'Dinas Kependudukan dan Pencatatan Sipil Kabupaten Gorontalo',
    'short_name' => 'Dukcapil Kab. Gorontalo',
    'public_url' => env('DUKCAPIL_PUBLIC_URL', 'https://dukcapil.gorontalokab.go.id'),
    'service_url' => env('DUKCAPIL_SERVICE_URL', 'https://siprima.gorontalokab.go.id/'),
    'service_hours' => 'Senin–Jumat, 08.00–15.30 WITA',
    'complaints' => [
        'whatsapp_number' => '0811434356',
        'whatsapp_url' => 'https://wa.me/62811434356',
        'identity_consultation_number' => '081243425557',
        'identity_consultation_url' => 'https://wa.me/6281243425557',
        'siprima_whistleblowing_url' => env('DUKCAPIL_SERVICE_URL', 'https://siprima.gorontalokab.go.id/'),
        'siprima_whistleblowing_publications_url' => 'https://siprima.gorontalokab.go.id/informasi_whistleblowing.php',
        'sp4n_lapor_url' => 'https://www.lapor.go.id/',
    ],
    'seo' => [
        'description' => 'Portal informasi resmi Dinas Kependudukan dan Pencatatan Sipil Kabupaten Gorontalo.',
        'social_image' => env('DUKCAPIL_SOCIAL_IMAGE', 'https://dukcapil.gorontalokab.go.id/images/dukcapil_.jpeg'),
    ],
    'uploads' => [
        'image_max_kilobytes' => 1024,
    ],
    'maintenance' => [
        'web_migrations_enabled' => env('DUKCAPIL_WEB_MIGRATIONS_ENABLED', false),
    ],
    'office' => [
        'address' => "Jalan Kolonel Rauf Mo'o, Limboto, Kabupaten Gorontalo, Gorontalo 96212",
        'email' => env('DUKCAPIL_CONTACT_EMAIL', 'dkpskabgor@gmail.com'),
        'phone' => env('DUKCAPIL_CONTACT_PHONE', '+62 812-4342-5557'),
        'hours' => 'Senin–Jumat, pendaftaran antrean 08.00–14.00 WITA',
        'map_url' => 'https://www.google.com/maps/search/?api=1&query=Dinas+Dukcapil+Kabupaten+Gorontalo',
    ],
    'admin' => [
        'super_email' => env('DUKCAPIL_SUPER_ADMIN_EMAIL'),
        'super_password' => env('DUKCAPIL_SUPER_ADMIN_PASSWORD'),
        'editor_email' => env('DUKCAPIL_NEWS_EDITOR_EMAIL'),
        'editor_password' => env('DUKCAPIL_NEWS_EDITOR_PASSWORD'),
    ],
];
