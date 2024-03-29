<?php

use Illuminate\Support\Facades\Redirect;

class MemberController extends BaseController {

	public function index()
	{
		if (Session::get('current_location')){
			$current_location = Session::get('current_location');
			$clocation = Location::where('LocationID', '=', Session::get('current_location'))->first();
			if(count($clocation) == 1){
				$currentlocation = $clocation->LocationTitle;
			}else{
				$currentlocation = 'Location';
			}
		}else{
			$current_location = 1;
			$currentlocation = 'Location';
		}

		$users = User::select('name', 'twitter_handle', 'klout_metric_score', 'image')
				->orderBy('klout_metric_score', 'desc')
				->where('location_id', '=', $current_location)
				->where('type_id', '=', 1)
				->take(10)
				->get();

		$lists = Lists::where('featured', '=', 1)->get();
			
		$users_week_gain = User::select('name', 'twitter_handle', 'klout_metric_score', 'image', 'klout_metric_score_week')
			->where('klout_metric_score_week', '>', 0)
			->where('location_id', '=', $current_location)
			->where('type_id', '=', 1)
			->orderBy('klout_metric_score_week', 'desc')
			->take(5)
			->get();


		$users_week_loss = User::select('name', 'twitter_handle', 'klout_metric_score', 'image', 'klout_metric_score_week')
			->where('klout_metric_score_week', '<', 0)
			->where('location_id', '=', $current_location)
			->where('type_id', '=', 1)
			->orderBy('klout_metric_score_week', 'asc')
			->take(5)
			->get();
		
		$location_list = Location::where('LocationActive', '=', 1)
						->orderBy('LocationID','asc')
						->get();
		
		return View::make('member.index',array('users_week_gain'=>$users_week_gain, 'users_week_loss' => $users_week_loss,'location_list' => $location_list, 'currentlocation' => $currentlocation))->withUsers($users)->withLists($lists);
	}
	
	public function userList(){
		if (Session::get('current_location')){
			$current_location = Session::get('current_location');
		}else{
			$current_location = 1;
		}
		$users = User::select('name', 'twitter_handle', 'klout_metric_score', 'image')
		->orderBy('klout_metric_score', 'desc')
		->where('location_id', '=', $current_location)
		->where('type_id', '=', 1)
		->paginate(10);
		return View::make('member.userlist', array('users'=>$users));
	}

	public function lists($slug)
	{
		if (Session::get('current_location')){
			$current_location = Session::get('current_location');
		}else{
			$current_location = 1;
		}
		$type = Lists::where('slug', '=', $slug)->with('users')->first();
		$users = $type->users()->orderBy('klout_metric_score', 'desc')
				->where('location_id', '=', $current_location)
				->paginate(50);

		return View::make('member.type', array('type'=>$type, 'users'=>$users));
	}
	
	public function customlists($twitter_handle, $slug)
	{
		if (Session::get('current_location')){
			$current_location = Session::get('current_location');
		}else{
			$current_location = 1;
		}
		$actor = User::where('twitter_handle', '=', $twitter_handle)->first();
		$type = Lists::where('slug', '=', $slug)
		->where('user_id', '=', $actor->id)
		->first();
		$users = $type->users()->orderBy('klout_metric_score', 'desc')
		->where('location_id', '=', $current_location)
		->paginate(50);
	
		return View::make('member.type', array('type'=>$type, 'users'=>$users));
	}

	public function user($twitter_handle)
	{
		$tags_id_array = array();
		$tags_count_array = array();
		$tags_count_total = 0;
		$user = User::where('twitter_handle', '=', $twitter_handle)->first();
		//list all tags for this user
		$tags = UserTag::select(DB::raw(' user_tag.user_id ,user_tag.tag_id , user_tag.actor_user_id , tag.* '))
		->join('tag','tag.id','=','user_tag.tag_id')
		->where('user_tag.user_id', '=', $user->id)
		->orderBy('user_tag.tag_id','desc')
		->get();

		if(count($tags) == 0){
			$tags_info = array();
			$tags_actor_id = array();
		}else{
			
			foreach ($tags as $tag_key => $tag_value) //merge the same item
			{
				$tags_info[$tag_value->tag_id] = $tag_value;
				$tags_actor_id[$tag_value->tag_id][$tag_value->actor_user_id] = $tag_value->actor_user_id;
			}
		}
		//user as list actor
		$list_actor = array();
		$list_actor = Lists::where('user_id', '=', $user->id)->get();
		
		//user listedby
		$listedby = array();
		$listedby_id = array();
		$listedby_rl = array();
		$listedby_rl = UserList::where('user_id', '=', $user->id)->get();
		foreach ($listedby_rl as $rl_value)
		{
			$listedby[] = Lists::select('users.*','list.*','list.user_id as actor_id','user_list.*')
			->where('list.id','=',$rl_value->list_id)
			->where('users.location_id','=','1')
			->join('user_list', 'list.id', '=', 'user_list.list_id')
			->join('users', 'users.id', '=', 'user_list.user_id')
			->orderBy('users.klout_metric_score','desc')
			->get();
		}
		
		foreach($listedby as $listedby_key => $listedby_value){
			foreach ($listedby_value as $listedby_k => $listedby_v){
				$listedby[$listedby_key][$listedby_k]['rank'] = intval($listedby_k)+1;
				if($listedby_v->user_id != $user->id){
					unset ($listedby[$listedby_key][$listedby_k]);
				}
			}
		}
		return View::make('member.user', array('user'=>$user, 'tags_info' => $tags_info, 'tags_actor_id' => $tags_actor_id,'list_actor' => $list_actor,'listedby' => $listedby));
	}
	public function userChangeLocation(){
		if(Input::has('current_location')){
			$location_id = Input::get('current_location');
			$location_id = (int)$location_id;
			if($location_id>0){
				Session::put('current_location',$location_id);
			}else{
				Session::put('current_location',1);
			}
		}
		return Redirect::to('/');
	}

	public function twitterSignIn()
	{
		$twitter_config = Config::get('twitter');
		$consumer_key = $twitter_config['consumer_key'];
		$consumer_secret = $twitter_config['consumer_secret'];
		$callback = url('twitter_callback');
		$connection = new TwitterOAuth($consumer_key, $consumer_secret);
		$request_token = $connection->getRequestToken($callback);
		
		$token = $request_token['oauth_token'];
		Session::put('oauth_token', $token);
		Session::put('oauth_token_secret', $request_token['oauth_token_secret']);
		
		switch ($connection->http_code) {
			case 200:
				$url = $connection->getAuthorizeURL($token);
				return Redirect::to($url);
				break;
			default:
				App::abort(500, 'Could not connect to Twitter.');
		}
	}

	public function twitterCallback()
	{
		if ((Input::get('oauth_token') && Session::get('oauth_token') !== Input::get('oauth_token')) || !Input::get('oauth_token')) {
			return Redirect::to('/');
		}
		
		$twitter_config = Config::get('twitter');
		$consumer_key = $twitter_config['consumer_key'];
		$consumer_secret = $twitter_config['consumer_secret'];
		
		$connection = new TwitterOAuth($consumer_key, $consumer_secret, Session::get('oauth_token'), Session::get('oauth_token_secret'));
		$access_token = $connection->getAccessToken(Input::get('oauth_verifier'));
		Session::put('access_token', $access_token);
		$user_info = $connection->get('account/verify_credentials');
		
		$location = $user_info->location;
		if(empty($location)){
			$user_info->location_id = 1;
		}else{
			$location = ucwords(trim($location));
			$location_list = Location::where('LocationTitle', '=', $location)->first();
			if(count($location_list) == 0){
				//add location if not exist
				$location = new Location();
				$location->LocationTitle = $user_info->location;
				$location->LocationText = '';
				$location->LocationLatitude = 0;
				$location->LocationLongitude = 0;
				$location->LocationRadius = 0;
				$location->LocationImage = '';
				$location->LocationActive = 1;
				$location->LocationScan = 0;
				$location->save();
				$location_id = $location->id;
				$user_info->location_id = $location_id;
			}else {
				$user_info->location_id = $location_list->LocationId; //get location id if location exist
			}
		}
		if (isset($user_info->error)) {
			return Redirect::to('/');
	    } else {
			$user = User::where('twitter_id', '=', $user_info->id)->first();
			if(empty($user)){
				$user = new User();
				$user->name = $user_info->name;
				$user->twitter_id = $user_info->id;
				$user->twitter_handle = $user_info->screen_name;
				$user->text = $user_info->description;
				$user->location = $user_info->location;
				$user->location_id = $user_info->location_id;		//@todo
				$user->type_id = 1;		//@todo
				$user->image = $user_info->profile_image_url;
				$user->url = $user_info->url;
				$user->twitter_metric_followers = $user_info->followers_count;
				$user->twitter_metric_friends = $user_info->friends_count;
				$user->twitter_date_created = date("Y-m-d H:i:s",strtotime($user_info->created_at));
				$user->timezone = $user_info->time_zone;
				
			}
			$user->twitter_oauth_token = Session::get('oauth_token');
			$user->twitter_oauth_secret = Session::get('oauth_token_secret');
			$user->twitter_logged = date('Y-m-d H:i:s');
			$klout = new KloutAPIv2($twitter_config['klout_key']);
			$kloutID = $klout->KloutIDLookupByName("twitter", $user->twitter_handle);
			
			if (!empty($kloutID)) {
				$curlResult = $klout->KloutUserScore($kloutID);
				$resultString = json_decode($curlResult);
			
				if (isset($resultString->score)) {
					$user->klout_id = $kloutID;
					$user->klout_metric_score = $resultString->score;
					$user->klout_metric_score_day = $resultString->scoreDelta->dayChange;
					$user->klout_metric_score_week = $resultString->scoreDelta->weekChange;
					$user->klout_metric_score_month = $resultString->scoreDelta->monthChange;
					$user->klout_updated = date('Y-m-d H:i:s');
				}
			}
				
			$user->save();
				Session::put('id',$user->id);
				Session::put('twitter_handle',$user_info->screen_name);
				Session::put('current_location', $user_info->location_id);
				Session::put('type_id',1);
			Auth::login($user);
					
			return Redirect::to('/dashboard');
	    }
	}

	public function logout()
	{
		Auth::logout();
		Session::forget('id');
		Session::forget('twitter_handle');
		Session::forget('location_id');
		Session::forget('type_id');
		return Redirect::to('/');
	}
}