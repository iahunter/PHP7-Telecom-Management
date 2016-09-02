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

class updateDidblockTest extends TestCase
{
    //
    use DatabaseTransactions;

    protected $token;
    protected $didblocks;
    protected $didblock;
    protected $didblock_id;
	protected $dids;
	
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
        echo PHP_EOL.__METHOD__.' | Starting Update API tests';

        $this->getJWT(env('TEST_USER_DN'));
		
		$this->createDidblock();						// Create Seed Data for Test. 
        $this->updateDidblockValidationTests();			// Set the Test Variables and loop thru all tests

        // Get Didblocks
        $this->getDidblocks();
		$this->getDidsbyBlockID();

        // Get the DID block ID we care about to mess with
        // Do all of our PUT tests against it to update
            // Test fail to update start and end range
            // update the type, comment, name,

        echo PHP_EOL.'Didblock testing complete'.PHP_EOL;

        echo PHP_EOL.__METHOD__.' All verification complete, testing successful, database has been cleaned up'.PHP_EOL;
    }
	
	// Seed your Block to work on. 
	protected function createDidblock()
    {
        echo PHP_EOL.__METHOD__.' | Creating the update test Did block';
        $input = [
                'country_code'           => 1,
                'name'                   => 'TEST DID BLOCK',
                'carrier'                => 'TEST CARRIER',
                'start'                  => 1004560800,
                'end'                    => 1004560809,
                'type'                   => 'private',
                'comment'                => 'Test Comment',
                ];
        $response = $this->call('POST',
                        '/api/didblock?token='.$this->token,
                        $input);
        //dd($response);
        if (! $response->original['status_code'] == 200) {
            echo ' | Message: '.$response->original['message'];
        } else {
            if (isset($response->original['didblock']['id'])) {
                $this->didblock_id = $response->original['didblock']['id'];
            }
            echo ' | Got ID: '.$this->didblock_id;
            $this->assertEquals(200, $response->original['status_code']);
        }
    }

    // Get the Didblock update test cases from an array
    protected function getDidblockTestData()
    {
        require __DIR__.'/updateDidblockTest.data';
        return $TESTS;
    }

    // Run our Didblock validation tests
    protected function updateDidblockValidationTests()
    {
        $tests = $this->getDidblockTestData();
        $count = 0;
        // Loop through and run all the tests
        foreach ($tests as $name => $test) {
            echo PHP_EOL.__METHOD__.' Case '.$count++.')';
            // Run this specific test
            $response = $this->updateDidblock($test['input']);
            // Handle positive and negative tests
            if ($test['success'] === true) {
                echo ' POSITIVE '.$name.' -';
                if (isset($response->original['message'])) {
                    echo ' Returned MESSAGE: '.$response->original['message'];
                }
                $this->assertEquals(200, $response->original['status_code']);
                echo ' Updated Didblock ID '.$response->original['didblock']['id'];
            } else {
                echo ' NEGATIVE '.$name.' -';
                $this->assertEquals(500, $response->original['status_code']);
                echo ' Failed to create with message '.$response->original['message'];
            }
        }
        echo PHP_EOL.'Didblock Update validation tests complete'.PHP_EOL;
    }
	
	protected function updateDidblock($input)
    {
        $response = $this->call('PUT',
                        '/api/didblock/'.$this->didblock_id.'?token='.$this->token,
                        $input);
		return $response;
    }

		
	protected function getDidblocks()
    {
        echo PHP_EOL.__METHOD__.' | Getting DID Blocks';
        $response = $this->call('GET', '/api/didblock?token='.$this->token);
        
		/*
		foreach($response->original['didblocks'] as $i){								// Print out all the Block IDs found. 
			//dd($i);
			echo PHP_EOL."Found Block ID: ".$i['id'];
		}
		*/
		$this->didblocks = $response->original['didblocks'];

        if ($response->original['status_code'] != 200) {
            dd($response);
            echo ' - Message: '.$response->original['message'];
        } else {
            echo ' - Found '.count($response->original['didblocks']).' didblocks';
            $this->assertEquals(200, $response->original['status_code']);
        }
    }

    protected function getDidsbyBlockID()
    {
        echo PHP_EOL.__METHOD__.' | Getting Child DIDs by Block ID';
        $response = $this->call('GET', '/api/didblock/'.$this->didblock_id.'/dids?token='.$this->token);
        $this->dids = $response->original['dids'];
        //dd($this->dids);
        if ($response->original['status_code'] != 200) {
            dd($response);
        } else {
            echo ' | Found '.count($response->original['dids']).' dids in block in Block ID '.$this->didblock_id;
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
}
