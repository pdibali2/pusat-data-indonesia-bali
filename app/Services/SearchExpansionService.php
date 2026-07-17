<?php
// app/Services/SearchExpansionService.php

namespace App\Services;

use App\Models\SinonimKata;

class SearchExpansionService
{
    protected StemmerService $stemmer;

    /** Cache in-memory per request, biar 1 kata cuma di-lookup sekali walau dipanggil berkali-kali */
    protected array $cache = [];

    public function __construct(StemmerService $stemmer)
    {
        $this->stemmer = $stemmer;
    }

    /**
     * @param array $keywords kata-kata hasil pecah dari input user
     * @return array of ['original' => string, 'stemmed' => string, 'sinonim' => string[]]
     */
    public function expand(array $keywords): array
    {
        return array_map(fn ($kw) => $this->expandOne($kw), $keywords);
    }

    protected function expandOne(string $keyword): array
    {
        $keyword = strtolower(trim($keyword));

        if (isset($this->cache[$keyword])) {
            return $this->cache[$keyword];
        }

        $stemmed = $this->stemmer->stem($keyword);

        // cari grup sinonim berdasarkan kata asli ATAU kata dasarnya
        $lookupTerms = array_unique([$keyword, $stemmed]);

        $grup = SinonimKata::whereIn('kata', $lookupTerms)->pluck('kata_dasar');

        $sinonim = $grup->isNotEmpty()
            ? SinonimKata::whereIn('kata_dasar', $grup)
                ->pluck('kata')
                ->reject(fn ($k) => in_array($k, [$keyword, $stemmed]))
                ->unique()
                ->values()
                ->all()
            : [];

        return $this->cache[$keyword] = [
            'original' => $keyword,
            'stemmed'  => $stemmed !== $keyword ? $stemmed : null,
            'sinonim'  => $sinonim,
        ];
    }
}