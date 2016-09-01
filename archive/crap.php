<?php

	protected $count = 0;
    protected $start = 1004560000;
    protected $end = 1004560009;

	//Thought about creating arrays and looping thru for testing but prob not going to do that here. 
	
	/**************************************************************************************************/
	
	protected $tests;
	
	
	protected function create_tests()
	{
		$start = 1004560000;
		$end = 1004560009;
		
		$ARRAY = file_get_contents('test.json');
		$ARRAY = json_decode($ARRAY);
		
		function assign_numbers($ARRAY){
			$start = $this->start + 10;
			$end = $this->end +10;
			
			foreach($ARRAY as $DIDBlock)
			$DIDBlock['input']['$start'] = $DIDBlock['$start'] = $start;
			$DIDBlock['input']['$end'] = $DIDBlock['$end'] = $end;
		}
		
		file_put_contents('test.json', json_encode($tests));
		
		return $this->tests = $tests; 
	}
	

	/**/