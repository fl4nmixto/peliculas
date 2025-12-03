<?php

namespace App\Support;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class RoleCatalog
{
    protected const DEFINITIONS = [
        'director' => [
            'name' => 'Dirección',
            'category' => 'director',
            'is_featured' => true,
            'position' => 0,
            'aliases' => [
                'director',
                'direccion',
                'dirección',
                'direccion-general',
                'codireccion',
                'co-direccion',
                'direction',
            ],
        ],
        'cast-featured' => [
            'name' => 'Protagonistas',
            'category' => 'cast',
            'is_featured' => true,
            'position' => 1,
            'aliases' => [
                'cast-featured',
                'protagonistas',
                'lead',
                'protagonist',
            ],
        ],
        'cast' => [
            'name' => 'Elenco',
            'category' => 'cast',
            'is_featured' => false,
            'position' => 10,
            'aliases' => [
                'cast',
                'elenco',
                'interpretes',
                'int',
                'actor',
                'actors',
            ],
        ],
        'writer' => [
            'name' => 'Guion',
            'category' => 'crew',
            'is_featured' => false,
            'position' => 11,
            'aliases' => [
                'writer',
                'writers',
                'guion',
                'guionista',
                'screenplay',
                'script',
                'story',
                'book',
            ],
        ],
        'assistant-director' => [
            'name' => 'Asistente de dirección',
            'category' => 'crew',
            'is_featured' => false,
            'position' => 12,
            'aliases' => [
                'assistant-director',
                'asistente-de-direccion',
                'first-assistant-director',
                'second-assistant-director',
                'third-assistant-director',
            ],
        ],
        'director-of-photography' => [
            'name' => 'Dirección de fotografía',
            'category' => 'crew',
            'is_featured' => false,
            'position' => 13,
            'aliases' => [
                'director-of-photography',
                'direccion-de-fotografia',
                'cinematography',
            ],
        ],
        'music' => [
            'name' => 'Música',
            'category' => 'crew',
            'is_featured' => false,
            'position' => 14,
            'aliases' => [
                'music',
                'musica',
                'musician',
                'music-arranger',
                'original-music-composer',
                'orchestrator',
            ],
        ],
        'sound' => [
            'name' => 'Sonido',
            'category' => 'crew',
            'is_featured' => false,
            'position' => 15,
            'aliases' => [
                'sound',
                'sonido',
                'direccion-de-sonido',
                'sound-designer',
                'sound-editor',
                'sound-mixer',
                'sound-engineer',
                'sound-recordist',
            ],
        ],
        'editor' => [
            'name' => 'Montaje',
            'category' => 'crew',
            'is_featured' => false,
            'position' => 16,
            'aliases' => [
                'editor',
                'montaje',
                'editing',
                'assistant-editor',
            ],
        ],
        'production-design' => [
            'name' => 'Dirección de arte',
            'category' => 'crew',
            'is_featured' => false,
            'position' => 17,
            'aliases' => [
                'production-design',
                'direccion-de-arte',
                'art-direction',
                'arte',
            ],
        ],
        'producer' => [
            'name' => 'Producción',
            'category' => 'crew',
            'is_featured' => false,
            'position' => 18,
            'aliases' => [
                'producer',
                'produccion',
                'productor',
                'production',
            ],
        ],
        'executive-producer' => [
            'name' => 'Producción ejecutiva',
            'category' => 'crew',
            'is_featured' => false,
            'position' => 19,
            'aliases' => [
                'executive-producer',
                'produccion-ejecutiva',
                'productor-ejecutivo',
                'production-executive',
            ],
        ],
        'costume-design' => [
            'name' => 'Vestuario',
            'category' => 'crew',
            'is_featured' => false,
            'position' => 20,
            'aliases' => [
                'costume-design',
                'vestuario',
                'costume-designer',
            ],
        ],
        'makeup' => [
            'name' => 'Maquillaje',
            'category' => 'crew',
            'is_featured' => false,
            'position' => 21,
            'aliases' => [
                'makeup',
                'maquillaje',
                'makeup-artist',
            ],
        ],
        'post-production' => [
            'name' => 'Postproducción',
            'category' => 'crew',
            'is_featured' => false,
            'position' => 22,
            'aliases' => [
                'post-production',
                'coordinacion-de-post-produccion',
            ],
        ],
        'colorist' => [
            'name' => 'Colorista',
            'category' => 'crew',
            'is_featured' => false,
            'position' => 23,
            'aliases' => [
                'colorist',
                'colorista',
                'color-grading',
            ],
        ],
        'visual-effects' => [
            'name' => 'Efectos visuales',
            'category' => 'crew',
            'is_featured' => false,
            'position' => 24,
            'aliases' => [
                'visual-effects',
                'fx',
                'vfx',
            ],
        ],
        'production-company' => [
            'name' => 'Casa productora',
            'category' => 'crew',
            'is_featured' => false,
            'position' => 25,
            'aliases' => [
                'production-company',
                'casa-productora',
            ],
        ],
        'casting' => [
            'name' => 'Casting',
            'category' => 'crew',
            'is_featured' => false,
            'position' => 26,
            'aliases' => [
                'casting',
                'casting-director',
                'director-de-casting',
                'casting-assistant',
            ],
        ],
        'continuity' => [
            'name' => 'Continuidad',
            'category' => 'crew',
            'is_featured' => false,
            'position' => 27,
            'aliases' => [
                'continuity',
                'script-supervisor',
                'supervisor-de-continuidad',
                'continuista',
            ],
        ],
        'art-department' => [
            'name' => 'Departamento de arte',
            'category' => 'crew',
            'is_featured' => false,
            'position' => 28,
            'aliases' => [
                'art-department',
                'art-department-coordinator',
                'set-decorator',
                'decorador',
            ],
        ],
        'stunts' => [
            'name' => 'Especialistas',
            'category' => 'crew',
            'is_featured' => false,
            'position' => 29,
            'aliases' => [
                'stunts',
                'stunt',
                'stunt-coordinator',
                'stunt-performer',
                'doble',
            ],
        ],
        'assistant-camera' => [
            'name' => 'Asistente de cámara',
            'category' => 'crew',
            'is_featured' => false,
            'position' => 30,
            'aliases' => [
                'assistant-camera',
                'first-assistant-camera',
                'second-assistant-camera',
                'ac',
            ],
        ],
        'gaffer' => [
            'name' => 'Jefe de iluminación',
            'category' => 'crew',
            'is_featured' => false,
            'position' => 31,
            'aliases' => [
                'gaffer',
                'chief-lighting-technician',
                'iluminacion',
                'iluminación',
            ],
        ],
        'grip' => [
            'name' => 'Grip',
            'category' => 'crew',
            'is_featured' => false,
            'position' => 32,
            'aliases' => [
                'grip',
                'key-grip',
                'best-boy-grip',
            ],
        ],
    ];

    public static function match(?string $code, ?string $name): ?array
    {
        $candidates = collect([$code, $name])
            ->filter()
            ->map(fn ($value) => Str::slug($value))
            ->filter()
            ->values();

        if ($candidates->isEmpty()) {
            return null;
        }

        foreach (self::DEFINITIONS as $canonical => $definition) {
            if ($candidates->intersect($definition['aliases'])->isNotEmpty()) {
                return [
                    'code' => $canonical,
                    'attributes' => Arr::only($definition, ['name', 'category', 'is_featured', 'position']),
                ];
            }
        }

        return null;
    }

    public static function definitions(): array
    {
        return self::DEFINITIONS;
    }
}
