<?php

return [
    // Lokasi soffice (LibreOffice). Di Windows/XAMPP biasanya:
    // C:\Program Files\LibreOffice\program\soffice.exe
    'soffice' => env('SOFFICE_PATH', 'soffice'),

    // Folder berisi file .pptx (taruh Template1.pptx dst. di sini)
    'path' => storage_path('app/templates/pptx'),

    // Folder hasil gambar pratinjau (dilayani lewat storage:link)
    'preview_path' => storage_path('app/public/template-previews'),

    // key => metadata. Tambahkan template-04 ... template-10 dengan pola yang sama.
    'templates' => [
        'template-01' => ['name' => 'Template 01', 'file' => 'Template1.pptx'],
        'template-02' => ['name' => 'Template 02', 'file' => 'Template2.pptx'],
        'template-03' => ['name' => 'Template 03', 'file' => 'Template3.pptx'],
    ],
];