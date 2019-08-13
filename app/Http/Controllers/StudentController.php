<?php

namespace App\Http\Controllers;
use App\Http\Requests\StudentRequest;
use App\Jobs\SendEmailJob;
use App\Repositories\ClassRepository\ClassRepository;
use App\Repositories\Mark\MarkRepository;
use App\Repositories\Student\StudentRepository;
use App\Repositories\Subject\SubjectRepository;
use App\Repositories\User\UserRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;


class StudentController extends Controller
{
    protected $studentRepository;
    protected $classRepository;
    protected $subjectRepository;
    protected $userRepository;
    protected $markRepository;

    public function __construct(StudentRepository $studentRepository,ClassRepository $classRepository,SubjectRepository $subjectRepository,UserRepository $userRepository,MarkRepository $markRepository)
    {
        $this->studentRepository = $studentRepository;
        $this->classRepository = $classRepository;
        $this->subjectRepository = $subjectRepository;
        $this->userRepository = $userRepository;
        $this->markRepository = $markRepository;
    }

    public function index(Request $request)
    {
        $data = $request->all();
        $subjects = $this->subjectRepository->getAllList();
        $students = $this->studentRepository->searchStudent($data,count($subjects));
        return view('students.index',compact('students','data','subjects','classes'));
    }

    public function create()
    {
        $classes = $this->classRepository->getAllList()->pluck('name','id');
        return view('students.create', compact('classes'));
    }

    public function store(StudentRequest $request)
    {
        $data = $request->all();

        if ($request->hasFile('avatar')) {

            $file = upload_image('avatar');
            if (isset($file['name'])) {
                $data['avatar'] = $file['name'];
            }
        }
        $this->studentRepository->store($data);
        return redirect($request->redirects_to)->with('success', 'Create student successfully');
    }

    public function edit($id)
    {
        $classes = $this->classRepository->getAllList()->pluck('name','id');
        $student = $this->studentRepository->getListById($id);
        return view('students.edit', compact('student'),compact('classes'));
    }

    public function update($id, StudentRequest $request)
    {
        $data = $request->all();
        if ($request->hasFile('avatar')) {
            $file = upload_image('avatar');

            if (isset($file['name'])) {

            $data['avatar'] = $file['name'];
            }
        }
        $student = $this->studentRepository->getListById($id);
        $user = $student->user;
        $this->studentRepository->update($id, $data);

        $this->userRepository->update($user->id,$data);

        return redirect('students')->with('success', 'Update student successfully');
    }


    public function delete($id)
    {
        $student = $this->studentRepository->getListById($id);
        return view('students.delete', compact('student'));
    }

    public function destroy($id)
    {
        if (Gate::allows('permission', 'admin')) {
            $student = $this->studentRepository->getListById($id);
            $this->studentRepository->destroy($id);
            if(!empty($student->avatar)) {
                if(empty($this->studentRepository->checkAvatar($student->avatar))) {
                    unlink(public_path(pare_url_file($student->avatar)));}}

            return back()->with('success', 'Delete student successfully');
        }

        return back()->with('error', 'You can not delete this item');
    }

    public function search(Request $request) {

    }

    public function show($id,Request $request)
    {
        $student = $this->studentRepository->getListById($id);
        $marks = $student->marks()->with('subject')->with('student');
        if(!empty($request['subject_id']) && $request['subject_id'] !== 'all') {
            $marks->where('subject_id', $request['subject_id']);
        }

        if(!empty($request['min_mark'] ) && $request['min_mark'] !== 'all') {
            $marks->where('mark','>=',$request['min_mark']);
        }

        if(!empty($request['max_mark']) && $request['max_mark'] !== 'all') {
            $marks->where('mark','<=',$request['max_mark']);
        }
       $marks =  $marks->paginate(8);
//        $marks = $student->marks()->with('subject')->with('student')->paginate($this->paginate);
        return view('students.showMarks', compact('marks'),['id'=>$id]);
    }

    public function createMarks($id) {
        $student = $this->studentRepository->getListById($id);
        $subjects = $this->subjectRepository->getAllList();
        $marks = $student->marks()->get();
        return view('students.createMark',compact('subjects','student','marks'));
    }

    public function badStudents() {
        $students = $this->studentRepository->badStudents();
        return view('students.sendEmail')->with(compact('students'));
    }

    public function mailStudent($id) {
        $user = $this->userRepository->getListById($id);

        dispatch(new SendEmailJob($user));

        return redirect()->back()->with('success','Done');

    }

    public function sendAll() {
        $students = $this->studentRepository->badStudents();

        foreach ($students as $key => $student) {

            dispatch(new SendEmailJob($student->user));

        }

        return redirect()->back()->with('success','Done');
    }
}
