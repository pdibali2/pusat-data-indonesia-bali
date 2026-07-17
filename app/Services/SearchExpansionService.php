<?php
// app/Services/SearchExpansionService.php

namespace App\Services;

use App\Models\SinonimKata;

class SearchExpansionService
{
    protected StemmerService $stemmer;
    protected array $cache = [];

    /** Sinonim di bawah panjang ini terlalu generik/rawan false-match, jangan dipakai sebagai term pencarian */
    protected int $minSinonimLength = 4;

    /** Kalau satu kata cocok ke lebih dari sekian grup kata_dasar, anggap terlalu ambigu → skip sinonim, cuma pakai kata asli+stem */
    protected int $maxAmbiguousGroups = 2;

    public function __construct(StemmerService $stemmer)
    {
        $this->stemmer = $stemmer;
    }

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
        $lookupTerms = array_unique([$keyword, $stemmed]);

        $grup = SinonimKata::whereIn('kata', $lookupTerms)->pluck('kata_dasar')->unique();

        $sinonim = [];

        // BARU: kalau kata ini ambigu (masuk ke banyak grup berbeda), jangan expand sinonim sama sekali —
        // terlalu berisiko menarik makna yang tidak relevan.
        if ($grup->isNotEmpty() && $grup->count() <= $this->maxAmbiguousGroups) {
            $sinonim = SinonimKata::whereIn('kata_dasar', $grup)
                ->pluck('kata')
                ->reject(fn ($k) => in_array($k, [$keyword, $stemmed]))
                // BARU: buang sinonim yang terlalu pendek, rawan jadi substring acak di kata lain
                ->filter(fn ($k) => mb_strlen($k) >= $this->minSinonimLength)
                ->unique()
                ->values()
                ->all();
        }

        return $this->cache[$keyword] = [
            'original' => $keyword,
            'stemmed'  => $stemmed !== $keyword ? $stemmed : null,
            'sinonim'  => $sinonim,
        ];
    }
}