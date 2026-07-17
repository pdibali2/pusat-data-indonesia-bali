<?php
// app/Models/SinonimKata.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SinonimKata extends Model
{
    protected $table = 'sinonim_kata';

    protected $fillable = ['kata', 'kata_dasar', 'sumber'];
}