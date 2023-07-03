<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ReportMessage;

class ReportMessageController extends Controller
{
    
    public function index(Request $request)
    {   

        $limit = $request->has('limit') ? $request->limit : 10;

        if($request->has('search') && $request->search != '')
        {
            $reportmessages = ReportMessage::where(function($q) use ($request){
                $q->where('message', 'like', '%'.$request->search.'%');
            })->paginate($limit);
        }
        else{
            $reportmessages =ReportMessage::paginate($limit);
        }

    	return view('admin.reportmessages.index',compact('reportmessages'));
    }

    public function store(Request $request)
    { 
        $this->validate($request, [
            'message' => 'string|max:255|unique:report_messages',
        ]);
    
        ReportMessage::create($request->all());

        return redirect()->route('admin.report-messages.index')->with('success', 'New Report Message created successfully');
    }

    public function update(Request $request, $id)
    {
       
        $this->validate($request, [
            'message' => 'string|max:255',
        ]);

        $reportmessage = ReportMessage::findOrFail($id);
        $reportmessage->update($request->all());

         return redirect()->route('admin.report-messages.index')->with('success', 'Role updated successfully');
    }

    public function destroy($id)
    {
        $reportmessage = ReportMessage::findOrFail($id);
        //dd($role);
        if ($reportmessage) {
            $reportmessage->delete();
        }
        
        return redirect()->route('admin.report-messages.index')->with('success', 'Role deleted successfully');
    }
}
