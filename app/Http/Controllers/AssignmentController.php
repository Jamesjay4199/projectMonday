<?php

namespace App\Http\Controllers;

use App\Models\Scoresheet;
use App\Repositories\AssignmentRepository;
use App\Repositories\AssignmentSumissionRepository;
use App\Repositories\ClassRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Ramsey\Uuid\Uuid;
use App\Models\Level;
use App\Models\StudentClass;
use App\Models\Assignment;

class AssignmentController extends Controller
{
	private $assignment;
    private $class;

	public function __construct(AssignmentRepository $assignment, ClassRepository $class, AssignmentSumissionRepository $subassignment)
	{
		$this->middleware('auth');
		$this->assignment = $assignment;
		$this->subassignment = $subassignment;
		$this->class = $class;
	}

    public function createAssignment()
	{
		$data['classes'] = $this->class->getByAttributes(['lecturer_id' => Auth::user()->lecturer->id], 'AND');
		return view('lecturer.create-assignment', $data);
    }

    public function storeAssignment(Request $request)
	{
        $data = $request->except(['_token']);
		$assignment = $this->assignment->fillAndSave($data);

		if ($assignment) {
			return back()->with('message', 'Assignment Created');
		}

		return back();
    }


    public function saveAssignment($id, Request $request)
	{
        //  return Auth::user()->student->reg_number;
		$assignment = $this->assignment->find($id);

		$data['filename'] = $request->assignmentFile->storeAs('public/assignments', $assignment->class->lecturer->user->name.'/'.$assignment->class->name.'\'s class/'.$assignment->title.'/'.$assignment->title. '-' . Auth::user()->name. '.pdf');
		$data['assignment_id'] = $id;
		$data['submitted'] = 1;
		$data['student_id'] = Auth::user()->student->id;
        $data['id'] = Uuid::uuid1();
		$submitted = $assignment->subscribers()->create($data);
        if ($submitted)
        {
            $score = new Scoresheet();
            $score->lecturer_id = $assignment->class->lecturer->id;
            $score->student_id = Auth::user()->student->id;
            $score->assignment_subscription_id = $submitted->id;
            $score->save();

			return redirect()->route('show.class', $assignment->class->id)->with("message", "Assignment Submitted Successfully");
		}

		return ['error' => 'Unable to Submit Assignment'];
	}

    public function submitAssignment($id)
	{
		$assignment = $this->assignment->find($id, ['class']);
        $data['assignment'] = $assignment;
        $data['now'] = \Carbon\Carbon::now();
        // return $data;
		return view('students.submit-assignment', $data);
    }


    public function viewAssignmentsPerClass($id)
    {
        $data['assignments'] = Assignment::where('class_id', $id)->orderBy('created_at', 'desc')->paginate(2);
        $data['studentsInClass'] = StudentClass::where('class_id', $id)->get()->count();
        $data['studentInClass'] = StudentClass::where('class_id', $id)->first();
        // return $data;
        return view('lecturer.assignmentListing',$data);
    }

    public function viewAssignmentSubmissions($id)
    {
        $data['subassignments'] = $this->subassignment->getByAttributes(['assignment_id' => $id], 'AND');
        return view('lecturer.submittedAssignmentList', $data);
    }

    public function deleteAssignment($id)
    {
        $assignment = Assignment::findOrFail($id);
        $submitted = $assignment->subscribers;
        // $assignments = $this->assignment->getByAttributes(['class_id' => $id], 'AND');
        foreach($submitted as $submit)
        {
            $submit->delete();
        }
        $assignment->delete();
        return back()->with('message', 'Assignment Deleted');
    }
    public function editClassAssignments($id){
        $data['assignment'] = Assignment::findOrFail($id);
        return view("lecturer.editAssignment",$data);
    }
    public function updateClassAssignments(Request $request, $id){
        $assignment = Assignment::findOrFail($id);
        // return $assignment;
        $requestObj = $request->except(['_token', '_method']);
        // return $data;
		if($assignment->update($requestObj)){
            return redirect()->route('show.assignment', $assignment->class->id)->with("message", "Assignment Edited Successfully");
        }
        return redirect()->route('show.assignment', $assignment->class->id)->with("message", "Assignment Not Edited Successfully");
    }
}
