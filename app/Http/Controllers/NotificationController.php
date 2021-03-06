<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Repositories\NotificationRepository;
use App\Models\Level;
use Illuminate\Http\Request;
use Auth;

class NotificationController extends Controller
{
    private $notification;

	public function __construct(NotificationRepository $notification ) {
    	$this->middleware('auth');
    	// $this->middleware('lecturer');

        $this->notification = $notification;
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $data['levels'] = Level::all();
        if(Auth::user()->role != 'lecturer'){
            $conditionsGeneral = [
                    ['type', 'general'],
                ];
            $conditionsLevel = [
                    ['level_id', Auth::user()->level_id],
                    ['type', 'level'],
                ];
            $data['levelInformations'] = Notification::where($conditionsLevel)->paginate(5);
            $data['generalInformations'] = Notification::where($conditionsGeneral)->paginate(5);
            return view('students.information',$data);
        }else{
             $conditionsGeneral = [
                    ['type', 'general'],
                ];
            $conditionsLevel = [
                    ['lecturer_id', Auth::user()->lecturer->id],
                    ['type', 'level'],
                ];
            $data['levelInformations'] = Notification::where($conditionsLevel)->orderBy('created_at', 'desc')->paginate(5);
            $data['generalInformations'] = Notification::where($conditionsGeneral)->paginate(5);
            return view('students.information',$data);
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $data['levels'] = Level::all();
        return view('lecturer.notification',$data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
         $data = $request->except(['_token']);
         $notification = $this->notification->fillAndSave($data);

    	if($notification) {
    		return back()->with('message', 'Announcement Made');
	    }

	    return ['error' => 'Cannot create a class'];
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Notification  $notification
     * @return \Illuminate\Http\Response
     */
    public function show($notification)
    {
        $data = $this->notification->getByAttributes(['id' => $notification], 'AND');
        return $data;
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Notification  $notification
     * @return \Illuminate\Http\Response
     */
    public function edit($notification)
    {
        $data['levels'] = Level::all();
        $data['notification'] = Notification::findOrFail($notification);
        // return $data;
        return view('lecturer.editNotification',$data);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Notification  $notification
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $notification)
    {
         $data['levels'] = Level::all();
        if(Auth::user()->role != 'lecturer'){
            $conditionsGeneral = [
                    ['type', 'general'],
                ];
            $conditionsLevel = [
                    ['level_id', Auth::user()->level_id],
                    ['type', 'level'],
                ];
            $data['levelInformations'] = Notification::where($conditionsLevel)->paginate(5);
            $data['generalInformations'] = Notification::where($conditionsGeneral)->paginate(5);
            return view('students.information',$data);
        }else{
             $conditionsGeneral = [
                    ['type', 'general'],
                ];
            $conditionsLevel = [
                    ['lecturer_id', Auth::user()->lecturer->id],
                    ['type', 'level'],
                ];
            $data['levelInformations'] = Notification::where($conditionsLevel)->orderBy('created_at', 'desc')->paginate(5);
            $data['generalInformations'] = Notification::where($conditionsGeneral)->paginate(5);
            }
        $dataObj = $request->except(['_token', '_method']);
        $notification = Notification::findOrFail($notification);
        // return $notification;
        if($notification->update($dataObj)){
            return redirect()->route('information',$data)->with("message", "Announcement Edited Successfully");
        }
        return redirect()->route('information', $data)->with("message", "Announcement Not Edited Successfully");
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Notification  $notification
     * @return \Illuminate\Http\Response
     */
    public function destroy($notificationId)
    {
        $data = Notification::findOrFail($notificationId);

        if($data->delete()){
            return back()->with('message', 'Information Deleted');
        }else{
            return back()->with('message', 'Error while deleting');
        }
    }
}
