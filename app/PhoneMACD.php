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

    public function getKey()
    {
        if (! $this->uuid) {
            throw new \Exception('CUCM skeleton model has no UUID defined');
        }
        // make sure everybody agrees that we do indeed exist
        $this->exists = true;
        // ALWAYS use the LOWER CASE form of the ID if it is TEXT
        $this->uuid = strtolower($this->uuid);
        // return the lower case UUID as our unique identifier
        return $this->uuid;
    }
}
