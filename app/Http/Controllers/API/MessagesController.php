<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use App\Models\ChMessage as Message;
use App\Models\ChFavorite as Favorite;
use App\Facades\ChatifyMessenger as Chatify;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Kutia\Larafirebase\Facades\Larafirebase;

class MessagesController extends BaseController
{
    protected $perPage = 30;

    public function __construct(Request $request)
    {
        $this->middleware(function ($request, $next) {
            $this->user = Auth::user();
            return $next($request);
        });
    }
    /**
        * Authinticate the connection for pusher
        *
        * @param Request $request
        * @return void
        */
    public function pusherAuth(Request $request)
    {
        // Auth data
        $authData = json_encode([
            'user_id' => Auth::user()->id,
            'user_info' => [
                'name' => Auth::user()->username
            ]
        ]);
        // check if user authorized
        if (Auth::check()) {
            return Chatify::pusherAuth(
                $request['channel_name'],
                $request['socket_id'],
                $authData
            );
        }
        // if not authorized
        return response()->json(['message'=>'Unauthorized'], 401);
    }

    /**
     * Fetch data by id for (user/group)
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function idFetchData(Request $request)
    {
        return auth()->user();
        // Favorite
        $favorite = Chatify::inFavorite($request['id']);

        // User data
        if ($request['type'] == 'user') {
            $fetch = User::where('id', $request['id'])->first();
            if ($fetch) {
                $userAvatar = Chatify::getUserWithAvatar($fetch)->avatar;
            }
        }

        // send the response
        return Response::json([
            'favorite' => $favorite,
            'fetch' => $fetch ?? [],
            'user_avatar' => $userAvatar ?? null,
        ]);
    }

    /**
     * This method to make a links for the attachments
     * to be downloadable.
     *
     * @param string $fileName
     * @return \Illuminate\Http\JsonResponse
     */
    public function download($fileName)
    {
        $path = config('chatify.attachments.folder') . '/' . $fileName;
        if (Chatify::storage()->exists($path)) {
            return response()->json([
                'file_name' => $fileName,
                'download_path' => Chatify::storage()->url($path)
            ], 200);
        } else {
            return response()->json([
                'message'=>"Sorry, File does not exist in our server or may have been deleted!"
            ], 404);
        }
    }

    /**
     * Send a message to database
     *
     * @param Request $request
     * @return JSON response
     */
    public function send(Request $request)
    {
        // make a validation
        $validator = Validator::make(
            $request->all(),
            [
            'id' => 'required|exists:users,id',
        ],
            [  'id.required' => 'The :attribute field can not be blank value.',
               'id.exists' => 'The :attribute is not exist.',
            ]
        );

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }
        // get user a send message
        $user_sended = User::find($this->user->id);
        // get user receiver
        $user_receiver = User::find($request->id);
        // get count of messages between them
        $count_messages_between_them = Message::query()
                                                ->whereIn('from_id', [$user_sended->id,$user_receiver->id])
                                                ->whereIn('to_id', [$user_sended->id,$user_receiver->id])
                                                ->count();

        // return $count_messages_beetween_them;
        // check if user have account private

        if ($user_receiver->account_type == 'private') {
            // check if user sended is fo
            if (!$user_sended->isFollowing($user_receiver)) {
                if ($count_messages_between_them == 0) {
                    return $this->sendError('Cannot send messages because this account a private');
                }
            }
        }
        // default variables
        $error = (object)[
           'status' => 0,
           'message' => null
        ];
        $attachment = null;
        $attachment_title = null;
        if ($request->has('id')) {
            // if there is attachment [file]
            if ($request->hasFile('file')) {
                // allowed extensions
                $allowed_images = Chatify::getAllowedImages();
                $allowed_files  = Chatify::getAllowedFiles();
                $allowed        = array_merge($allowed_images, $allowed_files);

                $file = $request->file('file');
                // check file size
                if ($file->getSize() < Chatify::getMaxUploadSize()) {
                    if (in_array(strtolower($file->getClientOriginalExtension()), $allowed)) {
                        // get attachment name
                        $attachment_title = $file->getClientOriginalName();
                        // upload attachment and store the new name
                        $attachment = Str::uuid() . "." . $file->getClientOriginalExtension();

                        $file->storeAs(config('chatify.attachments.folder'), $attachment, config('chatify.storage_disk_name'));
                    } else {
                        $error->status = 1;
                        $error->message = "File extension not allowed!";
                    }
                } else {
                    $error->status = 1;
                    $error->message = "File size you are trying to upload is too large!";
                }
            }
        }

        if (!$error->status) {
            // send to database
            $messageID = mt_rand(9, 999999999) + time();
            Chatify::newMessage([
                'id' => $messageID,
                'type' => $request['type'],
                'from_id' => Auth::user()->id,
                'to_id' => $request['id'],
                'body' => htmlentities(trim($request['message']), ENT_QUOTES, 'UTF-8'),
                'attachment' => ($attachment) ? json_encode((object)[
                    'new_name' => $attachment,
                    'old_name' => htmlentities(trim($attachment_title), ENT_QUOTES, 'UTF-8'),
                ]) : null,
            ]);

            // fetch message to send it with the response
            $messageData = Chatify::fetchMessage($messageID);

            // send to user using pusher
            $chat = Chatify::push('my-ahjili', 'my-event', [
                'from_id' => Auth::user()->id,
                'to_id' => $request['id'],
                'message' => $messageData
            ]);
            // user recieve
            $user_recieve = User::find($request['id']);
            // send notification to firebase
            Larafirebase::fromArray(
                [
                    'from_id' => Auth::user()->id,
                    'to_id' => $request['id'],
                    'message' => $messageData,
                    'sound' => 'default',
                ]
            )->sendMessage($user_recieve->fcmtoken);
        }
        // send the response
        return Response::json([
            'status' => '200',
            'error' => $error,
            'message' => $messageData ?? [],
            'tempID' => $request['temporaryMsgId'],
        ]);
    }

    /**
     * fetch [user/group] messages from database
     *
     * @param Request $request
     * @return JSON response
     */
    public function fetch(Request $request)
    {
        $query = Chatify::fetchMessagesQuery($request['id'])->latest();
        $messages = $query->paginate($request->per_page ?? $this->perPage);
        $totalMessages = $messages->total();
        $lastPage = $messages->lastPage();
        $this->messagesTransform($messages);
        $response = [
            'total' => $totalMessages,
            'last_page' => $lastPage,
            'last_message_id' => collect($messages->items())->last()->id ?? null,
            'messages' => $messages->items(),
        ];
        return Response::json($response);
    }

    /**
     * Make messages as seen
     *
     * @param Request $request
     * @return void
     */
    public function seen(Request $request)
    {
        // make as seen
        $seen = Chatify::makeSeen($request['id']);
        // send the response
        return Response::json([
            'status' => $seen,
        ], 200);
    }

    /**
     * Get contacts list
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse response
     */
    public function getContacts(Request $request)
    {
        // get all users that received/sent message from/to [Auth user]
        $users = Message::join('users', function ($join) {
            $join->on('ch_messages.from_id', '=', 'users.id')
                ->orOn('ch_messages.to_id', '=', 'users.id');
        })
        ->where(function ($q) {
            $q->where('ch_messages.from_id', Auth::user()->id)
            ->orWhere('ch_messages.to_id', Auth::user()->id);
        })
        ->where('users.id', '!=', Auth::user()->id)
        ->select('users.*', DB::raw('MAX(ch_messages.created_at) max_created_at'))
        ->orderBy('max_created_at', 'desc')
        ->groupBy('users.id')
        ->paginate($request->per_page ?? $this->perPage);
        // make a transform of users
        $this->usersTransform($users);
        // return response
        return response()->json([
            'contacts' => $users->items(),
            'current_user_name' => Auth::user()->username,
            'total' => $users->total() ?? 0,
            'last_page' => $users->lastPage() ?? 1,
        ], 200);
    }

    /**
     * Put a user in the favorites list
     *
     * @param Request $request
     * @return void
     */
    public function favorite(Request $request)
    {
        // check action [star/unstar]
        if (Chatify::inFavorite($request['user_id'])) {
            // UnStar
            Chatify::makeInFavorite($request['user_id'], 0);
            $status = 0;
        } else {
            // Star
            Chatify::makeInFavorite($request['user_id'], 1);
            $status = 1;
        }

        // send the response
        return Response::json([
            'status' => @$status,
        ], 200);
    }

    /**
     * Get favorites list
     *
     * @param Request $request
     * @return void
     */
    public function getFavorites(Request $request)
    {
        $favorites = Favorite::where('user_id', Auth::user()->id)->get();
        foreach ($favorites as $favorite) {
            $favorite->user = User::where('id', $favorite->favorite_id)->first();
        }
        return Response::json([
            'total' => count($favorites),
            'favorites' => $favorites ?? [],
        ], 200);
    }

    /**
     * Search in messenger
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function search(Request $request)
    {
        $input = trim(filter_var($request['input']));
        $records = User::where('id', '!=', Auth::user()->id)
                    ->where('name', 'LIKE', "%{$input}%")
                    ->paginate($request->per_page ?? $this->perPage);

        foreach ($records->items() as $index => $record) {
            $records[$index] += Chatify::getUserWithAvatar($record);
        }

        return Response::json([
            'records' => $records->items(),
            'total' => $records->total(),
            'last_page' => $records->lastPage()
        ], 200);
    }

    /**
     * Get shared photos
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function sharedPhotos(Request $request)
    {
        $images = Chatify::getSharedPhotos($request['user_id']);

        foreach ($images as $image) {
            $image = asset(config('chatify.attachments.folder') . $image);
        }
        // send the response
        return Response::json([
            'shared' => $images ?? [],
        ], 200);
    }

    /**
     * Delete conversation
     *
     * @param Request $request
     * @return void
     */
    public function deleteConversation(Request $request)
    {
        // delete
        $delete = Chatify::deleteConversation($request['id']);

        // send the response
        return Response::json([
            'deleted' => $delete ? 1 : 0,
        ], 200);
    }


    /**
     * deleteMessage
     *
     * @param  mixed $request
     * @return void
     */
    public function deleteMessage(Request $request)
    {
        // delete a message
        $delete = Chatify::deleteMessage($request->id_message);

        // send the response
        return Response::json([
            'deleted' => $delete ? 1 : 0,
        ], 200);
    }

    public function updateSettings(Request $request)
    {
        $msg = null;
        $error = $success = 0;

        // dark mode
        if ($request['dark_mode']) {
            $request['dark_mode'] == "dark"
                ? User::where('id', Auth::user()->id)->update(['dark_mode' => 1])  // Make Dark
                : User::where('id', Auth::user()->id)->update(['dark_mode' => 0]); // Make Light
        }

        // If messenger color selected
        if ($request['messengerColor']) {
            $messenger_color = trim(filter_var($request['messengerColor']));
            User::where('id', Auth::user()->id)
                ->update(['messenger_color' => $messenger_color]);
        }
        // if there is a [file]
        if ($request->hasFile('avatar')) {
            // allowed extensions
            $allowed_images = Chatify::getAllowedImages();

            $file = $request->file('avatar');
            // check file size
            if ($file->getSize() < Chatify::getMaxUploadSize()) {
                if (in_array(strtolower($file->getClientOriginalExtension()), $allowed_images)) {
                    // delete the older one
                    if (Auth::user()->avatar != config('chatify.user_avatar.default')) {
                        $path = Chatify::getUserAvatarUrl(Auth::user()->avatar);
                        if (Chatify::storage()->exists($path)) {
                            Chatify::storage()->delete($path);
                        }
                    }
                    // upload
                    $avatar = Str::uuid() . "." . $file->getClientOriginalExtension();
                    $update = User::where('id', Auth::user()->id)->update(['avatar' => $avatar]);
                    $file->storeAs(config('chatify.user_avatar.folder'), $avatar, config('chatify.storage_disk_name'));
                    $success = $update ? 1 : 0;
                } else {
                    $msg = "File extension not allowed!";
                    $error = 1;
                }
            } else {
                $msg = "File size you are trying to upload is too large!";
                $error = 1;
            }
        }

        // send the response
        return Response::json([
            'status' => $success ? 1 : 0,
            'error' => $error ? 1 : 0,
            'message' => $error ? $msg : 0,
        ], 200);
    }

    /**
     * Set user's active status
     *
     * @param Request $request
     * @return void
     */
    public function setActiveStatus(Request $request)
    {
        $update = $request['status'] > 0
            ? User::where('id', $request['user_id'])->update(['active_status' => 1])
            : User::where('id', $request['user_id'])->update(['active_status' => 0]);
        $user = User::where('id', $request['user_id']);
        $user->touch();
        // send the response
        return Response::json([
            'status' => $update,
        ], 200);
    }

    public function usersTransform(&$users)
    {
        $users->transform(function ($query) {
            $data = $query->toArray();
            $users_blocked =  Auth::user()->bans()->pluck('ban_user_id')->toArray();
            // get user banned if founded

            $message = Message::where(['from_id'=> Auth::user()->id,'to_id'=>$data['id']])->latest('created_at')->first();
            $last_seen = null;
            if ($data['active_status'] == 0) {
                $user = User::where('id', $data['id'])->first();
                $last_seen = $user->updated_at->diffForHumans();
            }
            $data = [
                'id'            => $data['id'],
                'full_name'     => $data['full_name'],
                'user_name'     => $data['username'],
                'profile_image' =>  getUserProfileImage($data['id']),
                'last_message'  => $message['body'] ?? '',
                'message_seen'  => $message['seen'] ?? 0,
                'last_seen'     => $last_seen,
                'active_status' => $data['active_status'],
                'is_blocked'    => in_array($data['id'], $users_blocked)

            ];
            return $data;
        });
    }

    public function messagesTransform(&$messages)
    {
        $messages->transform(function ($query) {
            $data = $query->toArray();
            $attachment = '';
            $attachment_title = '';
            $attachment_type = '';
            if (isset($query->attachment)) {
                $attachmentOBJ = json_decode($query->attachment);
                $attachment = $attachmentOBJ->new_name;
                $attachment_title = htmlentities(trim($attachmentOBJ->old_name), ENT_QUOTES, 'UTF-8');

                $ext = pathinfo($attachment, PATHINFO_EXTENSION);
                $attachment_type = in_array($ext, Chatify::getAllowedImages()) ? 'image' : 'file';
            }
            if (!empty($attachment)) {
                $data['attachment'] = array('attachment' =>$attachment,'title' => $attachment_title, 'type' => $attachment_type);
            } else {
                $data['attachment'] = null;
            }


            return $data;
        });
    }


    /**
     * CountMessageNotSeen
     *
     * @return void
     */
    public function CountMessageNotSeen()
    {
        // get count messages not seen
        $count_messages = Message::query()->where('to_id', $this->user->id)->where('seen', 0)->count();

        // send the response
        return Response::json([
           'count_messages' => $count_messages,
        ], 200);
    }
}
