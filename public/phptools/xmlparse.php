<?php
$string = <<<XML
<?xml version='1.0'?> 
<document>
 <title>Forty What?</title>
 <from>Joe</from>
 <to>Jane</to>
 <body>
  I know that's the answer -- but what's the question?
 </body>
 <arraydemo>
	<subarray1>
		Array 1
	</subarray1>
	<subarray2>
		Array 2
	</subarray2>
 </arraydemo>
</document>
XML;


$xml = simplexml_load_string($string);

print "<h1>XML</h1>"; 
print "<br><br>"; 
print_r($xml);
print "<br><br>"; 

$json = json_encode($xml, JSON_PRETTY_PRINT); 

$HTML = <<<JSON
<h1>JSON</h1>
<pre>{$json}</pre>
JSON;

print $HTML;

?>