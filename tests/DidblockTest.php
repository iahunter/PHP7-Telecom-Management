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

    // Authenticate and store JWT
    protected function getJWT($userdn)
    {
        echo PHP_EOL.__METHOD__.' | Generating JWT for user '.$userdn;
        $credentials = ['dn' => $userdn, 'password' => ''];
        $this->token = JWTAuth::attempt($credentials);
        echo ' got token '.$this->token;
    }

    public function testDidblockAPI()
    {
        // This is the main TEST Function. PHP Unit must be started with 'test'
        echo PHP_EOL.__METHOD__.' | Starting Telephone Number Create API tests';

        $this->getJWT(env('TEST_USER_DN'));                    // Get JWT for Test User
        $this->createDidblockValidationTests();                // Set the Test Variables and loop thru all tests

        echo PHP_EOL.'	*** Create Didblock testing complete'.PHP_EOL;

        echo PHP_EOL.__METHOD__.' All verification complete, testing successful, database has been cleaned up'.PHP_EOL;
    }

    // This just tries to create a Didblock and returns the response
    protected function createDidBlock($post)
    {
        $response = $this->call('POST',
                                '/api/didblock?token='.$this->token,
                                $post);

        return $response;
    }

    // Get the Didblock creation test cases from an array
    protected function getDidblockTestData()
    {
        require __DIR__.'/DidblockTest.data';

        return $TESTS;
    }

    // Run our Didblock validation tests
    protected function createDidblockValidationTests()
    {
        $tests = $this->getDidblockTestData();
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
}
