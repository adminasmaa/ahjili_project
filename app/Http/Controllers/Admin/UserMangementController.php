<?php

namespace App\Http\Controllers\Admin;

use App\Models\Role;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Validator;
use Illuminate\Support\Facades\Storage;
use App\Models\Post;
use App\Models\Story;
use Illuminate\Validation\Rule;
use Alert;
class UserMangementController extends Controller
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

//        if ($request->status == 'block') {
//            $users->where('status', 'block');
//        }
//        if ($request->status == 'ban') {
//            $users->where('status', 'ban');
//        }

        $users = $users->orderBy('created_at', 'DESC')->where('account_type', '=', 'Admin')->paginate($limit);

        return view('admin/usermangements/index')->with('users', $users);
    }



    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $roles = Role::get();

        return view('admin/usermangements/create')->with('roles', $roles);

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        $input = $request->all();
        $input['password'] = bcrypt($input['password']);
        $input['status'] = 'active';
        $input['account_type'] = 'Admin';

        $user = User::create($input);
        if ($request->roles) {

            $user->syncRoles($request->roles);
        }
        Alert::success('Success','User Added successfully');

        return redirect()->route('admin.usermangements.index')->with('success', 'User Added successfully');


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
        $user = User::findorFail($id);
        $roles = Role::get();
        return view('admin/usermangements/edit',compact('user','roles'));


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

        $user = User::findorFail($id);
        $input = $request->all();
        $input['password'] = bcrypt($input['password']);
        $user->update($input);
        if ($request->roles) {

            $user->syncRoles($request->roles);
        }
        Alert::success('Success','User Updated successfully');

        return redirect()->route('admin.usermangements.index')->with('success', 'User Updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {

    }


}
