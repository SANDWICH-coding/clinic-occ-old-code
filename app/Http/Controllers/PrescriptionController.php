<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PrescriptionController extends Controller
{
    public function studentIndex() { return view('student.prescriptions.index'); }
    public function studentShow($id) { return view('student.prescriptions.show'); }
    public function markAsTaken($id) { return back(); }
    public function downloadPrescription($id) { return back(); }
    public function index() { return view('prescriptions.index'); }
    public function show($id) { return view('prescriptions.show'); }
    public function edit($id) { return view('prescriptions.edit'); }
    public function update(Request $request, $id) { return back(); }
    public function destroy($id) { return back(); }
    public function updateStatus(Request $request, $id) { return back(); }
    public function refillRequests($id) { return view('prescriptions.refill-requests'); }
    public function approveRefill(Request $request, $id) { return back(); }
}