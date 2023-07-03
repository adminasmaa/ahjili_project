<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Support\Str;

use App\Models\Notification;
use App\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $limit = $request->has('limit') ? $request->limit : 10;

        $notification = Notification::query()->select('id', 'data->title as title', 'data->description as description', 'notifiable_id', 'created_at', 'data->type', 'type')->where('data->type', 'send_dashboard');
        if ($request->has('search') && $request->search != '') {
            $notification->where('data->title', 'like', '%' . $request->search . '%')
                ->orWhere('data->description', 'like', '%' . $request->search . '%')
                ->orWhere('created_at', $request->search);
        }

        $notifications = $notification->paginate($limit);

        $users = User::get();

        return view('admin.notifications.index', compact('notifications', 'users'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $data = json_encode([
            'type' => 'send_dashboard',
            'title' => $request->title,
            'description' => $request->data
        ]);

        Notification::create([
            'id'   =>Str::uuid()->toString(),
            'data' =>$data,
            'notifiable_id' => $request->user_id,
            'notifiable_type' =>'App\Models\User',
            'type' => 'null'
        ]);

        return redirect()->route('admin.notifications.index')->with('success', 'Notifications Added successfully');
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
    public function update(Notification $notification, Request $request)
    {
        $data = json_encode([
            'type' => 'send_dashboard',
            'title' => $request->title,
            'description' => $request->data
        ]);

        $notification->update(['data' => $data]);
        return redirect()->route('admin.notifications.index')->with('success', 'updated  successfully');
    }

    /**
     * Remove the specified resource from storage.
     *
     *
     *
     *
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Notification $notification)
    {
        $notification->delete();
        return redirect()->route('admin.notifications.index')->with('success', 'deleted  successfully');
    }
}
