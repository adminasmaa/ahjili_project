<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\API\BaseController as BaseController;
use Illuminate\Http\Request;
use Spatie\Tags\Tag;

class TagController extends BaseController
{
    public function searchTag(Request $request)
    {
        $tags=Tag::query();
        if ($request->search!='') {
            $tags->containing($request['search']);
        }
        $tags = $tags->get();

        $this->transformAllTags($tags);

        return $this->sendResponse($tags, 'All tags List retrieved successfully.');
        //dd($tags->get());
    }
}
