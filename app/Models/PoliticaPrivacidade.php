<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PoliticaPrivacidade extends Model
{
    protected $table = 'politica_privacidade';

    protected $fillable = ['texto', 'responsavel', 'email'];
}
