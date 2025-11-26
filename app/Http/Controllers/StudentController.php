<?php


namespace App\Http\Controllers\Nurse;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class StudentController extends Controller
{
    public function index()
    {
        $students = User::where('role', 'student')->paginate(10);
        return view('nurse.students.index', compact('students'));
    }

    public function search(Request $request)
    {
        $query = $request->input('q');

        $students = User::where('role', 'student')
            ->where(function ($q) use ($query) {
                $q->where('first_name', 'like', "%{$query}%")
                  ->orWhere('last_name', 'like', "%{$query}%")
                  ->orWhere('student_id', 'like', "%{$query}%");
            })
            ->paginate(10);

        // Keep query string in pagination
        $students->appends(['q' => $query]);

        return view('nurse.students.index', compact('students', 'query'));
    }
}
