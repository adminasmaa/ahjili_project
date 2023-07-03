<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\API\BaseController as BaseController;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\UserCode;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Laravel\Socialite\Facades\Socialite;
use App\Http\Resources\UserResource;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class AuthController extends BaseController
{
    /**
     * Register api
     *
     * @return \Illuminate\Http\Response
     */
    public function register(Request $request)
    {
        // begin validation
        $validator = Validator::make($request->all(), [
            'full_name'     => 'required',
            'username'      => 'sometimes|alpha_dash|unique:users,username',
            'email'         => 'sometimes|email|unique:users,email',
            'dob'           => 'required',
            'gender'        => 'required',
            'phone_number'  => 'required|unique:users,phone_number',
            'password'      => 'required'
        ], [
            'full_name.required' => 'The :attribute field can not be blank value.',
            'email.required' => 'The :attribute field can not be blank value.',
            'username.unique' => 'The :attribute already exist',
            'email.unique' => 'The :attribute already exist',
            'dob.required' => 'The :attribute field can not be blank value.',
            'gender.required' => 'The :attribute field can not be blank value.',
            'phone_number.required' => 'The :attribute field can not be blank value.',
            'phone_number.unique' => 'The :attribute already exist',
            'password.required' => 'The :attribute field is required.',
            'username.alpha_dash' => 'Special characters or spaces not allowed in :attribute',
        ]);
        // check if errors
        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }
        // operation of registered
        $code = rand(1000, 9999);
        $name=str_replace(' ', '', $request->full_name);
        $username=$name.''.$code;
        $input = $request->only('full_name', 'username', 'password', 'email', 'dob', 'gender', 'phone_number');
        $input['username']=$username;
        $input['password'] = Hash::make($input['password']);
        // create a new user
        $user = User::create($input);
        // make a token
        $success['token'] =  $user->createToken('MyApp')->plainTextToken;
        $success['user'] =  new UserResource($user);
        // send sms code for verifed account
        sendSMSCode($user);
        // return response
        return $this->sendResponse($success, 'User register successfully');
    }

    /**
     * Check user name
     *
     * @return \Illuminate\Http\Response
     */
    public function checkUsername(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required|alpha_dash',
        ], [
            'username.required' => 'The :attribute field can not be blank value.',
            'username.alpha_dash' => 'Special characters or spaces not allowed in :attribute',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }

        $user = User::where('username', $request['username'])->count();
        //$user = User::where('username', 'LIKE', "%{$request['username']}%")->count();
        if ($user) {
            return $this->sendError('Not available.', array('username' => ["username is not available."]));
        } else {
            $success['username']=["username is available."];
            return $this->sendResponse($success, 'available.');
        }
    }

    public function checkNumber(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'country_code' => 'required',
            'phone_number' => 'required|numeric',
        ], [
            'phone_number.numeric' => 'Please enter numbers only',
            'phone_number.required' => 'The :attribute field can not be blank value.',
            'country_code.required' => 'The :attribute field can not be blank value.',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }

        $user = User::where('phone_number', $request['phone_number'])
        ->where('country_code', $request['country_code'])->count();
        if ($user) {
            return $this->sendError('Not available.', array('phone_number' => ["Phone number already exist."]));
        } else {
            $success['phone_number']=["Phone number available"];
            return $this->sendResponse($success, 'available.');
        }
    }

    public function checkEmail(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
        ], [
            'email.required' => 'The :attribute field can not be blank value.',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error', $validator->errors()->getMessages());
        }

        $user = User::where('email', $request['email'])->count();
        if ($user) {
            return $this->sendError('Not available.', array('email' => ["Email already exist."]));
        } else {
            $success['email']=["Email available"];
            return $this->sendResponse($success, 'available.');
        }
    }

    public function requestOtp(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
            'email' => 'required',
            'send_code_medium' => 'required|in:email,sms',
        ],
            [
               'email.exists' => 'The :attribute is not exists.',
               'send_code_medium.required' => 'The :attribute field is required.',
               'send_code_medium.in' => 'The :attribute must be sms or email.',
            ]
        );

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }

        $user=User::where('email', $request->email)
        ->orWhere(DB::raw("concat(country_code, '', phone_number)"), $request->email)
        ->first();

        if ($user) {
            if ($request->send_code_medium=='email') {
                sendemailOtpCode($user);
                $success['status']=['code send'];
                return $this->sendResponse($success, 'We sent you code on your email.');
            }
            if ($request->send_code_medium=='sms') {
                sendSMSCode($user);
                $success['status']=['code send'];
                return $this->sendResponse($success, 'We sent you code on your mobile number.');
            }
        } else {
            return $this->sendError('error', ['email' => ['Email or phone number is not exist']]);
        }
    }

    public function verifyOtp(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
            'email' => 'required',
            'code' => 'required',
        ],
            [
               'email.required' => 'The :attribute field can not be blank value.',
               'code.required' => 'The :attribute field is required.',
            ]
        );

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }

        $user=User::where('email', $request->email)
        ->orWhere(DB::raw("concat(country_code, '', phone_number)"), $request->email)->first();

        if ($user) {
            $find = UserCode::where('user_id', $user->id)
            ->where('code', $request->code)
            ->where('updated_at', '>=', now()->subMinutes(30))
            ->first();

            if (!is_null($find)) {
                $user->update(['verified' => true]);
                $success['status']=['Code Verified successfully'];
                return $this->sendResponse($success, 'Code Verified');
            }

            return $this->sendError('Not Match.', array('code' => ["You entered wrong code."]));
        } else {
            return $this->sendError('error', ['email' => ['Email or phone number is not exist']]);
        }
    }

    /**
     * Login api
     *
     * @return \Illuminate\Http\Response
     */
    public function login(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
            'email' => 'required',
            'password' => 'required',
            'fcmtoken' => 'required'
        ],
            [   'email.required' => 'The :attribute field can not be blank value.',
                'password.required' => 'The :attribute field can not be blank value.',
                'fcmtoken.required' => 'The :attribute field can not be blank value.'
            ],
        );
        if ($validator->fails()) {
            //dd($validator->errors()->getmessages());
            return $this->sendError('Validation Error.', $validator->errors());
        }
        // get user deleted
        $user_check_deleted = User::query()->onlyTrashed()
                                            ->where('email', request()->input('email'))
                                            ->orWhere('phone_number', request()->input('email'))
                                            ->whereNotNull('deleted_at')
                                            ->first();
        // check if user deleted
        if ($user_check_deleted) {
            $date_now = Carbon::now()->format('Y-m-d H:i');
            $date_deleted = Carbon::parse($user_check_deleted->deleted_at)->addHours(12)->format('Y-m-d H:i');
            if ($date_deleted <= $date_now) {
                $user_check_deleted->restore();
            } else {
                return response()->json([
                    'success' => false ,
                    'data'=> "You have deleted your account, you cannot login to your account until after 12 hours",
                ]);
            }

            // make restore
        }

        $field=checkfieldName(request()->input('email'));
        $credentials = request()->only($field, 'password');
        if (Auth::attempt($credentials)) {
            $user = Auth::user();
            $user->update(['fcmtoken'=>$request->fcmtoken]);
            $success['token'] =  $user->createToken('MyApp')->plainTextToken;
            $success['user'] =  new UserResource($user);

            return $this->sendResponse($success, 'User login successfully.');
        } else {
            return response()->json([
                'success' => false,'message' => ucfirst($field).' or Password is not correct.',
                'data'=> array('unauthorized ' => ["You are not authenticated. Authentication required to perform this operation."]),
            ], 401);
        }
    }

    //code for social login

    public function socialLogin(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'register_with' => 'required',
            'social_token' => 'required',
            'fcmtoken'      => 'required'
        ], [
            'register_with.required' => 'The :attribute field can not be blank value.',
            'social_token.required' => 'The :attribute field can not be blank value.',
            'fcmtoken.required' => 'The :attribute field can not be blank value.',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }

        $social_user=null;
        if ($request->register_with == User::GOOGLE) {
            $social_user = Socialite::driver(User::GOOGLE)->stateless()->userFromToken($request->social_token);
        }
        if ($request->register_with == User::FACEBOOK) {
            $social_user = Socialite::driver(User::FACEBOOK)->stateless()->userFromToken($request->social_token);
        }
        if (is_null($social_user->getEmail())) {
            return $this->sendError('error.', array('email' => ["Something went wrong."]));
        } else {
            $finduser = User::where(['email' => $social_user->getEmail()])->first();
            if ($finduser) {
                $user=Auth::loginUsingId($finduser->id, true);
                $success['token'] =  $user->createToken('MyApp')->plainTextToken;
                $success['user'] =  new UserResource($user);
                return $this->sendResponse($success, ['message' => ['User login successfully']]);
            } else {
                $code = rand(1000, 9999);
                $name=str_replace(' ', '', $social_user->getName());
                $username=$name.''.$code;
                // create a new user
                $newUser = User::Create([
                        'email' => $social_user->getEmail(),
                        'full_name' => $social_user->getName() ?? "",
                        'username' => $username ?? "",
                        'password' => Hash::make('123456dummy'),
                        'fcmtoken' => $request->fcmtoken,
                        'gender' => 'male',
                    ]);
                // make a authenticate
                if (Auth::attempt(['email' => $newUser->email, 'password' => '123456dummy'])) {
                    $user=Auth::User();
                    $success['token'] =  $user->createToken('MyApp')->plainTextToken;
                    $success['user'] =  new UserResource($user);
                    return $this->sendResponse($success, ['message' => ['User login successfully']]);
                }
            }
        }
    }


    public function resetPassword(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
            'email' => 'required',
            'password' => 'required|min:8',
            'c_password' => 'required|same:password',
        ],
            [
                'email.required' => 'The :attribute field is required.',
                'password.required' => 'The :attribute field is required.',
                'c_password.required' => 'The :attribute field is required.',
                'c_password.same' => 'The :attribute field is not same.',
            ]
        );

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }

        $user=User::where('email', $request->email)
        ->orWhere(DB::raw("concat(country_code, '', phone_number)"), $request->email)->first();

        if ($user) {
            $user->update(['password'=> Hash::make($request->password)]);
            $success['email']=['Password changed successfully'];
            return $this->sendResponse($success, 'Changed successfully');
        } else {
            return $this->sendError('error', ['email' => ['Email or phone number is not exist']]);
        }
    }

    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();
        $success['status']=['User Logout successfully.'];
        return $this->sendResponse($success, 'User Logout.');
    }
}
