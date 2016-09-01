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
    //
    use DatabaseTransactions;

    protected $token;
    protected $didblocks;
    protected $didblock;
    protected $didblock_id;

    public function testDidblockAPI()
    {
        // This is the main TEST Function. PHP Unit must be started with 'test'
        echo PHP_EOL.__METHOD__.' | Starting Telephone Number API tests';

        $this->getJWT(env('TEST_USER_DN'));

        // Set the Test Variables and loop thru all tests
        $this->DidblockValidationTests();

        // Get Didblocks
        $this->getDidblocks();

        // Get the DID block ID we care about to mess with
        // Do all of our PUT tests against it to update
            // Test fail to update start and end range
            // update the type, comment, name,

        echo PHP_EOL.'Didblock testing complete'.PHP_EOL;

        echo PHP_EOL.__METHOD__.' All verification complete, testing successful, database has been cleaned up'.PHP_EOL;
    }

    // Get the Didblock creation test cases from an array
    protected function getTestData()
    {
        require __DIR__.'/DidblockTest.data';

        return $TESTS;
    }

    // Run our Didblock validation tests
    protected function DidblockValidationTests()
    {
        $tests = $this->getTestData();
        //\Metaclassing\Utility::dumper($tests);
        $count = 0;
        // Loop through and run all the tests
        foreach ($tests as $name => $test) {
            echo PHP_EOL.__METHOD__.' Case '.$count++.')';
            // Run this specific test
            $response = $this->createDidblock($test['input']);
            // Handle positive and negative tests
            if ($test['success'] === true) {
                echo ' POSITIVE '.$name.' -';
                if (isset($response->original['message'])) {
                    echo ' Returned MESSAGE: '.$response->original['message'];
                }
                $this->assertEquals(200, $response->original['status_code']);
                echo ' Created Didblock ID '.$response->original['didblock']['id'];
            } else {
                echo ' NEGATIVE '.$name.' -';
                $this->assertEquals(500, $response->original['status_code']);
                echo ' Failed to create with message '.$response->original['message'];
            }
        }
        echo PHP_EOL.'Didblock validation tests complete'.PHP_EOL;
    }

    // This just tries to create a Didblock and returns the response
    protected function createDidBlock($post)
    {
        $response = $this->call('POST',
                                '/api/didblock?token='.$this->token,
                                $post);

        return $response;
    }

    /**************************************************************************************************/

    protected function getJWT($userdn)
    {
        echo PHP_EOL.__METHOD__.' | Generating JWT for user '.$userdn;
        $credentials = ['dn' => $userdn, 'password' => ''];
        $this->token = JWTAuth::attempt($credentials);
        echo ' got token '.$this->token;
    }

    protected function getDidblocks()
    {
        echo PHP_EOL.__METHOD__.' | Getting DID Blocks';
        $response = $this->call('GET', '/api/didblock?token='.$this->token);
        $this->didblocks = $response->original['didblocks'];
        //$this->assertEquals(true, $response->original['success']);
        //dd($this->didblocks);

        if (! $response->original['status_code'] == 200) {
            \metaclassing\Utility::dumper($response);
            echo ' | Message: '.$response->original['message'];
        } else {
            echo ' | Found '.count($response->original['didblocks']).' didblocks';
            echo ' | Message: '.$response->original['message'];
            echo ' | Status Code: '.$response->original['status_code'];
            $this->assertEquals(200, $response->original['status_code']);
        }
    }

    protected function getDids()
    {
        echo PHP_EOL.__METHOD__.' | Getting DID Blocks';
        $response = $this->call('GET', '/api/didblock?token='.$this->token);
        $this->didblocks = $response->original['didblocks'];
        //dd($this->didblocks);
        if (! $response->original['status_code'] == 200) {
            \metaclassing\Utility::dumper($response);
        } else {
            echo ' | Found '.count($response->original['didblocks']).' didblocks';
            echo ' | Status Code: '.$response->original['status_code'];
            $this->assertEquals(200, $response->original['status_code']);
        }
    }

    protected function createDidblocks()
    {
        echo PHP_EOL.__METHOD__.' | Creating test Did block';
        $post = [
                'country_code'           => 1,
                'name'                   => 'TEST DID BLOCK',
                'carrier'                => 'TEST CARRIER',
                'start'                  => 1000000000,
                'end'                    => 1000009999,
                'type'                   => 'private',
                'comment'                => 'Test Comment',
                ];
        $response = $this->call('POST',
                        '/api/didblock?token='.$this->token,
                        $post);
        //dd($response);
        if (! $response->original['status_code'] == 200) {
            echo ' | Message: '.$response->original['message'];
            \metaclassing\Utility::dumper($response);
        } else {
            if (isset($response->original['didblock']['id'])) {
                $this->didblock_id = $response->original['didblock']['id'];
            }
            echo ' | Message: '.$response->original['message'];
            echo ' | Status Code: '.$response->original['status_code'];
            $this->assertEquals(200, $response->original['status_code']);
        }
    }

    protected function updateDidblocks()
    {
        echo PHP_EOL.__METHOD__.' | Updating '.$this->didblock_id.' test Did block';
        $put = [
                'country_code'         => 1,
                'name'                 => 'TEST DID BLOCK CHANGED',
                'carrier'              => 'TEST CARRIER CHANGED',
                //'start'                => 1000000000, 				// We don't want to allow changing start and end of a range.
                //'end'                  => 1000009999,
                'type'                   => 'private',
                'comment'                => 'Test Comment Updated',
                ];
        $response = $this->call('PUT',
                        '/api/didblock/'.$this->didblock_id.'?token='.$this->token,
                        $put);
        //dd($response);
        //echo $response->original['status_code'], $response->original['message'];
        //$this->assertEquals(true, $response->original['success']);
        if (! $response->original['status_code'] == 200) {
            \metaclassing\Utility::dumper($response);
        } else {
            echo ' | Status Code: '.$response->original['status_code'];
            $this->assertEquals(200, $response->original['status_code']);
        }
    }

    protected function getDidblock()
    {
        echo PHP_EOL.__METHOD__.' | Getting '.$this->didblock_id.' test Did block';
        $response = $this->call('GET',
                            '/api/didblock/'.$this->didblock_id.'?token='.$this->token);
        $this->didblock = $response->original['didblock'];

        if (! $response->original['status_code'] == 200) {
            \metaclassing\Utility::dumper($response);
        } else {
            echo ' | Status Code: '.$response->original['status_code'];
            $this->assertEquals(200, $response->original['status_code']);
        }
    }

    protected function deleteDidblocks()
    {
        echo PHP_EOL.__METHOD__.' | Deleting '.$this->didblock_id.' test Did block';
        $response = $this->call('DELETE',
                        '/api/didblock/'.$this->didblock_id.'?token='.$this->token);

        if (! $response->original['status_code'] == 200) {
            \metaclassing\Utility::dumper($response);
        } else {
            echo ' | Status Code: '.$response->original['status_code'];
            $this->assertEquals(200, $response->original['status_code']);
        }
    }

    /*
        Create Bad Data that should fail here.
    */


    /********************************************************************
        Country Code Validation
    ********************************************************************/

    protected function createDidblocks_nonnumeric_country_code()
    {
        // This test should fail because it has 11 digits on a NANP country code.
        echo PHP_EOL.__METHOD__.' | Creating test Did block with non integer';
        $post = [
                'country_code'            => '+1',
                'name'                    => 'TEST DID BLOCK',
                'carrier'                 => 'TEST CARRIER',
                'start'                   => 10000000000,
                'end'                     => 10000009999,
                ];
        $response = $this->call('POST',
                        '/api/didblock?token='.$this->token,
                        $post);
        //dd($response);

        // If the status_code is 500, then test is successfull.
        if (! $response->original['status_code'] == 500) {
            \Metaclassing\Utility::dumper($response);
        } else {
            echo ' | Message: '.$response->original['message'];
            echo ' | Status Code: '.$response->original['status_code'];
            $this->assertEquals(500, $response->original['status_code']);
        }
    }

    protected function createDidblocks_fail_no_country_code()
    {
        // This test should fail because it has 11 digits on a NANP country code.
        echo PHP_EOL.__METHOD__.' | Creating test Did block with no country code set';
        $post = [
                'name'                 => 'TEST DID BLOCK',
                'carrier'              => 'TEST CARRIER',
                'start'                => 10000000000,
                'end'                  => 10000009999,
                ];
        $response = $this->call('POST',
                        '/api/didblock?token='.$this->token,
                        $post);
        //dd($response);

        // If the status_code is 500, then test is successfull.
        if (! $response->original['status_code'] == 500) {
            \Metaclassing\Utility::dumper($response);
        } else {
            echo ' | Message: '.$response->original['message'];
            echo ' | Status Code: '.$response->original['status_code'];
            $this->assertEquals(500, $response->original['status_code']);
        }
    }

    protected function createDidblocks_fail_blank_country_code()
    {
        // This test should fail because it has 11 digits on a NANP country code.
        echo PHP_EOL.__METHOD__.' | Creating test Did block with blank country code';
        $post = [
                'country_code'         => '',
                'name'                 => 'TEST DID BLOCK',
                'carrier'              => 'TEST CARRIER',
                'start'                => 10000000000,
                'end'                  => 10000009999,
                ];
        $response = $this->call('POST',
                        '/api/didblock?token='.$this->token,
                        $post);
        //dd($response);

        // If the status_code is 500, then test is successfull.
        if (! $response->original['status_code'] == 500) {
            \Metaclassing\Utility::dumper($response);
        } else {
            echo ' | Message: '.$response->original['message'];
            echo ' | Status Code: '.$response->original['status_code'];
            $this->assertEquals(500, $response->original['status_code']);
        }
    }

    protected function createDidblocks_fail_11digits()
    {
        // This test should fail because it has 11 digits on a NANP country code.
        echo PHP_EOL.__METHOD__.' | Creating test Did block with 11 digits';
        $post = [
                'country_code'         => 1,
                'name'                 => 'TEST DID BLOCK',
                'carrier'              => 'TEST CARRIER',
                'start'                => 10000000000,
                'end'                  => 10000009999,
                ];
        $response = $this->call('POST',
                        '/api/didblock?token='.$this->token,
                        $post);
        //dd($response);

        // If the status_code is 500, then test is successfull.
        if (! $response->original['status_code'] == 500) {
            \Metaclassing\Utility::dumper($response);
        } else {
            echo ' | Message: '.$response->original['message'];
            echo ' | Status Code: '.$response->original['status_code'];
            $this->assertEquals(500, $response->original['status_code']);
        }
    }

    protected function updateDidblocks_fail_change_range()
    {
        // This test should fail because it is trying to update the start and end of an existing range. We should protect those fields from update.
        echo PHP_EOL.__METHOD__.' | Updating '.$this->didblock_id.' test Did block';
        $put = [
                'country_code'         => 1,
                'name'                 => 'TEST DID BLOCK CHANGE FAIL',
                'carrier'              => 'TEST CARRIER CHANGED FAIL',
                'start'                => 1000000000,                // We don't want to allow changing start and end of a range.
                'end'                  => 1000000999,                // We don't want to allow changing start and end of a range.
                ];
        $response = $this->call('PUT',
                        '/api/didblock/'.$this->didblock_id.'?token='.$this->token,
                        $put);
        //dd($response);

        // If the status_code is 500, then test is successfull.
        if (! $response->original['status_code'] == 500) {
            \Metaclassing\Utility::dumper($response);
        } else {
            echo ' | Message: '.$response->original['message'];
            echo ' | Status Code: '.$response->original['status_code'];
            $this->assertEquals(500, $response->original['status_code']);
        }
    }

    /*
    FUTURE TESTS GO HERE
    /**/
}
