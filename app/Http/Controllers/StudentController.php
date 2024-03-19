<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Student;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class StudentController extends Controller
{
    public function index()
    {
        $student = Student::all();

        $data = [
            'status'=>200,
            'student'=>$student
        ];
        return response()-> json($data, 200);
    }

    public function upload(Request $request){
        $validator = Validator::make($request->all(),[
            'name'=>'required',
            'email'=>'required|email'
        ]);

        if ($validator->fails()){
            $data = [
                "status"=>422,
                "message" =>$validator->getMessageBag()
            ];
            return response()->json($data, 422);
        }

        else {
            $student = new Student();
            $student->name = $request->name;
            $student->email = $request->email;
            $student->phone = $request->phone;

            $student->save();

            $data = [
                'status'=>200,
                'message'=>'Data uploaded success'
            ];
            return  response()-> json($data, 200);
        }

    }

    public function edit(Request $request, $id)
    {
        $validator = Validator::make($request->all(),[
            'name'=>'required',
            'email'=>'required|email'
        ]);

        if ($validator->fails()){
            $data = [
                "status"=>422,
                "message" =>$validator->getMessageBag()
            ];
            return response()->json($data, 422);
        }

        else {
            $student = Student::find($id);
//            dd($student);
//            $student = DB::table('students')->select('id','name','email','phone')->where('id', $id)->get();
            $student->name = $request->name;
            $student->email = $request->email;
            $student->phone = $request->phone;

            $student->save();

            $data = [
                'status'=>200,
                'message'=>'Data updated success'
            ];
            return  response()-> json($data, 200);
        }
    }

    public function delete($id)
    {
        $student = Student::find($id);
        $student->delete();

        $data= [
            'status' => 200,
            'message' => "data deleted successfully"
        ];
        return response()->json($data,200);
    }
}
