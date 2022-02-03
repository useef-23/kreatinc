<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

//use Facebook\Facebook;

use App\Providers\FacebookRepository;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\User;
use App\Page;
use App\Post;
use Illuminate\Support\Facades\Hash;

class SocialController extends Controller
{
    protected $facebook;

    public function __construct()
    {
        Log::info("we are in socialController construct");
        //$this->middleware('auth');
        $this->facebook = new FacebookRepository();
    }

    public function redirectToProvider()
    {
        return redirect($this->facebook->redirectTo());
    }

    public function handleProviderCallback()
    {
        
        if (request('error') == 'access_denied')
            //handle error
            Log::error("we have error access_denied ");

            //$accessToken = $this->facebook->handleCallback();
            $data= $this->facebook->handleCallback();
            //if(!$data)
            //
            Log::info("we have acceess ");
         
         //echo($accessToken);
         
         //var_dump($data);
         
        $user=new User();
        $user->name=            $data['user_Fname']." ". $data['user_Lname'];
        $user->email=           $data['user_email'];
        $user->password=        Hash::make($data['user_id']);
        $user->token=           $data['user_accessToken'];
        $user->facebook_app_id= $data['user_id'];
        $user->picture=         $data['user_picture'];


         //Auth::attempt($user);
         
         $credentials = ["email"=>$user->email,"password"=>$data['user_id']];//$request->only('email', 'password');

        if (Auth::attempt($credentials)) {}
        else
            Auth::login($user,true);
            
            
            
        
        $this->goToHomePage();
        
        //return redirect()->route('home');
        
        //Auth::login($user,true);
         
        //echo("we have cnx");
        
        //return view('home');
         
         
         
         
         
         
         
        $pages=$this->facebook->getPages($user->token);
        // var_dump($pages);
         
        // access_token 
        // id
        // name
        // image
        $collection=[];
        foreach ($pages as $item)
        {
            $page=new Page();
            $page->id=$item["id"];
            $page->access_token=$item["access_token"];
            $page->name=$item["name"];
            $page->image=$item["image"];
            
            array_push($collection,$page);
            
        }
            

    
        

        
        return view('home',['pages' => $collection]);
        //use token to get facebook pages
    }
    
    
    
    //,$token_page
    public function goToPostIndex($id,$tokenPage)
    {
        $token=Auth::user()->token;
        
        
        
        
        //$token="EAAG8F1SVE2kBAK3RlyJilyqhLprbZAqDuAFsrtAHfLZCRPWhIHz99LHLNCQ4pLIbtJ9ZBKW11z3ZAfZC2VOixYlKC5mrVWuMsnG9RGjuCBHP7lwpEaxIuYFoVFHaTGtXvvOsdccimQZCZBaGfop2muCZANZBpvuL2yFvtzzBRslus230ynYjstlVZBZBOTOW6DkzcWwLn8CASxPVwZDZD";
        $data=$this->facebook->getPostByPageId($token,$id,$tokenPage);
        
 
        
        
        
        
        
        
        
        $collection=[];
        foreach ($data as $item)
        {
            $post=new Post();
            $post->created_time=isset($item["created_time"])?$item["created_time"]:null;
            $post->id_page=$item["id"];
            
           
           
            $post->message=isset($item["message"])?$item["message"]:null;
            $post->story=isset($item['story'])?$item['story']:null;
            $post->full_picture=isset($item['full_picture'])?$item['full_picture']:null;
            
            $post->is_published=isset($item['is_published'])?$item['is_published']:null;
            $post->scheduled_publish_time=isset($item['scheduled_publish_time'])?$item['scheduled_publish_time']:null;
            
            if($post->message!=null)
                $post->type="message";
            else if($post->story!=null)
                $post->type="story";
            else if($post->full_picture!=null)
                $post->type="image";
            else    
                $post->type="video";
                
        
            array_push($collection,$post);
            
        }
        
        //return response()->json(["data"=>$collection]); 
        return View("post",['posts'=>$collection,"idpage"=>$id,"tokenPage"=>$tokenPage]);
        
      //  return response()->json(["data"=>$data]);
        //var_dump($data);
        return View("post");
    }
    
    
    
    public function goToHomePage()
    {
        $pages=$this->facebook->getPages(Auth::user()->token);
        
        $collection=[];
        foreach ($pages as $item)
        {
            $page=new Page();
            $page->id=$item["id"];
            $page->access_token=$item["access_token"];
            $page->name=$item["name"];
            $page->image=$item["image"];
            
            array_push($collection,$page);
            
        }
        
        //var_dump($pages);
        return view('home',['pages' => $collection]);
    }
    
      
    public function savePost(Request $request)
    {

        //  require validation data


        $isSchedule=$request->inlineCheckbox1;
        $description=$request->description;
        $date=$request->dateSchedule;
        $tokenPage=$request->tokenPage;
        $accountId  =$request->idpage;
        $images=(isset($request->fileUpload))?$request->fileUpload:[];

        $data=[];


        // check is scheduler post
        if($isSchedule)
            $data = ['message' => $description,
                     'published'=>false,
                     "scheduled_publish_time"=>$date];
        else
            $data = ['message' => $description];


        //send data
        //, , )
        /*
         * $accountId       ---> id page
         * $accessToken     ---> token page user ??
         * $content         ---> data you want to send
         */

        $content    =$data;

        $this->facebook->post($accountId,$tokenPage,$content,$images);
        
        return back();
        


    }
}
