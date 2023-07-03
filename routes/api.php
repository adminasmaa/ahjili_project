<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\ProfileController;
use App\Http\Controllers\API\FollowController;
use App\Http\Controllers\API\PostController;
use App\Http\Controllers\API\CommentController;
use App\Http\Controllers\API\MessagesController;
use App\Http\Controllers\API\SearchController;
use App\Http\Controllers\API\StoryController;
use App\Http\Controllers\API\TagController;
use Illuminate\Support\Facades\Storage;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::controller(AuthController::class)->group(function () {
    Route::post('register', 'register');
    Route::any('check_username', 'checkUsername');
    Route::post('check_number', 'checkNumber');
    Route::post('check_email', 'checkEmail');
    Route::any('request_otp', 'requestOtp');
    Route::post('verify_otp', 'verifyOtp');
    Route::post('login', 'login');
    Route::post('social_login', 'socialLogin');
    Route::post('reset_password', 'resetPassword');
});

Route::middleware(['auth:sanctum'])->group(function () {
    Route::controller(AuthController::class)->group(function () {
        Route::post('logout', 'logout');
    });

    // Route of search
    Route::get('/search', SearchController::class);

    // Routes of Profiles
    Route::controller(ProfileController::class)->group(function () {
        Route::get('get_user_profile', 'getUserProfile');
        Route::post('update_profile', 'updateProfile');
        Route::post('update_profile_image', 'updateProfileImage');
        Route::post('update_account_type', 'updateAccountType');
        Route::post('add/to/bookmark', 'addToBookmark');
        Route::post('remove/bookmark', 'removeBookmark');
        Route::get('bookmark/list', 'bookmarkList');
        Route::get('get_users', 'getUsers');
        Route::get('get_users_banned', 'getAllUserBanned');
        Route::get('get_all_notifications', 'getAllNotifications');
        Route::get('get_unreaded_notifications', 'getUnreadNotifications');
        Route::get('get_readed_notifications', 'getReadNotifications');
        Route::get('old_get_user_profile', 'oldgetUserProfile');
        Route::post('mark_notifications_read', 'markReadNotification');
        Route::post('save_user_story', 'saveUserStory');
        Route::post('delete_user', 'deleteLogicUser');
        Route::post('user_ban/{user}', 'banUser');
        Route::post('user_unban/{user}', 'unbanUser');
    });

    // Routes of follows
    Route::controller(FollowController::class)->group(function () {
        Route::post('follow_unfollow', 'followUnfollow');
        Route::post('accepted_follow', 'acceptedFollow');
        Route::post('rejected_follow', 'rejectedFollow');
        Route::get('followers/list', 'followersList');
        Route::get('following/list', 'followingList');
    });

    // Routes of Posts
    Route::controller(PostController::class)->group(function () {
        Route::post('create_user_post', 'store');
        Route::post('update_user_post', 'update');
        Route::post('create_anonymous_post', 'anonymousStore');
        Route::post('delete_post', 'delete');
        Route::get('get_user_posts', 'getUserPosts');
        Route::post('like_post', 'likePost');
        Route::get('get_all_post', 'getAllUsersPost');
        Route::get('get_all_post_vedios', 'getAllUsersPostVedios');
        Route::get('get_all_post_images', 'getAllUsersPostImages');
        Route::get('get_all_anonymous_post', 'getAllUsersAnonymousPost');
        Route::get('get_tagged_post/{tag}', 'taggedPost')->name('tag.post');

        Route::get('report/message/list', 'reportMessageList');
        Route::get('report/post/list', 'reportPostList');
        Route::post('abuse/report/post', 'postAbuseReport');
    });


    // Routes of Comments
    Route::controller(CommentController::class)->group(function () {
        Route::post('comment_add', 'store');
        Route::get('post_comments', 'postComments');
    });


    // Routes of Tags
    Route::controller(TagController::class)->group(function () {
        Route::get('/search_tag', 'searchTag');
    });

    // Routes of Stories
    Route::controller(StoryController::class)->group(function () {
        Route::post('create_story', 'store')->name('story.create');
        Route::get('get_stories', 'lisiting')->name('story.all');
        Route::post('new_create_story', 'newStoryStore')->name('new_story.store');
        Route::post('delete_story', 'delete')->name('story.delete');
        Route::get('new_get_stories', 'newlisiting')->name('new_story.all');
        Route::get('show_stories', 'show_stories')->name('show_stories');
        Route::post('story_seen', 'story_seen')->name('story.seen');
    });
    // Routes of Messages
    Route::controller(MessagesController::class)->group(function () {
        Route::post('sendMessage', 'send')->name('send.message');
        Route::post('fetchMessages', 'fetch')->name('fetch.messages');
        Route::post('makeSeen', 'seen')->name('messages.seen');
        Route::post('setActiveStatus', 'setActiveStatus')->name('activeStatus.set');
        Route::post('deleteConversation', 'deleteConversation')->name('deleteConversation');
        Route::post('deleteMessage', 'deleteMessage')->name('deleteMessage');
        Route::get('getContacts', 'getContacts')->name('contacts.get');
        Route::get('/count_messages_not_seen', 'CountMessageNotSeen')->name('messages.count_message_not_seen');
    });

    //for test s3
    Route::post('upload_picture', function (Request $request) {
        ini_set('memory_limit', '44M');
        $base_location = 'ahjili_app/images';
        // Handle File Upload
        if ($request->hasFile('profile_picture')) {
            $documentPath = $request->file('profile_picture')->store($base_location, 's3');
            $bucket_path = Storage::disk('s3')->url($documentPath);
            return response()->json(['success' => true, 'path' => $bucket_path], 200);
        } else {
            return response()->json(['success' => false, 'message' => 'No file uploaded'], 400);
        }

        return response()->json(['success' => false, 'message' => 'skipped'], 400);
    });
    /**
    * Authentication for pusher private channels
    */
    Route::post('/chat/auth', [MessagesController::class,'pusherAuth'])->name('chat.auth');
});

// Route::post('/firebase', function () {
//     $SERVER_API_KEY = 'AAAAIgf8M38:APA91bEi_GQzl-FXRLbjktUzD-Lxrxb8E_9PEzwJfi8NWG3-02-_nPiPhuR0SsQqiL0qpCk0pKFMuBXrvak8EOiQz8V8qQsrvMFGUMV-QMwWqShanEFou6x6EVW2X-Ax-to5RNov9tvK';

//     $data = [
//         "registration_ids" => [
//             'fuB5YS-KRK-TMS7vnPZFWu:APA91bG4nMmlvlo2IS38MYQzYhg0THYKsKF8Q2BUV22SBQzv156cBQmLNyEbJwoBeg_3qi16SKse_av7jXxl6pUvronMIIf-o25xmas1AK6XbmuDeE0EXuhRz8iHIN1E17nxQRnhhKKJ'
//         ],
//         "notification" => [
//             "title" => 'this is a title',
//             "body" => 'this is a body',
//         ]
//     ];
//     $dataString = json_encode($data);

//     $headers = [
//         'Authorization: key=' . $SERVER_API_KEY,
//         'Content-Type: application/json',
//     ];

//     $ch = curl_init();

//     curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');
//     curl_setopt($ch, CURLOPT_POST, true);
//     curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
//     curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
//     curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
//     curl_setopt($ch, CURLOPT_POSTFIELDS, $dataString);

//     $response = curl_exec($ch);

//     return $response;
// });
