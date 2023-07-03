<?php

namespace App\Http\Controllers\Admin;

use App\Models\Role;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;
use Illuminate\Support\Facades\Storage;
use App\Models\Post;
use App\Models\Story;
use Illuminate\Validation\Rule;
use Alert;

class RoleController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $limit = $request->has('limit') ? $request->limit : 10;

        $roles = Role::query();
        if ($request->has('search') && $request->search != '') {
            $roles->where('name', 'like', '%' . $request->search . '%')
                ->orWhere('display_name', $request->search);

        }


        $roles = $roles->orderBy('created_at', 'DESC')->paginate($limit);

        return view('admin/roles/index')->with('roles', $roles);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {


        $models = ['users', 'usermangements', 'roles',
            'posts', 'categories', 'helps'
        ];


        $maps = ['create', 'update', 'read', 'delete'];


        return view('admin.roles.create', compact('models', 'maps'));

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        $input = $request->except('permissions');

        $role = Role::create($input);
        if ($request->has('permissions')) {
            $all_permissions = array_merge($request->permissions, []);
            $role->syncPermissions($all_permissions);

        }
        Alert::success('Success', 'User Added successfully');

        return redirect()->route('admin.roles.index')->with('success', 'Role Added successfully');


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
        $role = Role::findorFail($id);

        $models = ['users', 'usermangements', 'roles',
            'posts', 'categories', 'helps'
        ];


        $maps = ['create', 'update', 'read', 'delete'];
        return view('admin/roles/edit', compact('role', 'models', 'maps'));


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

        $role = Role::findorFail($id);
        $input = $request->except('permissions');
        $role->update($input);
        if ($request->has('permissions')) {
            $all_permissions = array_merge($request->permissions, []);
            $role->syncPermissions($all_permissions);

        }
        Alert::success('Success', 'User Updated successfully');

        return redirect()->route('admin.roles.index')->with('success', 'Role Updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $role = Role::findorFail($id);

        $role->delete();
        return back();
    }


}
