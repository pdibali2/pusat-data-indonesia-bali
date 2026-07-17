<?php
// app/Services/StemmerService.php

namespace App\Services;

use Sastrawi\Stemmer\StemmerFactory;

class StemmerService
{
    protected $stemmer;

    public function __construct()
    {
        $this->stemmer = (new StemmerFactory())->createStemmer();
    }

    public function stem(string $word): string
    {
        return strtolower(trim($this->stemmer->stem($word)));
    }
}