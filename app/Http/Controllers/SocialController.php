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
        $this->facebook = new FacebookRepository();
    }


    // redirect to api fb for get token access
    public function redirectToProvider()
    {
        return redirect($this->facebook->redirectTo());
    }


    // recovry  token access from fb and set auth session
    public function handleProviderCallback()
    {
        
        if (request('error') == 'access_denied')

            Log::error("we have error access_denied ");


            $data= $this->facebook->handleCallback();

            Log::info("we have acceess ");
         

         



            // set Authentification current user data
            $user=new User();
            $user->name=            $data['user_Fname']." ". $data['user_Lname'];
            $user->email=           $data['user_email'];
            $user->password=        Hash::make($data['user_id']);
            $user->token=           $data['user_accessToken'];
            $user->facebook_app_id= $data['user_id'];
            $user->picture=         $data['user_picture'];



            // if user exist set auth else created (remember user) save data in database
           $credentials = ["email"=>$user->email,"password"=>$data['user_id']];

           if (Auth::attempt($credentials)) {}
           else
            Auth::login($user,true);
            // after login go to page home and show liste pages
            $this->goToHomePage();

    }
    
    
    
    // this function show all posts by page post published and not published 'schedule'
    public function goToPostIndex($id,$tokenPage)
    {
        $token=Auth::user()->token;
        // get data by id page
        $data=$this->facebook->getPostByPageId($token,$id,$tokenPage);
        
        // prepare data
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

        return View("post",['posts'=>$collection,"idpage"=>$id,"tokenPage"=>$tokenPage]);

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
    

    // publish post
    public function savePost(Request $request)
    {
        // recovry data from requet
        // attention validation data

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


        $content    =$data;

        $this->facebook->post($accountId,$tokenPage,$content,$images);


        // after publish post retourn to lists post
        return back();
    }
}
