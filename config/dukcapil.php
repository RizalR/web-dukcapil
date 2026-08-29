<?php

return [
    'name' => 'Dinas Kependudukan dan Pencatatan Sipil Kabupaten Gorontalo',
    'short_name' => 'Dukcapil Kab. Gorontalo',
    'service_url' => env('DUKCAPIL_SERVICE_URL', 'https://siprima.gorontalokab.go.id/'),
    'uploads' => [
        'image_max_kilobytes' => 1024,
    ],
    'office' => [
        'address' => "Jalan Kolonel Rauf Mo'o, Limboto, Kabupaten Gorontalo, Gorontalo 96212",
        'email' => env('DUKCAPIL_CONTACT_EMAIL', 'dukcapil@gorontalokab.go.id'),
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
