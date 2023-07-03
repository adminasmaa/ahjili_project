<?php

use App\Models\UserCode;
use Twilio\Rest\Client;
use App\Mail\SendCodeMail;
use Illuminate\Support\Carbon;
use App\Models\PostImage;
use App\Models\MediaStory;
use App\Models\Post;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

/**
 * @param string $identifier
 * @param int $digits
 * @param int $validity
 * @return mixed
 */
//for sms code sending
function sendSMSCode($user)
{
    $code = rand(1000, 9999);
    UserCode::updateOrCreate([ 'user_id' => $user->id ], [ 'code' => $code ]);

    $message = "Your otp is " . $code;
    $receiverNumber = $user->country_code.''.$user->phone_number;
    $receiverNumber= $receiverNumber ?? "+923125115216";

    try {
        $account_sid = getenv("TWILIO_SID");
        $auth_token = getenv("TWILIO_AUTH_TOKEN");
        $twilio_number = getenv("TWILIO_NUMBER");

        $client = new Client($account_sid, $auth_token);
        $client->messages->create($receiverNumber, [
            'from' => $twilio_number,
            'body' => $message
        ]);

        return 1;
    } catch (Exception $e) {
        info("SMS Error: " . $e->getMessage());
    }
}

function sendemailOtpCode($user)
{
    //dd($user);
    $code = rand(1000, 9999);
    UserCode::updateOrCreate([ 'user_id' => $user->id ], [ 'code' => $code ]);
    try {
        $details = [
            'title' => 'OTP Code from ' . config('app.name'),
            'code' => $code
        ];
        Mail::to($user->email)->send(new SendCodeMail($details));
    } catch (Exception $e) {
        info("Error: " . $e->getMessage());
    }
}
//end

//for set field for login
function checkfieldName($field)
{
    $login_type = filter_var($field, FILTER_VALIDATE_EMAIL)
        ? 'email'
        : 'phone_number';

    request()->merge([
        $login_type => $field
    ]);

    return $login_type;
}

/* Check or create directory*/
function checkDirectory($path)
{
    $path = public_path($path);
    /* Check if the directory already exists. */
    if (!is_dir($path)) {
        /* Directory does not exist, so lets create it. */
        mkdir($path, 0755, true);
    }
}

function postS3Images($request)
{
    ini_set('memory_limit', '44M');
    $files = $request->file('images');
    $image_base_location = 'ahjili_app/posts/images';
    $pathArr=array();
    $file_thumb=array();
    if ($request->hasFile('images')) {
        foreach ($files as $file) {
            $documentPath = $file->store($image_base_location, 's3');
            $bucket_path = Storage::disk('s3')->url($documentPath);
            $pathArr[]=$bucket_path;
        }

        return ['path'=>$pathArr,'thumb_path'=> $file_thumb];
    }
    return ['path'=>$pathArr,'thumb_path'=> $file_thumb];
}

function postS3Video($request)
{
    ini_set('memory_limit', '44M');
    $vedio_base_location = 'ahjili_app/posts/vedios';
    $vedio_thumb_base_location = 'ahjili_app/posts/vedios/thumbnail';

    $pathArr=array();
    $file_thumb=array();
    if ($request->hasFile('video')) {
        $documentPath = $request->file('video')->store($vedio_base_location, 's3');
        $bucket_path = Storage::disk('s3')->url($documentPath);
        $pathArr[]=$bucket_path;
    }
    //for thumbnail upload
    if ($request->hasFile('video_thumbnail')) {
        $documentPath = $request->file('video_thumbnail')->store($vedio_thumb_base_location, 's3');
        $bucket_path_thumb = Storage::disk('s3')->url($documentPath);
        $file_thumb[]=$bucket_path_thumb;
    }
    //end
    return ['path'=>$pathArr,'thumb_path'=> $file_thumb];
}

function postS3Audio($request)
{
    ini_set('memory_limit', '44M');
    $audio_base_location = 'ahjili_app/posts/audio';

    $pathArr=array();
    $file_thumb=array();
    if ($request->hasFile('audio')) {
        $documentPath = $request->file('audio')->store($audio_base_location, 's3');
        $bucket_path = Storage::disk('s3')->url($documentPath);
        $pathArr[]=$bucket_path;
    }

    return ['path'=>$pathArr,'thumb_path'=> $file_thumb];
}

function postS3Gif($request)
{
    ini_set('memory_limit', '44M');
    $gif_base_location = 'ahjili_app/posts/gif';

    $pathArr=array();
    $file_thumb=array();
    if ($request->hasFile('gif')) {
        $documentPath = $request->file('gif')->store($gif_base_location, 's3');
        $bucket_path = Storage::disk('s3')->url($documentPath);
        $pathArr[]=$bucket_path;
    }

    return ['path'=>$pathArr,'thumb_path'=> $file_thumb];
}

function allUserPosts($user_id, $post_type)
{
    $posts = Post::query()
                    ->active()
                    ->where('user_id', $user_id)
                    ->whereIn('post_type', $post_type)
                    ->with(['getPostImages','comments','likers'])
                    ->orderBy('created_at', 'DESC')
                    ->paginate(15);
    return $posts;
}

//uper waly fun sab ok ha

function getUserName($id)
{
    $user_name = User::where('id', $id)->first();
    return $user_name['username'];
}


if (!function_exists('generateOtp')) {
    function generateOtp(string $identifier, string $login_type, int $digits = 4, int $validity = 10): object
    {
        Otp::where('identifier', $identifier)->where('valid', true)->delete();

        $token = str_pad(generatePin(), 4, '0', STR_PAD_LEFT);

        if ($digits == 5) {
            $token = str_pad(generatePin(5), 5, '0', STR_PAD_LEFT);
        }

        if ($digits == 6) {
            $token = str_pad(generatePin(6), 6, '0', STR_PAD_LEFT);
        }
        //$token = 000000;
        Otp::create([
               'identifier' => $identifier,
               'token' => $token ?? 123456,
               'validity' => $validity
        ]);


        return (object)[
            'status' => true,
            'token' => $token,
            'message' => 'OTP generated'
        ];
    }
}
/**
 * @param string $identifier
 * @param string $token
 * @return mixed
 */
if (!function_exists('validateOtp')) {
    function validateOtp(string $identifier, string $token): object
    {
        $otp = Otp::where('identifier', $identifier)->where('token', $token)->where('valid', 1)->first();

        if ($otp == null) {
            return (object)[
                'status' => false,
                'message' => 'OTP does not exist'
            ];
        } else {
            if ($otp->valid == true) {
                $carbon = new Carbon();
                $now = $carbon->now();
                $validity = $otp->created_at->addMinutes($otp->validity);

                if (strtotime($validity) < strtotime($now)) {
                    $otp->valid = false;
                    $otp->save();

                    return (object)[
                        'status' => false,
                        'message' => 'OTP Expired'
                    ];
                } else {
                    $otp->valid = false;
                    $otp->save();

                    return (object)[
                        'status' => true,
                        'message' => 'OTP is valid'
                    ];
                }
            } else {
                return (object)[
                    'status' => false,
                    'message' => 'OTP is not valid'
                ];
            }
        }
    }
}
/**
 * @param int $digits
 * @return string
 */
if (!function_exists('generatePin')) {
    function generatePin($digits = 4)
    {
        $i = 0;
        $pin = "";

        while ($i < $digits) {
            $pin .= mt_rand(0, 9);
            $i++;
        }

        return $pin;
    }
}


if (!function_exists('postImages')) {
    function postImages($request)
    {
        if (!$request->hasFile('images')) {
            return response()->json(['upload_file_not_found'], 400);
        }
        $image_id = generateImageId();

        $allowedfileExtension = ['jpeg', 'jpg', 'png', 'gif'];
        $files = $request->file('images');
        $errors = [];

        foreach ($files as $file) {
            $extension = $file->getClientOriginalExtension();

            $check = in_array($extension, $allowedfileExtension);

            if ($check) {
                foreach ($request->images as $mediaFiles) {
                    $uploadFolder = 'userPosts/' . $image_id;
                    checkDirectory($uploadFolder);
                    $path = $mediaFiles->store($uploadFolder, 'public');
                    $name = $mediaFiles->getClientOriginalName();

                    //store image file into directory and db
                    $save = new PostImage();
                    $save->title = $name;
                    $save->user_id = auth()->id();
                    $save->post_image_id = $image_id;
                    $save->path = $path;
                    $save->type = 'image';
                    $save->save();
                }
            } else {
                return ['file_uploaded' => false];
            }

            return ['file_uploaded' => true, 'image_id' => $image_id];
        }
    }
}

if (!function_exists('postVideo')) {
    function postVideo($request)
    {
        $image_id = generateImageId();
        $file = $request->file('video');

        $extension = $file->getClientOriginalExtension();

        $file_name = $image_id.'.'.$extension;
        $uploadFolder = 'userPosts/' . $image_id;
        checkDirectory($uploadFolder);
        $path = $request->file('video')->move($uploadFolder, $file_name);
        $name = $file->getClientOriginalName();

        //store image file into directory and db
        $save = new PostImage();
        $save->title = $file_name;
        $save->user_id = auth()->id();
        $save->post_image_id = $image_id;
        $save->path = $path;
        $save->type = 'video';
        $save->save();

        return ['file_uploaded' => true, 'image_id' => $image_id];
    }
}

if (!function_exists('postVideoo')) {
    function postVideoo($request)
    {
        $image_id = generateImageId();
        $file = $request->file('video');
        $file_thumb = $request->file('video_thumbnail');
        $extension = $file->getClientOriginalExtension();

        $file_name = $image_id.'.'.$extension;
        $uploadFolder = 'userPosts/' . $image_id;
        checkDirectory($uploadFolder);
        $path = $request->file('video')->move($uploadFolder, $file_name);
        $name = $file->getClientOriginalName();

        //for thumbnail upload
        $uploadFolder_thumb = 'userPosts_thumbnail/' . $image_id;
        checkDirectory($uploadFolder_thumb);
        $myimage = $image_id.'.'.$file_thumb->getClientOriginalExtension();
        $path_thumb=$file_thumb->move($uploadFolder_thumb, $myimage);
        //end
        //dd($path,$path_thumb);
        //store image file into directory and db
        $save = new PostImage();
        $save->title = $file_name;
        $save->user_id = auth()->id();
        $save->post_image_id = $image_id;
        $save->path = $path;
        $save->path_thumbnail = $path_thumb;
        $save->type = 'video';
        $save->save();

        return ['file_uploaded' => true, 'image_id' => $image_id];
    }
}

if (!function_exists('postAudio')) {
    function postAudio($request)
    {
        $image_id = generateImageId();
        $file = $request->file('audio');

        $extension = $file->getClientOriginalExtension();

        $file_name = $image_id.$extension;
        $uploadFolder = 'userPosts/' . $image_id;
        checkDirectory($uploadFolder);
        $path = $request->file('audio')->move($uploadFolder, $file_name);
        $name = $file->getClientOriginalName();

        //store image file into directory and db
        $save = new PostImage();
        $save->title = $name;
        $save->user_id = auth()->id();
        $save->post_image_id = $image_id;
        $save->path = $path;
        $save->type = 'audio';
        $save->save();

        return ['file_uploaded' => true, 'file_id' => $image_id];
    }
}

function generateImageId()
{
    $random = Str::random(40);
    // call the same function if the barcode exists already
    // if (ImageIdExists($random)) {
    //     return generateImageId();
    // }
    // otherwise, it's valid and can be used
    return $random;
}

function ImageIdExists($number)
{
    return PostImage::where('post_image_id', $number)->exists();
}


if (!function_exists('getUserAnonymousPosts')) {
    function getUserAnonymousPosts()
    {
        $posts = Post::where('status', 'active')->where('anonymous', '1')->with(['getPostImages','comments','likers'])->orderBy('created_at', 'DESC')->paginate(10);
        return $posts;
    }
}

if (!function_exists('getUserPostsCustom')) {
    function getUserPostsCustom($id, $types)
    {
        $posts = Post::where('user_id', $id)->where('status', 'active')->whereIn('type', $types)->with(['getPostImages','comments','likes'])->orderBy('created_at', 'DESC')->paginate(15);
        return $posts;
    }
}

if (!function_exists('getUserPostsCount')) {
    function getUserPostsCount($id)
    {
        $total = Post::query()->active()->where('user_id', $id)->count();
        return $total;
    }
}

if (!function_exists('getUserProfileImage')) {
    function getUserProfileImage($id)
    {
        $profile_image = User::find($id);
        if ($profile_image) {
            $profile_image = $profile_image['profile_image'] ? Storage::disk('public')->url($profile_image['profile_image']) : url('/')."/images/ahjili.png";
        }
        return $profile_image;
    }
}

if (!function_exists('storyImages')) {
    function storyImages($request)
    {
        if (!$request->hasFile('images')) {
            return response()->json(['upload_file_not_found'], 400);
        }
        $image_id = generateImageId();

        $allowedfileExtension = ['jpeg', 'jpg', 'png', 'gif'];
        $files = $request->file('images');
        $errors = [];

        foreach ($files as $file) {
            $extension = $file->getClientOriginalExtension();

            $check = in_array($extension, $allowedfileExtension);

            if ($check) {
                foreach ($request->images as $mediaFiles) {
                    $uploadFolder = 'userStory/' . $image_id;
                    checkDirectory($uploadFolder);
                    $path = $mediaFiles->store($uploadFolder, 'public');
                    $name = $mediaFiles->getClientOriginalName();

                    //store image file into directory and db
                    $save = new MediaStory();
                    $save->title = $name;
                    $save->user_id = auth()->id();
                    $save->story_media_id = $image_id;
                    $save->path = $path;
                    $save->type = 'image';
                    $save->save();
                }
            } else {
                return ['file_uploaded' => false];
            }

            return ['file_uploaded' => true, 'image_id' => $image_id];
        }
    }
}

if (!function_exists('storyS3Images')) {
    function storyS3Images($request)
    {
        ini_set('memory_limit', '44M');
        $image_id = generateImageId();
        $files = $request->file('images');
        // dd($files);
        foreach ($request->images as $mediaFiles) {
            $path = $mediaFiles->store('ahjili_app/stories/images', 's3');
            $bucket_path = Storage::disk('s3')->url($path);

            $name = $mediaFiles->getClientOriginalName();
            //store image file
            $save = new MediaStory();
            $save->title = $name ?? $image_id;
            $save->user_id = auth()->id();
            $save->story_media_id = $image_id;
            $save->path = $bucket_path;
            $save->type = 'image';
            $save->save();
        }
        return ['file_uploaded' => true, 'image_id' => $image_id];
    }
}


if (!function_exists('storyS3Video')) {
    function storyS3Video($request)
    {
        ini_set('memory_limit', '44M');
        $image_id = generateImageId();
        $file = $request->file('video');

        $path = $file->store('ahjili_app/stories/vedios', 's3');
        $bucket_path = Storage::disk('s3')->url($path);

        $name = $file->getClientOriginalName();

        //store image file into directory and db
        $save = new MediaStory();
        $save->title = $name ?? $image_id;
        $save->user_id = auth()->id();
        $save->story_media_id = $image_id;
        $save->path = $bucket_path;
        $save->type = 'video';
        $save->save();

        return ['file_uploaded' => true, 'image_id' => $image_id];
    }
}

if (!function_exists('storyVideo')) {
    function storyVideo($request)
    {
        $image_id = generateImageId();
        $file = $request->file('video');

        $extension = $file->getClientOriginalExtension();

        $file_name = $image_id.'.'.$extension;
        $uploadFolder = 'userStory/' . $image_id;
        checkDirectory($uploadFolder);
        $path = $request->file('video')->move($uploadFolder, $file_name);
        $name = $file->getClientOriginalName();

        //store image file into directory and db
        $save = new MediaStory();
        $save->title = $file_name;
        $save->user_id = auth()->id();
        $save->story_media_id = $image_id;
        $save->path = $path;
        $save->type = 'video';
        $save->save();

        return ['file_uploaded' => true, 'image_id' => $image_id];
    }
}

if (!function_exists('storyAudio')) {
    function storyAudio($request)
    {
        $image_id = generateImageId();
        $file = $request->file('audio');

        $extension = $file->getClientOriginalExtension();

        $file_name = $image_id.$extension;
        $uploadFolder = 'userStory/' . $image_id;
        checkDirectory($uploadFolder);
        $path = $request->file('audio')->move($uploadFolder, $file_name);
        $name = $file->getClientOriginalName();

        //store image file into directory and db
        $save = new MediaStory();
        $save->title = $name;
        $save->user_id = auth()->id();
        $save->story_media_id = $image_id;
        $save->path = $path;
        $save->type = 'audio';
        $save->save();

        return ['file_uploaded' => true, 'file_id' => $image_id];
    }
}

if (!function_exists('getPost')) {
    function getPost($id)
    {
        $post = Post::where('user_id', Auth::user()->id)->where('id', $id)->where('status', 'active')->with(['getPostImages','comments','likers'])->get();
        return $post;
    }
}

if (!function_exists('tagsFromString')) {
    function tagsFromString($string)
    {
        $allTags = [];
        $stringArrays = explode(' ', $string);
        foreach ($stringArrays as $string) {
            if (str_contains($string, '#')) {
                $word = str_replace('#', '', $string);
                $allTags []= $word;
            }
        }
        return $allTags;
    }
}


if (!function_exists('get_profile_image')) {
    function get_profile_image($profile_image = null)
    {
        return  $profile_image ? Storage::disk('public')->url($profile_image) : url('/')."/images/ahjili.png";
    }
}

if (!function_exists('get_path_storage')) {
    function get_path_storage($image)
    {
        return  Storage::disk('public')->url($image);
    }
}
