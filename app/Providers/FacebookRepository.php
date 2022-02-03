<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

use Illuminate\Support\Facades\Log;
use Facebook\Facebook;

class FacebookRepository extends ServiceProvider
{

    protected $facebook;



    
    public function __construct()
    {
        Log::info(' we are in facebook repository construct');
        $this->facebook = new Facebook([
            'app_id' => config('services.facebook.client_id'),
            'app_secret' => config('services.facebook.client_secret'),
            'grant_type'=>"client_credentials",
            'default_graph_version' => 'v12.0'
        ]);
    }

    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        
        
    }



    public function redirectTo()
    {
        $helper = $this->facebook->getRedirectLoginHelper();

        $permissions = [
            'pages_manage_posts',
            'pages_read_engagement'
        ];


        //use var from  config('app.url')
        $redirectUri = "https://kreatinc.3ajilpress.com/". '/auth/facebook/callback';
        
        return $helper->getLoginUrl($redirectUri, $permissions);
    }


     
    // this function recepured data from API FB 
    //   the token and inf current user
      


    
    public function handleCallback()
    {
        $helper = $this->facebook->getRedirectLoginHelper();


        
        if (request('state')) {
            $helper->getPersistentDataHandler()->set('state', request('state'));
        }

        try {
            $accessToken = $helper->getAccessToken();
        } catch(FacebookResponseException $e) {
          //  throw new Exception("Graph returned an error: {$e->getMessage()}");
        } catch(FacebookSDKException $e) {
           // throw new Exception("Facebook SDK returned an error: {$e->getMessage()}");
        }

        if (!isset($accessToken)) {
            //throw new Exception('Access token error');
        }

        if (!$accessToken->isLongLived()) {
            try {
                $oAuth2Client = $this->facebook->getOAuth2Client();
                $accessToken = $oAuth2Client->getLongLivedAccessToken($accessToken);
            } catch (FacebookSDKException $e) {
              //  throw new Exception("Error getting a long-lived access token: {$e->getMessage()}");
            }
        }

       ;
       
       
       
       
        $this->facebook->setDefaultAccessToken($accessToken->getValue());
        $profileRequest = $this->facebook->get('/me?fields=name,first_name,last_name,email,link,gender,locale,cover,picture');
        $fbUserProfile = $profileRequest->getGraphNode()->asArray();
        
        //echo("\n ---->".$fbUserProfile['first_name']);
        //echo("\n ---->".$fbUserProfile['last_name']);
        //echo("\n ---->".$fbUserProfile['email']);
        //echo("\n ---->".$fbUserProfile['id']);
        //var_dump($fbUserProfile);
        
         
        
        
          $data=[
            "user_Fname"=>$fbUserProfile['first_name'],
            "user_Lname"=>$fbUserProfile['last_name'],
            "user_id"=>$fbUserProfile['id'],
            "user_email"=>(isset($fbUserProfile['email']))? $fbUserProfile['email']:$fbUserProfile['first_name']."@".$fbUserProfile['last_name'],
            "user_accessToken"=>$accessToken->getValue(),
            "user_picture"=>$fbUserProfile['picture']['url']
        ];

       
        return $data;
        
 

    }


    
    public function getPages($accessToken)
    {
        $pages = $this->facebook->get('/me/accounts', $accessToken);
        $pages = $pages->getGraphEdge()->asArray();

        return array_map(function ($item) {
            return [
                'access_token' => $item['access_token'],
                'id' => $item['id'],
                'name' => $item['name'],
                'image' => "https://graph.facebook.com/{$item['id']}/picture?type=large"
            ];
        }, $pages);
    }


    
    
    private function postData($accessToken, $endpoint, $data)
    {
        try {
            $response = $this->facebook->post(
                $endpoint,
                $data,
                $accessToken
            );
            return $response->getGraphNode();

        } catch (FacebookResponseException $e) {
            throw new Exception($e->getMessage(), $e->getCode());
        } catch (FacebookSDKException $e) {
            throw new Exception($e->getMessage(), $e->getCode());
        }
    }  
    
    
     public function post($accountId, $accessToken, $content, $images = [])
    {
        $data = $content;
    
        // i check if exist file
        if($images!=[])
        {
            $attachments = $this->uploadImages($accountId, $accessToken, $images);
        
            foreach ($attachments as $i => $attachment) {
                $data["attached_media[$i]"] = "{\"media_fbid\":\"$attachment\"}";
            }
        }
        
        try {
            
            return $this->postData($accessToken, "$accountId/feed", $data);
            
        } catch (\Exception $exception) {
            //notify user about error
            return false;
        }
    }
    
    
    private function uploadImages($accountId, $accessToken, $images = [])
    {
        $attachments = [];
        
        //this code dor singl file
        
        $data = [
                'source' => $this->facebook->fileToUpload($images),
            ];
        
         try {
                $response = $this->postData($accessToken, "$accountId/photos?published=false", $data);
                if ($response) $attachments[] = $response['id'];
            } catch (\Exception $exception) {
                throw new Exception("Error while posting: {$exception->getMessage()}", $exception->getCode());
            }
        return $attachments;




        /// this code for multipl Files
        foreach ($images as $image) {
            if (!file_exists($image)) continue;


            var_dump($image);
            
            $data = [
                'source' => $this->facebook->fileToUpload($image),
            ];

            try {
                $response = $this->postData($accessToken, "$accountId/photos?published=false", $data);
                if ($response) $attachments[] = $response['id'];
            } catch (\Exception $exception) {
                throw new Exception("Error while posting: {$exception->getMessage()}", $exception->getCode());
            }
        }

        return $attachments;
    }
    
    
    public function getPostByPageId($accessToken,$pageId,$tokenPage)
    {

        $data=[];
        //$pageId="109802077585380";
        //109802077585380/posts
        try {
            $response =$this->facebook->get('/'.$pageId."/posts?fields=message,story,full_picture,is_published,scheduled_publish_time,created_time", $accessToken);
            
            
            $responseSchedule =$this->facebook->get('/'.$pageId."/scheduled_posts?fields=message,story,full_picture,is_published,scheduled_publish_time,created_time", $tokenPage);
            
            //return $responseSchedule->getGraphEdge()->asArray();
            $response=$response->getGraphEdge()->asArray();
            if(isset($responseSchedule))
                return array_merge($response,$responseSchedule->getGraphEdge()->asArray());
            
            return $response;

        } catch (FacebookResponseException $e) {
            // throw new Exception($e->getMessage(), $e->getCode());
        } catch (FacebookSDKException $e) {
            //    throw new Exception($e->getMessage(), $e->getCode());
        }

    }
}
