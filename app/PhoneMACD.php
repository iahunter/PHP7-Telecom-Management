<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use GuzzleHttp\Client as GuzzleHttpClient;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Cookie\FileCookieJar as FileCookieJar;

class PhoneMACD extends Model
{
    // Added dummy class for permissions use for now.
    protected $fillable = ['uuid'];
}
