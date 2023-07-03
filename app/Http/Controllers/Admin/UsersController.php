<?php

namespace App\Http\Controllers\Admin;

use App\Models\Blocked;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Validator;
use Illuminate\Support\Facades\Storage;
use App\Models\Post;
use App\Models\Story;
use Illuminate\Validation\Rule;

class UsersController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $limit = $request->has('limit') ? $request->limit : 10;

        $users = User::query();
        if ($request->has('search') && $request->search != '') {
            $users->where('username', 'like', '%' . $request->search . '%')
                ->orWhere('email', $request->search)
                ->orWhere('phone_number', 'like', '%' . $request->search . '%')
                ->orWhere('status', 'like', '%' . $request->search . '%');
        }

        if ($request->status == 'block') {
            $users->where('status', 'block');
        }
        if ($request->status == 'ban') {
            $users->where('status', 'ban');
        }

        $users = $users->where('account_type', '=', 'public')->orWhere('account_type', '=', 'private')->orderBy('created_at', 'DESC')->paginate($limit);

        return view('admin/users/index')->with('users', $users);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {

        $request['block_comment'] = isset($request['block_comment']) ? $request['block_comment'] : 0;
        $request['block_post'] = isset($request['block_post']) ? $request['block_post'] : 0;
        $request['block_message'] = isset($request['block_message']) ? $request['block_message'] : 0;
        Blocked::updateOrCreate(['user_id' => $request->user_id], $request->all());

        return back();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'full_name' => 'required',
            'username' => 'nullable|unique:users,username',
            'email' => 'nullable|email|unique:users,email',
            'dob' => 'required',
            'gender' => 'required',
            'phone_number' => 'required|unique:users,phone_number',
            'password' => 'required'
        ], [
            'unique' => ':attribute already exist',
            'required' => 'The :attribute field is required.'
        ]);
        $error['token'] = '';
        $error['email'] = '';
        $error['username'] = '';
        $error['status'] = '';
        $error['uuid'] = '';
        if ($validator->fails()) {
            return $this->sendError($error, $validator->errors()->getMessages());
        }
        if ($request['verified'] == 1) {
            $input = $request->all();
            $input['password'] = bcrypt($input['password']);
            $input['status'] = 'active';
            $input['uuid'] = Str::uuid()->toString();
            $user = User::create($input);
            $success['token'] = $user->createToken('MyApp')->accessToken;
            $success['email'] = $user->email;
            $success['username'] = $user->username;
            $success['status'] = $user->status;
            $success['uuid'] = $user->uuid;

            return $this->sendResponse($success, ['message' => ['User register successfully']]);
        } else {
            return $this->sendError('Validation Error', ['message' => ['User not verified']]);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $status = $request->status;
        $user = User::findorFail($id);
        $user->update(['status' => $status]);
        return redirect()->back()->with('success', 'User block successfully');
        //session()->put('success','User block successfully');
        //return redirect()->back();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $user = User::findorFail($id);
//        $allposts = $user->posts;
//        $allstories = $user->stories;
//        // dd($user,$allposts,$allstories);
//        //posts delete start
//        ini_set('memory_limit', '44M');
//        if (count($allposts) > 0) {
//            foreach ($allposts as $key => $post) {
//                $postmedia = $post->getPostImages;
//
////                return $postmedia;
//                if (count($postmedia) > 0) {
//                    foreach ($postmedia as $single) {
//
//
//                        $opath = explode('https://ahjili.s3.eu-central-1.amazonaws.com/', $single->path);
//
////                        return $opath[1];
//                        $path = $opath[1] ?? "";
//
////                        return Storage::disk('s3')->exists($path);
////                        if (!empty(Storage::disk('s3')->exists($path))) {
////
//////                            return "cfgf";
////                            Storage::disk('s3')->delete($path);
////                        }
//                        $single->delete();
//                    }
//                }
//                $post->delete();
//            }
//        }
//        // post delete end
//        //stories delete start
//        foreach ($allstories as $key => $story) {
//            $storymedia = $story->getStoryMedia;
//            if (count($storymedia) > 0) {
//                foreach ($storymedia as $single) {
//                    $opath = explode('https://ahjili.s3.eu-central-1.amazonaws.com/', $single->path);
//                    $path = $opath[1] ?? "";
////                    if (Storage::disk('s3')->exists($path)) {
////                        Storage::disk('s3')->delete($path);
////                    }
//                    $single->delete();
//                }
//            }
//            $story->delete();
//        }
        //stories delete end
        $user->delete();

        return back();

//        return redirect()->route('admin.users.index')->with('success', 'User deleted successfully');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function getUserProfile($id)
    {
//        $user = User::where('id',$id)->with('profile')->first();
        $user = User::where('id', $id)->with(['comments', 'posts'])->first();

//        return $user;

        return view('admin/users/userProfile', compact('user'));
    }

    public function ChangePassword($id)
    {
        $user = User::where('id', $id)->first();
        $password = $user->password;


        return view('admin/users/changePassword', compact('user', 'password'));
    }

    public function updatePassword(Request $request)
    {
//        return $request['password'];
//        $validator = Validator::make($request->all(), [
//            'oldpassword' => 'required',
//            'newpassword' => 'required|different:oldpassword|min:6',
//            'confirmpassword' => 'nullable|same:newpassword',
//        ]);
//
//        if ($validator->fails()) {
//            return $this->sendError(null,$validator->errors()->getMessages());
//        }

        $request->validate([

                'oldpassword' => 'required',
                'newpassword' => 'required|different:oldpassword|min:6',
                'confirmpassword' => 'nullable|same:newpassword',

            ]
        );
        $user = $request->user;
        if (Hash::check($request->oldpassword, $request->password)) {
            $user->fill([
                'password' => Hash::make($request['newpassword'])
            ])->save();


        }

        return redirect()->route('admin.users.index')->with('success', 'User Updated Password successfully');


    }


    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public
    function active($id)
    {
        dd($id);
        $users = User::orderBy('created_at', 'DESC')->where('status', $id)->get();
        return view('admin/users/index')->with('users', $users);
    }
}
