<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Post;
use App\Models\ReportAbusePost;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Models\ReportMessage;

class PostController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {

        $limit = $request->has('limit') ? $request->limit : 10;

        $posts = Post::with('user')->paginate($limit);
        return view('admin/posts/index')->with('posts', $posts);

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

        $request_data = $request->except('images');
        $request_data['user_id'] = Auth::id();
        Post::create($request_data);

        return redirect()->route('admin.posts.index')->with('success', 'Post Added successfully');


    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $post = Post::findorFail($id);

//        return $post->getPostImages;
        $aposts = ReportAbusePost::where('post_id', $id)->get();
        return view('admin.posts.show', compact('post', 'aposts'));
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
        $post=Post::find($id);
        $request_data = $request->except('images');
//        $request_data['user_id'] = Auth::id();
        $post->update($request_data);

        return redirect()->route('admin.posts.index')->with('success', 'Post Updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $post=Post::find($id);
        $post->delete();
        return redirect()->route('admin.posts.index')->with('success', 'Post deleted successfully');

    }

    public function status(Request $request, $id)
    {

        $limit = $request->has('limit') ? $request->limit : 10;
        if ($id == 'active') {
            $posts = Post::with('user')->paginate($limit);
        } else {
            $posts = Post::with('user')->paginate($limit);
        }

        return view('admin/posts/index')->with('posts', $posts);
    }

    public function abusePostsIndex(Request $request)
    {

        $limit = $request->has('limit') ? $request->limit : 10;
        $alldata = ReportAbusePost::query();

        if ($request->has('search') && $request->search != '') {
            $alldata->whereHas('post', function ($query) use ($request) {
                $query->where('title', 'like', "%{$request->search}%")
                ->where('post_type', 'like', "%{$request->search}%");
            });
        }

        $alldata = $alldata->with(['user', 'post'])->groupBy('post_id')->paginate($limit);
        return view('admin.posts.abuse-posts-index', compact('alldata'));
    }

    public function abusePostsDestory($id)
    {
        $apost = ReportAbusePost::findorFail($id);
        $post_id = $apost->post_id;

        //abuse reports delete
        $allaposts = ReportAbusePost::where('post_id', $post_id)->delete();
        //end
        //main post delete start
        $post = Post::findorFail($post_id);
        $postmedia = $post->getPostImages;

        ini_set('memory_limit', '44M');

        if (count($postmedia) > 0) {
            foreach ($postmedia as $single) {
                $opath = explode('https://ahjili.s3.eu-central-1.amazonaws.com/', $single->path);
                $path = $opath[1] ?? "";
                if (Storage::disk('s3')->exists($path)) {
                    Storage::disk('s3')->delete($path);
                }
                $single->delete();
            }
        }
        $post->delete();
        //main post delete end
        return redirect()->route('admin.report-abuse-posts.index')->with('success', 'Role deleted successfully');
    }
}
