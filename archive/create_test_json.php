<?php

$TESTS = [		
			[
				// Create: Good Data 
				'title'	=> 'Create Valid DID Block',
				'input' => ['country_code' => 1,'name' => 'TRAVIS ROCKS AND AGAIN!!!', 'carrier' => 'TEST GOOD CARRIER', 'start' => 1004560000, 'end' => 1004560009, 'type' => 'public', 'comment' => 'TESTING'],
				'verb'	=> 'POST',
				'url'	=> '/api/didblock',
				'expected_field' => 'status_code',
				'expected_result'	=> '200',
			],
			[
				// Create: Good Data 
				'title'	=> 'Create Valid DID Block larger than 10000',
				'input' => ['country_code' => 1,'name' => 'TRAVIS ROCKS AND AGAIN!!!', 'carrier' => 'TEST GOOD CARRIER', 'start' => 100456000, 'end' => 1004560009, 'type' => 'public', 'comment' => 'TESTING'],
				'verb'	=> 'POST',
				'url'	=> '/api/didblock',
				'expected_field' => 'status_code',
				'expected_result'	=> '500',
			],
			[
				// Create: Bad Data 
				'title'	=> 'Create DID block No Start - Fail',
				'input' => ['country_code' => '1','name' => 'TEST DID BLOCK', 'carrier' => 'TEST CARRIER', 'end' => 1004560049, 'type' => 'public', 'comment' => 'TESTING'],
				'verb'	=> 'POST',
				'url'	=> '/api/didblock',
				'expected_field' => 'status_code',
				'expected_result'	=> '500',
			],
			[
				// Create: Bad Data 
				'title'	=> 'Create DID Block with 11 Digits',
				'input' => ['country_code' => '1','name' => 'TEST DID BLOCK', 'carrier' => 'TEST CARRIER', 'start' => 10045600300, 'end' => 10045600309, 'type' => 'public', 'comment' => 'TESTING'],
				'verb'	=> 'POST',
				'url'	=> '/api/didblock',
				'expected_field' => 'status_code',
				'expected_result'	=> '500',
			],
			[
				// Create: Bad Data 
				'title'	=> 'Create DID block with a non numeric country code',
				'input' => ['country_code' => '+1','name' => 'TEST DID BLOCK', 'carrier' => 'TEST CARRIER', 'start' => 1004560040, 'end' => 1004560049, 'type' => 'public', 'comment' => 'TESTING'],
				'verb'	=> 'POST',
				'url'	=> '/api/didblock',
				'expected_field' => 'status_code',
				'expected_result'	=> '500',
			],
			[
				// 
				'title'	=> 'Create DID Block with 11 Digits',
				'input' => ['country_code' => '1','name' => 'TEST DID BLOCK', 'carrier' => 'TEST CARRIER', 'start' => 1004560050, 'end' => 1004560059, 'type' => 'public', 'comment' => 'TESTING'],
				'verb'	=> 'POST',
				'url'	=> '/api/didblock',
				'expected_field' => 'status_code',
				'expected_result'	=> '500',
			],
			[
				// Create: No Name
				'title'	=> 'DID Block with No Name - Should Fail',
				'input' => ['country_code' => 1, 'carrier' => 'TEST', 'start' => 1004560010, 'end' => 1004560019, 'type' => 'public', 'comment' => 'TESTING'],
				'verb'	=> 'POST',
				'url'	=> "/api/didblock/",
				'expected_field' => 'status_code',
				'expected_result'	=> '500',
			],
			[
				// Create: No Carrier - this is optional and should pass 
				'title'	=> 'Create: DID Block with No Carrier - this is optional and should pass ',
				'input' => ['country_code' => 1, 'name' => 'TEST GOOD DID BLOCK', 'carrier' => '', 'start' => 1004560020, 'end' => 1004560029, 'type' => 'public', 'comment' => 'TESTING'],
				'verb'	=> 'POST',
				'url'	=> "/api/didblock/",
				'expected_field' => 'status_code',
				'expected_result'	=> '200',
			],
			[
				// Create: Bad Data 
				'title'	=> 'Create DID block No End - Should set to same as start',
				'input' => ['country_code' => '1','name' => 'TEST DID BLOCK', 'carrier' => 'TEST CARRIER', 'start' => 1004560040, 'type' => 'public', 'comment' => 'TESTING'],
				'verb'	=> 'POST',
				'url'	=> '/api/didblock',
				'expected_field' => 'status_code',
				'expected_result'	=> '200',
			],
			
			
	];
		
file_put_contents(__DIR__.'/tests.json', json_encode($TESTS));
