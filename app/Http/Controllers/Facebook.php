<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

require_once __DIR__ . '/../../../vendor/autoload.php';
session_start();

class Facebook extends Controller {
	const APP_ID = '148129495771480';
	const APP_SECRET = '9295dd05cfaf1a2ae400c0644f8a752f';
	const APP_VERSION = 'v2.2';

	protected $fb_app_data = [
		'app_id' => self::APP_ID,
		'app_secret' => self::APP_SECRET,
		'default_graph_version' => self::APP_VERSION,
		'persistent_data_handler'=>'session'
	];
	protected $fb_callback = "http://kyu.plusmedia.nz/fb-redirect";
	protected $fb_access_token;

	public function index() {
	}

	public function login() {
		$fb = new \Facebook\Facebook($this->fb_app_data);
		$helper = $fb->getRedirectLoginHelper();

		$permissions = [
			'email',
			'user_location',
			'user_birthday',
			'publish_actions',
			'public_profile',
		];
		$loginUrl = $helper->getLoginUrl($this->fb_callback, $permissions);
		$assign['loginUrl'] = $loginUrl;
		return view('login',$assign);
	}

	public function fb_callback() {
		$fb = new \Facebook\Facebook($this->fb_app_data);

		$helper = $fb->getRedirectLoginHelper();
		$_SESSION['FBRLH_state']=$_GET['state'];

		try {
			$accessToken = $helper->getAccessToken();
		} catch(\Facebook\Exceptions\FacebookResponseException $e) {
			// When Graph returns an error
			echo 'Graph returned an error: ' . $e->getMessage();
			exit;
		} catch(\Facebook\Exceptions\FacebookSDKException $e) {
			// When validation fails or other local issues
			echo 'Facebook SDK returned an error: ' . $e->getMessage();
			exit;
		}

		if (! isset($accessToken)) {
			if ($helper->getError()) {
			header('HTTP/1.0 401 Unauthorized');
			echo "Error: " . $helper->getError() . "\n";
			echo "Error Code: " . $helper->getErrorCode() . "\n";
			echo "Error Reason: " . $helper->getErrorReason() . "\n";
			echo "Error Description: " . $helper->getErrorDescription() . "\n";
			} else {
			header('HTTP/1.0 400 Bad Request');
			echo 'Bad request';
			}
			exit;
		}

		// Logged in

		// The OAuth 2.0 client handler helps us manage access tokens
		$oAuth2Client = $fb->getOAuth2Client();

		// Get the access token metadata from /debug_token
		$tokenMetadata = $oAuth2Client->debugToken($accessToken);

		// Validation (these will throw FacebookSDKException's when they fail)
		$tokenMetadata->validateAppId(self::APP_ID); // Replace {app-id} with your app id
		// If you know the user ID this access token belongs to, you can validate it here
		//$tokenMetadata->validateUserId('123');
		$tokenMetadata->validateExpiration();

		if (! $accessToken->isLongLived()) {
			// Exchanges a short-lived access token for a long-lived one
			try {
				$accessToken = $oAuth2Client->getLongLivedAccessToken($accessToken);
			} catch (\Facebook\Exceptions\FacebookSDKException $e) {
				echo "<p>Error getting long-lived access token: " . $helper->getMessage() . "</p>\n\n";
				exit;
			}

			echo '<h3>Long-lived</h3>';
			var_dump($accessToken->getValue());
		}

		$_SESSION['fb_access_token'] = (string) $accessToken;
		// User is logged in with a long-lived access token.
		// You can redirect them to a members-only page.
		return redirect('dashboard');
	}

	public function dashboard()	{
		////////////////	get FB user	////////////////////////////
		if(!isset($_SESSION['fb_access_token'])) {
			return redirect('/');
		} else {
			$this->fb_access_token = $_SESSION['fb_access_token'];
		}

		$fb = new \Facebook\Facebook($this->fb_app_data);

		try {
			$response = $fb->get('/me?fields=id,name,email,birthday', $this->fb_access_token);
		} catch(\Facebook\Exceptions\FacebookResponseException $e) {
			echo 'Graph returned an error: ' . $e->getMessage();
			exit;
		} catch(\Facebook\Exceptions\FacebookSDKException $e) {
			echo 'Facebook SDK returned an error: ' . $e->getMessage();
			exit;
		}

		$user = $response->getDecodedBody();
		$assign['user'] = $user;

		////////////////	Call the posted result	////////////////////////////
		$params = [$user['email']];
		$result = DB::select("SELECT * FROM fb_post WHERE user_email=? ORDER BY issued DESC",$params);
		$result = json_decode(json_encode($result), true);

		$result = $this->jsonDecode($result);
		$assign['posts'] = $result;
		return view('dashboard',$assign);
	}
	public function fb_post(Request $request) {
		if(!isset($_SESSION['fb_access_token'])) {
			return redirect('/');
		} else {
			$this->fb_access_token = $_SESSION['fb_access_token'];
		}

		$validator = Validator::make($request->all(), [
			"message"=>'required',
		]);

		if($validator->fails()) {
			return redirect('dashboard')
				->withInput()
				->withErrors($validator);
		}

		$fb = new \Facebook\Facebook($this->fb_app_data);

		$data = [
			"message"=>$request->message,
			"privacy"=>[
				'value'=>'SELF'
			]
		];

		try {
			$response = $fb->post('/me/feed', $data, $this->fb_access_token);
		} catch(\Facebook\Exceptions\FacebookResponseException $e) {
			echo 'Graph returned an error: ' . $e->getMessage();
			exit;
		} catch(\Facebook\Exceptions\FacebookSDKException $e) {
			echo 'Facebook SDK returned an error: ' . $e->getMessage();
			exit;
		}
		$result = [
			"status"=>$response->getHttpStatusCode(),
			"header"=>$response->getHeaders(),
			"body"=>$response->getDecodedBody(),
			'message'=>$request->message
		];
		$graphNode = $response->getGraphNode();

		try {
			$response = $fb->get('/me?fields=id,name,email,birthday', $this->fb_access_token);
		} catch(\Facebook\Exceptions\FacebookResponseException $e) {
			echo 'Graph returned an error: ' . $e->getMessage();
			exit;
		} catch(\Facebook\Exceptions\FacebookSDKException $e) {
			echo 'Facebook SDK returned an error: ' . $e->getMessage();
			exit;
		}

		$user = $response->getGraphUser();
		$assign['user'] = $user;
		$assign["fb_post_id"] = $graphNode['id'];

		$params = [$graphNode['id'],$user['email'],json_encode($result)];
		DB::insert("INSERT INTO fb_post SET post_id=?, user_email=?, response=?", $params);

		return redirect('dashboard');
	}
	function jsonDecode($array) {
		foreach($array as $key=>$value) {
			if(is_array($value)) {
				$value = $this->jsonDecode($value);
				$array[$key] = $value;
				continue;
			}
			if(is_array(json_decode($value,true))) {
				$array[$key] = json_decode($value,true);
			}
		}
		return $array;
	}
 }
