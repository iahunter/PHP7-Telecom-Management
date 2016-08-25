<?php

/**
 * DID Database Unit Tests.
 *
 * PHP version 7
 *
 *
 * @category  default
 * @author    Travis Riesenberg
 * @copyright 2015-2016 @authors
 * @license   http://www.opensource.org/licenses/mit-license.html  MIT License
 */
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\User;
use App\Didblock;
use App\Did;

class DidblockTest extends TestCase
{
    use DatabaseTransactions;

    protected $token;
    protected $didblocks;

    public function testDidblockAPI()
    {
        echo PHP_EOL.__METHOD__.' Starting CA Account API tests';
        // Seed our test data, this entire test is wrapped in a transaction so will be auto-removed
        // *** Need to change this to .env TEST_USER_DN. ***
        $this->getJWT(env('TEST_USER_DN'));
        $this->getDidblocks();
        echo PHP_EOL.__METHOD__.' All verification complete, testing successful, database has been cleaned up'.PHP_EOL;
    }

    protected function getJWT($userdn)
    {
        echo PHP_EOL.__METHOD__.' Generating JWT for user '.$userdn;
        $credentials = ['dn' => $userdn, 'password' => ''];
        $this->token = JWTAuth::attempt($credentials);
        echo ' got token '.$this->token;
    }

    protected function getDidblocks()
    {
        echo PHP_EOL.__METHOD__.' Loading latest accounts visible to current role';
        $response = $this->call('GET', '/api/didblock?token='.$this->token);
        $this->didblocks = $response->original['didblocks'];
        $this->assertEquals(true, $response->original['success']);
        echo ' - found '.count($response->original['didblocks']).' didblocks';
        dd($this->didblocks);
    }

    /*
    protected function getAccountCertificates()
    {
        echo PHP_EOL.__METHOD__.' Loading certificates for accounts';
        $this->accountcertificates = [];
        foreach ($this->accounts as $account) {
            $response = $this->call('GET',
                                    '/api/ca/account/'.$account['id'].'/certificate/?token='.$this->token);
            $this->assertEquals(true, $response->original['success']);
            $this->accountcertificates[$account['id']] = $response->original['certificates'];
            echo ' - found '.count($response->original['certificates']).' in account '.$account['id'];
        }
    }

    protected function getAccountIdByName($name)
    {
        foreach ($this->accounts as $account) {
            if ($account['name'] == $name) {
                return $account['id'];
            }
        }
        throw new \Exception('could not identify account id for account named '.$name);
    }

    protected function getAccountCertificateIdByName($account_id, $name)
    {
        foreach ($this->accountcertificates[$account_id] as $certificate) {
            if ($certificate['name'] == $name) {
                return $certificate['id'];
            }
        }
        throw new \Exception('could not identify certificate id for account id '.$account_id.' named '.$name);
    }

    protected function selfSignCaAccountCertificates()
    {
        echo PHP_EOL.__METHOD__.' Self signing phpUnit Root CA cert';
        $account_id = $this->getAccountIdByName('phpUnitCaAccount');
        $certificate_id = $this->getAccountCertificateIdByName($account_id, 'phpUnit Root CA');
        $response = $this->call('GET',
                                '/api/ca/account/'.$account_id.'/certificate/'.$certificate_id.'/sign?token='.$this->token);
        $this->assertEquals(true, $response->original['success']);
    }

    protected function createCertificate()
    {
        echo PHP_EOL.__METHOD__.' Creating new certificate for example.com';
        $account_id = $this->getAccountIdByName('phpUnitCaAccount');
        $post = [
                    'name'     => 'example.com',
                    'subjects' => ['example.com', 'www.example.com', 'test.phpunit.org'],
                    'type'     => 'server',
                ];
        $response = $this->call('POST',
                                '/api/ca/account/'.$account_id.'/certificate/?token='.$this->token,
                                $post);
        $this->assertEquals(true, $response->original['success']);
    }

    protected function generateKeys()
    {
        echo PHP_EOL.__METHOD__.' Generating keys for example.com cert';
        $account_id = $this->getAccountIdByName('phpUnitCaAccount');
        $certificate_id = $this->getAccountCertificateIdByName($account_id, 'example.com');
        $response = $this->call('GET',
                                '/api/ca/account/'.$account_id.'/certificate/'.$certificate_id.'/generatekeys?token='.$this->token);
        $this->assertEquals(true, $response->original['success']);
    }

    protected function generateCSR()
    {
        echo PHP_EOL.__METHOD__.' Generating csr for example.com cert';
        $account_id = $this->getAccountIdByName('phpUnitCaAccount');
        $certificate_id = $this->getAccountCertificateIdByName($account_id, 'example.com');
        $response = $this->call('GET',
                                '/api/ca/account/'.$account_id.'/certificate/'.$certificate_id.'/generaterequest?token='.$this->token);
        $this->assertEquals(true, $response->original['success']);
    }

    protected function signCSR()
    {
        echo PHP_EOL.__METHOD__.' Signing csr for example.com cert';
        $account_id = $this->getAccountIdByName('phpUnitCaAccount');
        $certificate_id = $this->getAccountCertificateIdByName($account_id, 'example.com');
        $response = $this->call('GET',
                                '/api/ca/account/'.$account_id.'/certificate/'.$certificate_id.'/sign?token='.$this->token);
        if (! $response->original['success']) {
            \metaclassing\Utility::dumper($response);
        }
        $this->assertEquals(true, $response->original['success']);
    }

    protected function validateCertificateRouteAccess($expected)
    {
        echo PHP_EOL.__METHOD__.' Validating certificate route access conditions';
        $i = 0;
        $account_id = $this->getAccountIdByName('phpUnitCaAccount');
        $certificate_id = $this->getAccountCertificateIdByName($account_id, 'example.com');
        //
        echo PHP_EOL.__METHOD__.' User can certificates: '.$expected[$i];
        $response = $this->call('GET', '/api/ca/account/'.$account_id.'/certificate/?token='.$this->token);
        if ($expected[$i++]) {
            $this->assertEquals(true, $response->original['success']);
        } else {
            $this->assertEquals(0, count($response->original['certificates']));
        }
        //
        echo PHP_EOL.__METHOD__.' User can view assigned certificate: '.$expected[$i];
        $response = $this->call('GET', '/api/ca/account/'.$account_id.'/certificate/'.$certificate_id.'/?token='.$this->token);
        if ($expected[$i++]) {
            $this->assertEquals(true, $response->original['success']);
        } else {
            $this->assertEquals(401, $response->original['status_code']);
        }
        //
        echo PHP_EOL.__METHOD__.' User can create new certificate: '.$expected[$i];
        $post = [
                'name'             => 'phpUnit Test Cert',
                'subjects'         => '[]',
                'type'             => 'server',
                ];
        $response = $this->call('POST',
                        '/api/ca/account/'.$account_id.'/certificate/?token='.$this->token,
                        $post);
        if ($expected[$i++]) {
            $this->assertEquals(true, $response->original['success']);
        } else {
            $this->assertEquals(401, $response->original['status_code']);
        }
        //
        echo PHP_EOL.__METHOD__.' User can generate csr: '.$expected[$i];
        $response = $this->call('GET', '/api/ca/account/'.$account_id.'/certificate/'.$certificate_id.'/generaterequest/?token='.$this->token);
        if ($expected[$i++]) {
            $this->assertEquals(true, $response->original['success']);
        } else {
            $this->assertEquals(401, $response->original['status_code']);
        }
        //
        echo PHP_EOL.__METHOD__.' User can sign csr: '.$expected[$i];
        $response = $this->call('GET', '/api/ca/account/'.$account_id.'/certificate/'.$certificate_id.'/sign/?token='.$this->token);
        if ($expected[$i++]) {
            $this->assertEquals(true, $response->original['success']);
        } else {
            $this->assertEquals(401, $response->original['status_code']);
        }
        //
        echo PHP_EOL.__METHOD__.' User can renew cert: '.$expected[$i];
        $response = $this->call('GET', '/api/ca/account/'.$account_id.'/certificate/'.$certificate_id.'/renew/?token='.$this->token);
        if ($expected[$i++]) {
            $this->assertEquals(true, $response->original['success']);
        } else {
            $this->assertEquals(401, $response->original['status_code']);
        }
        //
        echo PHP_EOL.__METHOD__.' User can view pkcs12: '.$expected[$i];
        $response = $this->call('GET', '/api/ca/account/'.$account_id.'/certificate/'.$certificate_id.'/pkcs12/?token='.$this->token);
        if ($expected[$i++]) {
            // I have literally no idea how to test this response format
        } else {
            $this->assertEquals(401, $response->original['status_code']);
        }
        //
        echo PHP_EOL.__METHOD__.' User can view pem: '.$expected[$i];
        $response = $this->call('GET', '/api/ca/account/'.$account_id.'/certificate/'.$certificate_id.'/pem/?token='.$this->token);
        if ($expected[$i++]) {
            // I have literally no idea how to test this response format
        } else {
            $this->assertEquals(401, $response->original['status_code']);
        }
    }
/**/
}
