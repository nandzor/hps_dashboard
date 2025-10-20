<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\V1\BaseController;
use App\Models\HpsElektronik;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class HpsElektronikController extends BaseController
{
    /**
     * Check price and grade for electronic items
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkPrice(Request $request)
    {
        // Validate input using base response format
        $validator = Validator::make($request->all(), [
            'jenis_barang' => 'required|string',
            'merek' => 'required|string',
            'nama_barang' => 'required|string',
            'kelengkapan' => 'required|string',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors()->toArray());
        }

        $validated = $validator->validated();

        // Normalize inputs
        $jenisBarang = Str::lower(trim($validated['jenis_barang']));
        $merek = Str::lower(trim($validated['merek']));
        $namaBarang = Str::lower(trim($validated['nama_barang']));
        $kelengkapan = Str::lower(trim($validated['kelengkapan']));

        // Map kelengkapan to kondisi/grade
        $kondisiMapping = $this->mapKelengkapanToKondisi($kelengkapan);

        // Start query
        $query = HpsElektronik::where('active', true);

        // Filter by jenis_barang
        $query->whereRaw('LOWER(jenis_barang) LIKE ?', ['%' . $jenisBarang . '%']);

        // Filter by merek
        $query->whereRaw('LOWER(merek) LIKE ?', ['%' . $merek . '%']);

        // Filter by barang (nama_barang)
        $query->whereRaw('LOWER(barang) LIKE ?', ['%' . $namaBarang . '%']);

        // Clone query for different kondisi searches
        $results = collect();

        // Search for exact kondisi match first
        if ($kondisiMapping['kondisi']) {
            $exactMatch = (clone $query)
                ->whereRaw('LOWER(kondisi) = ?', [$kondisiMapping['kondisi']])
                ->first();

            if ($exactMatch) {
                $results->push($exactMatch);
            }
        }

        // Search for grade match
        if ($kondisiMapping['grade']) {
            $gradeMatch = (clone $query)
                ->whereRaw('LOWER(grade) = ?', [$kondisiMapping['grade']])
                ->first();

            if ($gradeMatch && !$results->contains('id', $gradeMatch->id)) {
                $results->push($gradeMatch);
            }
        }

        // If no exact matches, get closest matches
        if ($results->isEmpty()) {
            $closeMatches = $query
                ->orderByRaw("
                    CASE
                        WHEN LOWER(kondisi) LIKE ? THEN 1
                        WHEN LOWER(grade) LIKE ? THEN 2
                        ELSE 3
                    END
                ", ['%' . $kelengkapan . '%', '%' . $kelengkapan . '%'])
                ->limit(3)
                ->get();

            $results = $results->merge($closeMatches);
        }

        if ($results->isEmpty()) {
            return $this->notFoundResponse('Tidak ditemukan data yang sesuai');
        }

        // Format response
        $response = [
            'request' => [
                'jenis_barang' => $validated['jenis_barang'],
                'merek' => $validated['merek'],
                'nama_barang' => $validated['nama_barang'],
                'kelengkapan' => $validated['kelengkapan'],
            ],
            'results' => $results->map(function ($item) use ($validated) {
                return [
                    'id' => $item->id,
                    'jenis_barang' => $item->jenis_barang,
                    'merek' => $item->merek,
                    'barang' => $item->barang,
                    'tahun' => $item->tahun,
                    'grade' => $item->grade,
                    'kondisi' => $item->kondisi,
                    'harga' => $item->harga,
                    'harga_formatted' => format_currency_id($item->harga),
                    'match_score' => $this->calculateMatchScore($item, [
                        'jenis_barang' => $validated['jenis_barang'],
                        'merek' => $validated['merek'],
                        'nama_barang' => $validated['nama_barang'],
                        'kelengkapan' => $validated['kelengkapan'],
                    ])
                ];
            })->sortByDesc('match_score')->values(),
            'best_match' => null,
            'price_range' => null,
        ];

        // Set best match
        if ($results->isNotEmpty()) {
            $response['best_match'] = $response['results'][0];

            // Calculate price range if multiple results
            if ($results->count() > 1) {
                $prices = $results->pluck('harga');
                $response['price_range'] = [
                    'min' => $prices->min(),
                    'max' => $prices->max(),
                    'min_formatted' => format_currency_id($prices->min()),
                    'max_formatted' => format_currency_id($prices->max()),
                ];
            }
        }

        return $this->successResponse($response, 'Data harga berhasil ditemukan');
    }

    /**
     * Map kelengkapan text to kondisi/grade
     *
     * @param string $kelengkapan
     * @return array
     */
    private function mapKelengkapanToKondisi($kelengkapan)
    {
        $kelengkapan = Str::lower($kelengkapan);

        // Define mappings
        $mappings = [
            // Fullset Like New
            ['keywords' => ['fullset like new', 'full set like new', 'fs like new', 'fsln'], 'kondisi' => 'fullset like new', 'grade' => 'a'],

            // Fullset
            ['keywords' => ['fullset', 'full set', 'fs', 'lengkap'], 'kondisi' => 'fullset', 'grade' => 'b'],

            // Unit Only / Unit Saja
            ['keywords' => ['unit only', 'unit saja', 'uo', 'unit aja'], 'kondisi' => 'unit only', 'grade' => 'c'],

            // Like New
            ['keywords' => ['like new', 'ln', 'seperti baru'], 'kondisi' => 'like new', 'grade' => 'a'],

            // Second / Bekas
            ['keywords' => ['second', 'bekas', 'used'], 'kondisi' => 'second', 'grade' => 'b'],

            // Grade specific
            ['keywords' => ['grade a', 'gradea'], 'kondisi' => null, 'grade' => 'a'],
            ['keywords' => ['grade b', 'gradeb'], 'kondisi' => null, 'grade' => 'b'],
            ['keywords' => ['grade c', 'gradec'], 'kondisi' => null, 'grade' => 'c'],
        ];

        // Check each mapping
        foreach ($mappings as $map) {
            foreach ($map['keywords'] as $keyword) {
                if (Str::contains($kelengkapan, $keyword)) {
                    return [
                        'kondisi' => $map['kondisi'],
                        'grade' => $map['grade']
                    ];
                }
            }
        }

        // Default fallback
        return [
            'kondisi' => $kelengkapan,
            'grade' => null
        ];
    }

    /**
     * Calculate match score for sorting results
     *
     * @param HpsElektronik $item
     * @param array $searchParams
     * @return float
     */
    private function calculateMatchScore($item, $searchParams)
    {
        $score = 0;

        // Exact jenis_barang match
        if (Str::lower($item->jenis_barang) === Str::lower($searchParams['jenis_barang'])) {
            $score += 30;
        } elseif (Str::contains(Str::lower($item->jenis_barang), Str::lower($searchParams['jenis_barang']))) {
            $score += 20;
        }

        // Exact merek match
        if (Str::lower($item->merek) === Str::lower($searchParams['merek'])) {
            $score += 30;
        } elseif (Str::contains(Str::lower($item->merek), Str::lower($searchParams['merek']))) {
            $score += 20;
        }

        // Barang/nama_barang match
        if (Str::contains(Str::lower($item->barang), Str::lower($searchParams['nama_barang']))) {
            $score += 20;
        }

        // Kondisi/kelengkapan match
        $kondisiMapping = $this->mapKelengkapanToKondisi($searchParams['kelengkapan']);

        if ($kondisiMapping['kondisi'] && Str::lower($item->kondisi) === $kondisiMapping['kondisi']) {
            $score += 20;
        }

        if ($kondisiMapping['grade'] && Str::lower($item->grade) === $kondisiMapping['grade']) {
            $score += 10;
        }

        return $score;
    }
}
