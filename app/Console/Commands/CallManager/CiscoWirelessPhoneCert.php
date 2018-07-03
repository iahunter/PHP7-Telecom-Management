<?php

namespace App\Console\Commands\CallManager;

use Illuminate\Console\Command;

class CiscoWirelessPhoneCert extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cisco_phone:auth_server_cert_management';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Checks to make sure Radius Cert is loaded as Authentication server CA';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $cookieJar = new \GuzzleHttp\Cookie\CookieJar(true);

        $params = [
                    'cookies' => $cookieJar,
                    'verify'	 => false,
                    ];

        $base_url = 'https://10.252.210.69:8443';

        $client = new \GuzzleHttp\Client($params);

        $url = $base_url.'/CGI/Java/Serviceability?adapter=login';

        $response = $client->get($url);	// Do this to make sure we always have a cookie before sending our first request

        dd($response);
        $xml = $response->getBody()->getContents();

        // Load the Reponse HTML
        $doc = new \DOMDocument();
        libxml_use_internal_errors(true);
        $doc->loadHTML($xml);

        //dd($doc);

        $dxp = new \DOMXpath($doc);
        $csrf = $dxp->query('//input[@name="CSRFToken"]/@value')->item(0)->value;

        echo 'CSRFToken: '.$csrf.PHP_EOL;

        // Login to the Phone Webpage
        $url = $base_url.'/CGI/Java/Serviceability?adapter=loginPost';
        $form_data = ['form_params' => [
                            ['username'    => 'admin',
                            'userPassword' => env('CALLMANAGER_PASS'),
                            'CSRFToken'    => $csrf, ],
                            ],
                        'cookie' => $cookieJar,
                    ];

        $response = $client->post($url, $form_data);

        /*
        $boundary = 'my_custom_boundary';
        $multipart_form = [
            [
                'name' => 'username',
                'contents' => 'admin',
            ],
            [
                'name' => 'userPassword',
                'contents' => env('CALLMANAGER_PASS'),
            ],
            [
                'name' => 'CSRFToken',
                'contents' => $csrf,
            ],
        ];

        $params = [
            'headers' => [
                'Connection' => 'close',
                'Content-Type' => 'multipart/form-data; boundary='.$boundary,
            ],
            'body' => new \GuzzleHttp\Psr7\MultipartStream($multipart_form, $boundary), // here is all the magic
        ];

        $res = $client->request('POST', $url, $params);
        */

        /*
        $options=[
          'body'=>['foo'=>'bar'],
          'headers' => ['Content-Type'=>'multipart/form-data']
        ];

        $request = $client->createRequest('POST', $url, $options);
        $response = $this->httpClient->send($request);
        //dd($response);
        */

        $xml = $response->getBody()->getContents();
        echo $xml;

        // Get Certificate Page
        $url = $base_url.'/CGI/Java/Serviceability?adapter=certificate';
        $response = $client->get($url);
        $xml = $response->getBody()->getContents();
        //echo $xml;
    }
}
