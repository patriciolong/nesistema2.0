<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Abogado;

$sampleLawyers = [
    [
        'nombres' => 'Christian',
        'apellidos' => 'Moreno',
        'especialidad' => 'Derecho Notarial, Apostillas y Trámites Consulares',
        'colegio_abogados' => 'New York State Notary Public / Ecuador Legal Network',
        'matricula_profesional' => 'Reg. NYS-2018-0914',
        'ciudad' => 'New York, NY',
        'pais' => 'Estados Unidos',
        'experiencia_anios' => 12,
        'idiomas' => 'Español, Inglés',
        'biografia_corta' => 'Especialista en estructuración y otorgamiento de poderes notariales internacionales, protocolización de escrituras, legalizaciones consulares y coordinación jurídica directa entre EE.UU. y Ecuador.',
        'foto' => 'assets/images/team/chris-portrait.jpg',
        'telefono_privado' => '+1 (212) 810-7721',
        'email_privado' => 'chris@notariaecuador.com',
        'estado' => 'activo',
        'destacado' => true,
    ],
    [
        'nombres' => 'Dra. María Elena',
        'apellidos' => 'Vintimilla Castro',
        'especialidad' => 'Derecho Civil, Sucesiones y Bienes Inmuebles',
        'colegio_abogados' => 'Colegio de Abogados del Azuay',
        'matricula_profesional' => 'Matrícula N° 01-2012-3841',
        'ciudad' => 'Cuenca',
        'pais' => 'Ecuador',
        'experiencia_anios' => 14,
        'idiomas' => 'Español, Inglés',
        'biografia_corta' => 'Especialista en herencias, particiones extrajudiciales, compraventa de inmuebles para migrantes, poderes especiales para escrituraciones y trámites ante el Registro de la Propiedad en Ecuador.',
        'foto' => 'assets/images/team/lawyer-female-1.jpg',
        'telefono_privado' => '+593 99 824 5512',
        'email_privado' => 'vintimilla.legal@notariaecuador.com',
        'estado' => 'activo',
        'destacado' => true,
    ],
    [
        'nombres' => 'Dr. Fernando',
        'apellidos' => 'Alvarado Reyes',
        'especialidad' => 'Derecho Migratorio y Visas Americanas',
        'colegio_abogados' => 'New York Immigration Law Associates / Quito Bar',
        'matricula_profesional' => 'Reg. NY-552910 / CJ-17-2015-89',
        'ciudad' => 'New York, NY',
        'pais' => 'Estados Unidos',
        'experiencia_anios' => 10,
        'idiomas' => 'Español, Inglés',
        'biografia_corta' => 'Asesoría integral en solicitudes de visa B1/B2, preparación de Cartas de Invitación, perdones migratorios, peticiones familiares y representación consular para compatriotas.',
        'foto' => 'assets/images/team/lawyer-male-1.jpg',
        'telefono_privado' => '+1 (718) 555-8910',
        'email_privado' => 'alvarado.visas@notariaecuador.com',
        'estado' => 'activo',
        'destacado' => true,
    ],
    [
        'nombres' => 'Dra. Patricia',
        'apellidos' => 'Guamán Espinoza',
        'especialidad' => 'Derecho de Familia, Divorcios y Salida de Menores',
        'colegio_abogados' => 'Colegio de Abogados de Pichincha',
        'matricula_profesional' => 'Matrícula N° 17-2016-1102',
        'ciudad' => 'Quito',
        'pais' => 'Ecuador',
        'experiencia_anios' => 9,
        'idiomas' => 'Español',
        'biografia_corta' => 'Experta en tramitación de divorcios notariales y judiciales por mutuo consentimiento desde el exterior, acuerdos de alimentos, tenencia y autorizaciones de salida del país para niños y adolescentes.',
        'foto' => 'assets/images/team/lawyer-female-2.jpg',
        'telefono_privado' => '+593 98 441 2901',
        'email_privado' => 'guaman.familia@notariaecuador.com',
        'estado' => 'activo',
        'destacado' => false,
    ],
    [
        'nombres' => 'Dr. Roberto',
        'apellidos' => 'Mendoza Zambrano',
        'especialidad' => 'Derecho Corporativo, Tributario y Contratos Mercantiles',
        'colegio_abogados' => 'Colegio de Abogados del Guayas',
        'matricula_profesional' => 'Matrícula N° 09-2014-7741',
        'ciudad' => 'Guayaquil',
        'pais' => 'Ecuador',
        'experiencia_anios' => 11,
        'idiomas' => 'Español, Inglés',
        'biografia_corta' => 'Especializado en constitución de compañías en Ecuador para inversionistas en EE.UU., acuerdos comerciales privados, representación judicial con procuración y asesoría en cumplimiento tributario ante SRI.',
        'foto' => 'assets/images/team/lawyer-male-2.jpg',
        'telefono_privado' => '+593 99 310 4488',
        'email_privado' => 'mendoza.corp@notariaecuador.com',
        'estado' => 'activo',
        'destacado' => false,
    ]
];

foreach ($sampleLawyers as $data) {
    $existing = Abogado::where('nombres', $data['nombres'])
                       ->where('apellidos', $data['apellidos'])
                       ->first();
    if (!$existing) {
        Abogado::create($data);
        echo "Created lawyer: {$data['nombres']} {$data['apellidos']}\n";
    } else {
        $existing->update($data);
        echo "Updated lawyer: {$data['nombres']} {$data['apellidos']}\n";
    }
}

echo "Initial lawyers seeded successfully!\n";
