<?php

/**
 * ExampleAPI - Laravel API example with enterprise directory authentication.
 *
 * PHP version 7
 *
 * This auth controller is an example for creators to use and extend for
 * enterprise directory integrated single-sign-on
 *
 * @category  default
 *
 * @author    Metaclassing <Metaclassing@SecureObscure.com>
 * @copyright 2015-2016 @authors
 * @license   http://www.opensource.org/licenses/mit-license.html  MIT License
 */

namespace App\Http\Controllers\Auth;

use App\User;
use Validator;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
// added by 3
use App\Http\Controllers\Controller;
use Spatie\Activitylog\Models\Activity;
// Logger
use Illuminate\Foundation\Auth\ThrottlesLogins;

class AuthController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Registration & Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users, as well as the
    | authentication of existing users. By default, this controller uses
    | a simple trait to add these behaviors. Why don't you explore it?
    |
    */

    use ThrottlesLogins;

    /**
     * Where to redirect users after login / registration.
     *
     * @var string
     */
    protected $redirectTo = '/';
    private $ldap = 0;

    /**
     * Create a new authentication controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        // Let unauthenticated users attempt to authenticate, all other functions are blocked
        $this->middleware('jwt.auth', ['except' => ['authenticate']]);
    }

    // Added by 3, try to cert auth, if that fails try to post ldap username/password auth, if that fails go away.
    public function authenticate(Request $request)
    {
        // Testing
        //activity()->withProperties($request)->log("User attempting to Authenticate");

        $error = '';
        // Only authenticate users based on CERTIFICATE info passed from webserver
        if ($_SERVER['SSL_CLIENT_VERIFY'] == 'SUCCESS') {
            try {
                return $this->goodauth($this->certauth());
            } catch (\Exception $e) {
                // Cert auth failure, continue to LDAP auth test
                $error .= "\tError with TLS client certificate authentication {$e->getMessage()}\n";
            }
        }
        if (env('LDAP_AUTH')) {
            // Attempt to authenticate all users based on LDAP username and password in the request
            try {
                //return $this->ldapauth($request);
                return $this->goodauth($this->ldapauth($request));
            } catch (\Exception $e) {
                $error .= "\tError with LDAP authentication. {$e->getMessage()}\n";
            }
        }

        // Log activity
        //activity()->withProperties($request)->log("All authentication methods available have failed, ".$error);
        activity('authlog')->withProperties(['username' => $request->username])->log('Auth Error:, '.$error);

        abort(401, 'Authentication failed. '.$error);
    }

    public function renew(Request $request)
    {
        $response = [];
        try {
            //$user = $this->auth->user();
            $user = JWTAuth::parseToken()->authenticate();
            /*
            if($user->ip != $_SERVER['REMOTE_ADDR']) {
                throw new \Exception('Error authenticating token for your IP');
            }
            */
            $credentials = ['id' => $user->id, 'password' => ''];
            // This should NEVER fail.
            if (! $token = JWTAuth::attempt($credentials)) {
                throw new \Exception('Failed to generate JWT for user');
            }
            // Build our response
            $response['token'] = $token;
            $response['success'] = true;
        } catch (\Exception $e) {
            $response['success'] = false;
            $response['message'] = 'Encountered exception: '.$e->getMessage();
        }

        return response()->json($response);
    }

    protected function certauth()
    {
        // Make sure we got a client certificate from the web server
        if (! $_SERVER['SSL_CLIENT_CERT']) {
            throw new \Exception('TLS client certificate missing');
        }
        // try to parse the certificate we got
        $x509 = new \phpseclib\File\X509();
        // NGINX screws up the cert by putting a bunch of tab characters into it so we need to clean those out
        $asciicert = str_replace("\t", '', $_SERVER['SSL_CLIENT_CERT']);
        $cert = $x509->loadX509($asciicert);
        $cnarray = \Metaclassing\Utility::recursiveArrayTypeValueSearch($x509->getDN(), 'id-at-commonName');
        $cn = reset($cnarray);
        if (! $cn) {
            throw new \Exception('Authentication failure, could not extract CN from TLS client certificate');
        }

        // Get UPN
        $extensions = $cert['tbsCertificate']['extensions'];

        foreach ($extensions as $extension) {
            if ($extension['extnId'] == 'id-ce-subjectAltName') {
                $ext = $extension['extnValue'];
                foreach ($ext as $i) {
                    //print_r($i['otherName']);
                    if ($i['otherName']['type-id'] == '1.3.6.1.4.1.311.20.2.3') {
                        // reset returns the first value of the array without specifying the key
                        $upn = reset($i['otherName']['value']);
                        //print_r($upn);
                    }
                }
            }
        }

        // Get DN
        $dnparts = $x509->getDN();

        //print_r($dnparts);
        $parts = [];
        foreach ($dnparts['rdnSequence'] as $part) {
            $part = reset($part);
            $type = $part['type'];
            $value = reset($part['value']);
            switch ($type) {
                case 'id-domainComponent':
                    $parts[] = 'DC='.$value;
                    break;
                case 'id-at-organizationalUnitName':
                    $parts[] = 'OU='.$value;
                    break;
                case 'id-at-commonName':
                    $parts[] = 'CN='.$value;
                    break;
            }
        }
        $dnstring = implode(',', array_reverse($parts));

        // TODO write some checking to make sure the cert DN matches the user DN in AD

        return [
                'username'             => $cn,
                'dn'                   => $dnstring,
                'userprincipalname'    => $upn,
                ];
    }

    protected function ldapauth(Request $request)
    {
        if (! $request->has('username') || ! $request->has('password')) {
            throw new \Exception('Missing username or password');
        }
        $username = $request->input('username');
        $password = $request->input('password');
        //print "Auth testing for {$username} / {$password}\n";
        $this->ldapinit();
        if (! $this->ldap->authenticate($username, $password)) {
            throw new \Exception('LDAP authentication failure');
        }
        // get the username and DN and return them in the data array
        $ldapuser = $this->ldap->user()->info($username, ['*'])[0];

        return [
                'username'          => $ldapuser['cn'][0],
                'dn'                => $ldapuser['dn'],
                //'samaccountname'    => $ldapuser['samaccountname'][0],
                'userprincipalname' => $ldapuser['userprincipalname'][0],
                ];
    }

    // This is called when any good authentication path succeeds, and creates a user in our table if they have not been seen before
    protected function goodauth(array $data)
    {
        // If a user does NOT exist, create them
        if (User::where('dn', '=', $data['dn'])->exists()) {
            $user = User::where('dn', '=', $data['dn'])->first();
            /* Deprecated samaccountname usage.
            if ($user->samaccountname == null) {
                $user->samaccountname = $data['samaccountname'];
                $user->save();
            }*/
            if ($user->userprincipalname == null) {
                $user->userprincipalname = $data['userprincipalname'];
                $user->save();
            }
        } else {
            $user = $this->create($data);
        }

        // IF we are using LDAP, place them into LDAP groups as Bouncer roles
        if (env('LDAP_AUTH')) {
            $userldapinfo = $this->getLdapUserByName($user->username);
            if (isset($userldapinfo['memberof'])) {
                // remove the users existing database roles before assigning new ones
                $userroles = $user->roles()->get();
                foreach ($userroles as $role) {
                    $user->retract($role);
                }
                $groups = $userldapinfo['memberof'];
                unset($groups['count']);
                // now go through groups and assign them as new roles.
                foreach ($groups as $group) {
                    // Do i need to do any other validation here? Make sure group name is CN=...?
                    $user->assign($group);
                }
            } else {
                /* Old samaccountname name lookup - deprecating
                $userldapinfo = $this->getLdapUserByName($user->samaccountname);
                if (isset($userldapinfo['memberof'])) {
                    // remove the users existing database roles before assigning new ones
                    $userroles = $user->roles()->get();
                    foreach ($userroles as $role) {
                        $user->retract($role);
                    }
                    $groups = $userldapinfo['memberof'];
                    unset($groups['count']);
                    // now go through groups and assign them as new roles.
                    foreach ($groups as $group) {
                        // Do i need to do any other validation here? Make sure group name is CN=...?
                        $user->assign($group);
                    }
                }
                */
                // Use userprincipalname going forward
                $userldapinfo = $this->getLdapUserByName($user->userprincipalname);
                if (isset($userldapinfo['memberof'])) {
                    // remove the users existing database roles before assigning new ones
                    $userroles = $user->roles()->get();
                    foreach ($userroles as $role) {
                        $user->retract($role);
                    }
                    $groups = $userldapinfo['memberof'];
                    unset($groups['count']);
                    // now go through groups and assign them as new roles.
                    foreach ($groups as $group) {
                        // Do i need to do any other validation here? Make sure group name is CN=...?
                        $user->assign($group);
                    }
                }
            }
        }

        // We maintain a user table for permissions building and group lookup, NOT authentication and credentials
        $credentials = ['dn' => $data['dn'], 'password' => ''];
        try {
            // This should NEVER fail.
            if (! $token = JWTAuth::attempt($credentials)) {
                abort(401, 'JWT Authentication failure');
            }
        } catch (JWTException $e) {
            return response()->json(['error' => 'could_not_create_token'], 500);
        }

        // Log successfull Login by User
        activity('authlog')->causedBy($user)->withProperties(['username' => $user->username])->log('Authenticated');

        return response()->json(compact('token'));
    }

    // dump all the known users in our table out
    public function listusers()
    {
        $users = User::all();

        return $users;
    }

    protected function ldapinit()
    {
        if (! $this->ldap) {
            // Load the ldap library that pre-dates autoloaders
            require_once base_path().'/vendor/adldap/adldap/src/adLDAP.php';
            try {
                $this->ldap = new \adLDAP\adLDAP([
                                                    'base_dn'            => env('LDAP_BASEDN'),
                                                    'admin_username'     => env('LDAP_USER'),
                                                    'admin_password'     => env('LDAP_PASS'),
                                                    'domain_controllers' => [env('LDAP_HOST')],
                                                    'ad_port'            => env('LDAP_PORT'),
                                                    'account_suffix'     => '@'.env('LDAP_DOMAIN'),
                                                ]);
            } catch (\Exception $e) {
                abort("Exception: {$e->getMessage()}");
            }
        }
    }

    public function changeLdapPhone($username, $phonenumber)
    {
        if (! $this->ldap) {
            $this->ldapinit();
        }
        $user = $this->ldap->user()->info($username, ['*']);
        if ($user[0]['dn'] == null) {
            throw new \Exception('Error getting DN for username '.$username);
        }
        $user_dn = $user[0]['dn'];
        //print_r($user_dn);
        $user_ipphone = '';
        if (isset($user[0]['ipphone'][0])) {
            $user_ipphone = $user[0]['ipphone'][0];
        }

        $ldapshost = 'ldaps:/'.'/'.env('LDAP_HOST');
        $ad = ldap_connect($ldapshost);
        if (! $ad) {
            throw new \Exception('Could not reconnect to AD for modifications');
        }
        ldap_set_option($ad, LDAP_OPT_PROTOCOL_VERSION, 3);
        $bd = ldap_bind($ad, env('LDAP_USER'), env('LDAP_PASS'));
        if (! $bd) {
            throw new \Exception('Error rebinding to AD as user '.env('LDAP_USER'));
        }
        // set the users new phone number
        $change['ipPhone'] = $phonenumber;
        // execute the LDAP modify query
        $result = ldap_mod_replace($ad, $user_dn, $change);
        if (! $result) {
            throw new \Exception('Error modifying AD attribute for DN '.$user_dn);
        }
        ldap_unbind($ad);

        return [
                    'user'    => $user_dn,
                    'ipphone' => [
                                    'old' => $user_ipphone,
                                    'new' => $phonenumber,
                                ],
                    ];
    }

    public function getUserLdapPhone($username)
    {
        if (! $this->ldap) {
            $this->ldapinit();
        }
        $user = $this->ldap->user()->info($username, ['*']);
        if ($user[0]['dn'] == null) {
            return [
                'user'     		=> '',
                'ipphone'  		=> '',
				'displayname' 	=> '',
				'firstname' 	=> '',
				'lastname' 	=> '',
                ];
            //throw new \Exception('Error getting DN for username '.$username);
        }
        $user_dn = $user[0]['dn'];
        //print_r($user_dn);
        $user_ipphone = '';
        if (isset($user[0]['ipphone'][0])) {
            $user_ipphone = $user[0]['ipphone'][0];
        }
		
		if(isset($user[0]['displayname']) && $user[0]['displayname'][0]){
			$displayname = $user[0]['displayname'][0];
			$name = explode(".", $displayname);
			$firstname = $name[0];
			$lastname = $name[1];
		}

        return [
                    'user'     		=> $user_dn,
                    'ipphone'  		=> $user_ipphone,
					'displayname'	=> 	$name,
					'firstname'		=> 	$firstname,
					'lastname'		=> 	$lastname,
                    ];
    }

    public function getLdapUserByName($username)
    {
        $this->ldapinit();
        // Search for the LDAP user by his username we copied from the certificates CN= field
        $ldapuser = $this->ldap->user()->info($username, ['*'])[0];
        // If they have unencoded certificate crap in the LDAP response, this will dick up JSON encoding
        if (isset($ldapuser['usercertificate']) && is_array($ldapuser['usercertificate'])) {
            //			unset($ldapuser["usercertificate"]);/**/
            foreach ($ldapuser['usercertificate'] as $key => $value) {
                if (\Metaclassing\Utility::isBinary($value)) {
                    $asciicert = "-----BEGIN CERTIFICATE-----\n".
                                 chunk_split(base64_encode($value), 64).
                                 "-----END CERTIFICATE-----\n";
                    $x509 = new \phpseclib\File\X509();
                    $cert = $x509->loadX509($asciicert);
                    $cn = \Metaclassing\Utility::recursiveArrayFindKeyValue(
                                \Metaclassing\Utility::recursiveArrayTypeValueSearch(
                                    $x509->getDN(),
                                    'id-at-commonName'
                                ), 'printableString'
                            );
                    $issuer = \Metaclassing\Utility::recursiveArrayFindKeyValue(
                                    \Metaclassing\Utility::recursiveArrayTypeValueSearch(
                                        $x509->getIssuerDN(),
                                        'id-at-commonName'
                                    ), 'printableString'
                                );
                    $ldapuser['usercertificate'][$key] = "Bag Attributes\n"
                                                       ."\tcn=".$cn."\n"
                                                       ."\tserial=".$cert['tbsCertificate']['serialNumber']->toString()."\n"
                                                       ."\tissuer=".$issuer."\n"
                                                       ."\tissued=".$cert['tbsCertificate']['validity']['notBefore']['utcTime']."\n"
                                                       ."\texpires=".$cert['tbsCertificate']['validity']['notAfter']['utcTime']."\n"
                                                       .$asciicert;
                }
            }/**/
        }
        // Handle any other crappy binary encoding in the response
        $ldapuser = \Metaclassing\Utility::recursiveArrayBinaryValuesToBase64($ldapuser);
        // Handle any remaining UTF8 encoded garbage before returning the user, this causes silent json_encode failures
        //$ldapuser = \Metaclassing\Utility::encodeArrayUTF8($ldapuser);
        return $ldapuser;
    }

    public function check_if_app_user($user)
    {
        // Adding Telecom Group Checks.
        $app_groups = [env('ADMIN_GRP'), env('READ_UPDATE_GRP'), env('READ_ONLY_GRP')];

        if ((isset($user['memberof'])) && $user['memberof']) {
            foreach ($user['memberof'] as $group) {
                if (in_array($group, $app_groups)) {
                    return true;
                }
            }
        }
    }

    public function userinfo()
    {
        $user = JWTAuth::parseToken()->authenticate();
        //print_r($user);
        if (env('LDAP_AUTH')) {
            $userinfo = $this->getLdapUserByName($user->username);
        }

        /* For legacy...
        if (! $userinfo) {
            $userinfo = $this->getLdapUserByName($user->samaccountname);
        }
        */

        // Use User Pricipal Name instead of samaccountname
        if (! $userinfo) {
            $userinfo = $this->getLdapUserByName($user->userprincipalname);
        }

        return response()->json($userinfo);
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param array $data
     *
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'username' => 'required|max:255',
            'dn'       => 'required|max:255|unique:users',
            'password' => 'required|min:0',
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param array $data
     *
     * @return User
     */
    protected function create(array $data)
    {
        activity('usercreate')->withProperties(['username' => $data['username'], 'dn' => $data['dn'], 'userprincipalname'  => $data['userprincipalname']])->log('Creating User');
        // Again, users we track are for LDAP linkage, NOT authentication.
        return User::create([
            'username'           => $data['username'],
            'dn'                 => $data['dn'],
            //'samaccountname'     => $data['samaccountname'],
            'userprincipalname'  => $data['userprincipalname'],
            'password'           => bcrypt(''),
        ]);
    }
}
