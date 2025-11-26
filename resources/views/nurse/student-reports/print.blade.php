<!DOCTYPE html>
<html>
<head>
    <title>Complete Student Health Report - {{ $student->full_name }}</title>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            margin: 20px; 
            line-height: 1.4;
        }
        .header { 
            text-align: center; 
            margin-bottom: 30px; 
            border-bottom: 2px solid #333; 
            padding-bottom: 20px; 
        }
        .logo-container {
            text-align: center;
            margin-bottom: 15px;
        }
        .logo { 
            max-width: 80px; 
            max-height: 80px; 
            display: block;
            margin: 0 auto;
        }
        .header-text { 
            flex: 1; 
        }
        .section { 
            margin-bottom: 25px; 
            page-break-inside: avoid;
        }
        .section-title { 
            background-color: #f8f9fa; 
            padding: 10px; 
            font-weight: bold; 
            border-left: 4px solid #007bff;
            margin-bottom: 15px;
        }
        table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-top: 10px; 
            margin-bottom: 20px;
            font-size: 12px;
        }
        th, td { 
            border: 1px solid #ddd; 
            padding: 8px; 
            text-align: left; 
            vertical-align: top;
        }
        th { 
            background-color: #f8f9fa; 
            font-weight: bold;
        }
        .stats-grid { 
            display: grid; 
            grid-template-columns: repeat(4, 1fr); 
            gap: 15px; 
            margin-bottom: 20px; 
        }
        .stat-card { 
            border: 1px solid #ddd; 
            padding: 15px; 
            text-align: center; 
        }
        .footer { 
            margin-top: 50px; 
            text-align: center; 
            font-size: 12px; 
            color: #666; 
        }
        .medical-alert {
            background-color: #fff3cd;
            border: 1px solid #ffeaa7;
            padding: 10px;
            margin: 10px 0;
            border-radius: 4px;
        }
        .emergency-badge {
            background-color: #dc3545;
            color: white;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 10px;
            margin-left: 5px;
        }
        .status-badge {
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 10px;
            margin: 0 2px;
        }
        .completed { background-color: #28a745; color: white; }
        .pending { background-color: #ffc107; color: black; }
        .cancelled { background-color: #dc3545; color: white; }
        .follow-up { background-color: #17a2b8; color: white; }
        
        @media print {
            .no-print { display: none; }
            body { margin: 15px; }
            .section { page-break-inside: avoid; }
            .page-break { page-break-before: always; }
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="logo-container">
            <img src="https://scontent.fmnl13-4.fna.fbcdn.net/v/t39.30808-6/510989554_1291512086313864_1697600367017083003_n.jpg?_nc_cat=107&ccb=1-7&_nc_sid=6ee11a&_nc_ohc=ufmPlgigja8Q7kNvwF_30_x&_nc_oc=AdlrVYVNZAzO_DGs6ZEPjA4md988p2i11ak9QgVeBj4BwvzoytYbd1KnPkJEmid5YjyY_JwK9qwtz50ZYfdvBf8K&_nc_zt=23&_nc_ht=scontent.fmnl13-4.fna&_nc_gid=rMT0DBrkO1xJIQoTq41-fg&oh=00_AfdSCdTcOmyUMkEIoOJWxolN_Vw123IzII1EZrXdG8omQg&oe=68F5CA67" 
                 alt="OPOL COMMUNITY COLLEGE Logo" class="logo">
        </div>
        <div class="header-text">
            <h1>OPOL COMMUNITY COLLEGE</h1>
            <h2>Complete Student Health History Report</h2>
            <h3>{{ $student->full_name }} ({{ $student->student_id }})</h3>
            <p>Generated on: {{ now()->format('F j, Y g:i A') }}</p>
        </div>
    </div>

    <!-- Student Basic Information -->
    <div class="section">
        <div class="section-title">Student Basic Information</div>
        <table>
            <tr>
                <th width="25%">Full Name</th>
                <td width="25%">{{ $student->full_name }}</td>
                <th width="25%">Student ID</th>
                <td width="25%">{{ $student->student_id }}</td>
            </tr>
            <tr>
                <th>Email</th>
                <td>{{ $student->email }}</td>
                <th>Phone Number</th>
                <td>{{ $student->phone_number ?? 'N/A' }}</td>
            </tr>
            <tr>
                <th>Course & Year</th>
                <td>{{ $student->course ?? 'N/A' }} - Year {{ $student->year_level ?? 'N/A' }}</td>
                <th>Date of Birth</th>
                <td>
                    @if($student->date_of_birth)
                        {{ \Carbon\Carbon::parse($student->date_of_birth)->format('M j, Y') }}
                        ({{ \Carbon\Carbon::parse($student->date_of_birth)->age }} years old)
                    @else
                        N/A
                    @endif
                </td>
            </tr>
            <tr>
                <th>Gender</th>
                <td>{{ $student->gender ? ucfirst($student->gender) : 'N/A' }}</td>
                <th>Address</th>
                <td>{{ $student->address ?? 'N/A' }}</td>
            </tr>
        </table>
    </div>

    <!-- Health Statistics Overview -->
    <div class="section">
        <div class="section-title">Health Statistics Overview</div>
        <div class="stats-grid">
            <div class="stat-card">
                <h3>{{ $healthStats['total_consultations'] }}</h3>
                <p>Total Consultations</p>
            </div>
            <div class="stat-card">
                <h3>{{ $healthStats['total_symptoms_logged'] }}</h3>
                <p>Symptoms Logged</p>
            </div>
            <div class="stat-card">
                <h3>{{ $healthStats['total_appointments'] }}</h3>
                <p>Appointments</p>
            </div>
            <div class="stat-card">
                <h3>{{ $healthStats['emergency_cases'] }}</h3>
                <p>Emergency Cases</p>
            </div>
            <div class="stat-card">
                <h3>{{ $healthStats['recent_consultations'] }}</h3>
                <p>Recent Consultations (30 days)</p>
            </div>
            <div class="stat-card">
                <h3>{{ $healthStats['recent_symptoms_logged'] }}</h3>
                <p>Recent Symptoms (30 days)</p>
            </div>
            <div class="stat-card">
                <h3>{{ $healthStats['follow_up_needed'] }}</h3>
                <p>Follow-ups Needed</p>
            </div>
            <div class="stat-card">
                <h3>{{ $healthStats['medical_record_completion'] }}%</h3>
                <p>Record Complete</p>
            </div>
        </div>
        
        <table>
            <tr>
                <th>Health Risk Level</th>
                <td><strong>{{ $healthStats['health_risk_level'] ?? 'Unknown' }}</strong></td>
                <th>Average Pain Level</th>
                <td>{{ $healthStats['average_pain_level'] ?? 0 }}/10</td>
            </tr>
            <tr>
                <th>BMI</th>
                <td>
                    @if($healthStats['bmi'])
                        {{ number_format($healthStats['bmi'], 1) }} 
                        ({{ $healthStats['bmi_category']['category'] ?? 'Unknown' }})
                    @else
                        Not calculated
                    @endif
                </td>
                <th>Common Symptoms</th>
                <td>
                    @if(!empty($healthStats['common_symptoms']))
                        @foreach($healthStats['common_symptoms'] as $symptom => $count)
                            {{ $symptom }} ({{ $count }})@if(!$loop->last), @endif
                        @endforeach
                    @else
                        No common symptoms recorded
                    @endif
                </td>
            </tr>
        </table>
    </div>

    <!-- Medical Record Details -->
    <div class="section">
        <div class="section-title">Medical Record Details</div>
        @if($student->medicalRecord)
        <table>
            <tr>
                <th width="20%">Blood Type</th>
                <td width="30%">{{ $student->medicalRecord->blood_type ?? 'Not specified' }}</td>
                <th width="20%">Height</th>
                <td width="30%">{{ $student->medicalRecord->height ? $student->medicalRecord->height . ' cm' : 'Not specified' }}</td>
            </tr>
            <tr>
                <th>Weight</th>
                <td>{{ $student->medicalRecord->weight ? $student->medicalRecord->weight . ' kg' : 'Not specified' }}</td>
                <th>Blood Pressure</th>
                <td>{{ $student->medicalRecord->blood_pressure ?? 'Not recorded' }}</td>
            </tr>
            <tr>
                <th>Vaccination Status</th>
                <td colspan="3">
                    @if($student->medicalRecord->immunization_history)
                        Recorded
                    @else
                        Not specified
                    @endif
                </td>
            </tr>
        </table>

        <!-- Medical Alerts -->
        @if($student->medicalRecord->allergies || $student->medicalRecord->chronic_conditions || $student->medicalRecord->current_medications)
        <div style="margin-top: 15px;">
            <h4>Medical Alerts & Conditions</h4>
            @if($student->medicalRecord->allergies)
            <div class="medical-alert">
                <strong>Allergies:</strong> {{ $student->medicalRecord->allergies }}
            </div>
            @endif
            @if($student->medicalRecord->chronic_conditions)
            <div class="medical-alert">
                <strong>Chronic Conditions:</strong> {{ $student->medicalRecord->chronic_conditions }}
            </div>
            @endif
            @if($student->medicalRecord->current_medications && $student->medicalRecord->is_taking_maintenance_drugs)
            <div class="medical-alert">
                <strong>Current Medications:</strong> {{ $student->medicalRecord->current_medications }}
            </div>
            @endif
            @if($student->medicalRecord->family_medical_history)
            <div style="margin-top: 10px;">
                <strong>Family Medical History:</strong> {{ $student->medicalRecord->family_medical_history }}
            </div>
            @endif
        </div>
        @endif

        @else
        <p>No medical record found for this student.</p>
        @endif
    </div>

    <!-- Emergency Contacts -->
    <div class="section">
        <div class="section-title">Emergency Contacts</div>
        @if($emergencyContacts->count() > 0)
        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Relationship</th>
                    <th>Phone</th>
                   
                </tr>
            </thead>
            <tbody>
                @foreach($emergencyContacts as $contact)
                <tr>
                    <td>
                        {{ $contact['name'] ?? $contact['full_name'] ?? 'N/A' }}
                        @if($contact['is_primary'] ?? false)
                        <span class="status-badge completed">Primary</span>
                        @endif
                    </td>
                    <td>{{ $contact['relationship'] ?? 'Not specified' }}</td>
                    <td>{{ $contact['phone'] ?? $contact['phone_number'] ?? 'Not specified' }}</td>
                    
                </tr>
                @endforeach
            </tbody>
        </table>
        @else
        <p>No emergency contacts registered.</p>
        @endif
    </div>

    <!-- Symptom History -->
    <div class="section page-break">
        <div class="section-title">Symptom History ({{ $symptomLogs->count() }} records)</div>
        @if($symptomLogs->count() > 0)
        <table>
            <thead>
                <tr>
                    <th width="15%">Date & Time</th>
                    <th width="25%">Symptoms</th>
                    <th width="10%">Severity</th>
                    <th width="15%">Status</th>
                    <th width="20%">Duration</th>
                    <th width="15%">Details</th>
                </tr>
            </thead>
            <tbody>
                @foreach($symptomLogs as $symptom)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($symptom->logged_at)->format('M j, Y g:i A') }}</td>
                    <td>
                        @if(is_array($symptom->symptoms))
                            {{ implode(', ', $symptom->symptoms) }}
                        @else
                            {{ $symptom->symptoms ?? 'No symptoms specified' }}
                        @endif
                        @if($symptom->is_emergency)
                        <span class="emergency-badge">EMERGENCY</span>
                        @endif
                    </td>
                    <td>
                        @if($symptom->severity_rating)
                        {{ $symptom->severity_rating }}/10
                        @else
                        N/A
                        @endif
                    </td>
                    <td>
                        <span class="status-badge {{ $symptom->status === 'resolved' ? 'completed' : ($symptom->status === 'under_review' ? 'pending' : 'follow-up') }}">
                            {{ ucfirst($symptom->status) }}
                        </span>
                    </td>
                    <td>{{ $symptom->duration ?? 'N/A' }}</td>
                    <td>
                        @if($symptom->description)
                        {{ Str::limit($symptom->description, 50) }}
                        @else
                        No details
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @else
        <p>No symptom logs found.</p>
        @endif
    </div>

    <!-- Consultation History -->
    <div class="section">
        <div class="section-title">Consultation History ({{ $consultations->count() }} records)</div>
        @if($consultations->count() > 0)
        <table>
            <thead>
                <tr>
                    <th width="12%">Date</th>
                    <th width="20%">Chief Complaint</th>
                    <th width="10%">Type</th>
                    <th width="10%">Status</th>
                    <th width="15%">Diagnosis</th>
                    <th width="15%">Vital Signs</th>
                    <th width="18%">Assessment</th>
                </tr>
            </thead>
            <tbody>
                @foreach($consultations as $consultation)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($consultation->consultation_date)->format('M j, Y') }}</td>
                    <td>{{ $consultation->chief_complaint ?? 'No chief complaint' }}</td>
                    <td>
                        <span class="status-badge {{ $consultation->type === 'emergency' ? 'cancelled' : 'completed' }}">
                            {{ ucfirst($consultation->type) }}
                        </span>
                    </td>
                    <td>
                        <span class="status-badge {{ $consultation->status === 'completed' ? 'completed' : ($consultation->status === 'follow_up' ? 'follow-up' : 'pending') }}">
                            {{ ucfirst($consultation->status) }}
                        </span>
                    </td>
                    <td>{{ $consultation->diagnosis ? Str::limit($consultation->diagnosis, 30) : 'N/A' }}</td>
                    <td>
                        @if($consultation->temperature || $consultation->blood_pressure_systolic)
                            @if($consultation->temperature) T:{{ $consultation->temperature }}°C @endif
                            @if($consultation->blood_pressure_systolic) BP:{{ $consultation->blood_pressure_systolic }}/{{ $consultation->blood_pressure_diastolic }} @endif
                        @else
                            N/A
                        @endif
                    </td>
                    <td>{{ $consultation->assessment ? Str::limit($consultation->assessment, 40) : 'N/A' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @else
        <p>No consultations found.</p>
        @endif
    </div>

    <!-- Appointment History -->
    <div class="section page-break">
        <div class="section-title">Appointment History ({{ $appointments->count() }} records)</div>
        @if($appointments->count() > 0)
        <table>
            <thead>
                <tr>
                    <th width="12%">Date</th>
                    <th width="15%">Time</th>
                    <th width="25%">Reason</th>
                    <th width="15%">Type</th>
                    <th width="15%">Status</th>
                    <th width="18%">Notes</th>
                </tr>
            </thead>
            <tbody>
                @foreach($appointments as $appointment)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($appointment->appointment_date)->format('M j, Y') }}</td>
                    <td>
                        @if($appointment->appointment_time)
                            {{ \Carbon\Carbon::parse($appointment->appointment_time)->format('g:i A') }}
                        @else
                            N/A
                        @endif
                    </td>
                    <td>{{ $appointment->reason ?? 'No reason specified' }}</td>
                    <td>{{ ucfirst($appointment->appointment_type ?? 'general') }}</td>
                    <td>
                        <span class="status-badge {{ $appointment->status === 'completed' ? 'completed' : ($appointment->status === 'cancelled' ? 'cancelled' : 'pending') }}">
                            {{ ucfirst($appointment->status) }}
                        </span>
                    </td>
                    <td>{{ $appointment->notes ? Str::limit($appointment->notes, 30) : 'No notes' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @else
        <p>No appointments found.</p>
        @endif
    </div>

    <!-- Vital Signs History -->
    <div class="section">
        <div class="section-title">Vital Signs History</div>
        @if($vitalSigns->count() > 0)
        <table>
            <thead>
                <tr>
                    <th width="15%">Date</th>
                    <th width="15%">Temperature (°C)</th>
                    <th width="15%">Blood Pressure</th>
                    <th width="15%">Heart Rate (bpm)</th>
                    <th width="15%">Respiratory Rate</th>
                    <th width="15%">Oxygen Saturation (%)</th>
                </tr>
            </thead>
            <tbody>
                @foreach($vitalSigns as $vital)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($vital['date'])->format('M j, Y') }}</td>
                    <td>{{ $vital['temperature'] ?? 'N/A' }}</td>
                    <td>{{ $vital['blood_pressure'] ?? 'N/A' }}</td>
                    <td>{{ $vital['heart_rate'] ?? 'N/A' }}</td>
                    <td>{{ $vital['respiratory_rate'] ?? 'N/A' }}</td>
                    <td>{{ $vital['oxygen_saturation'] ?? 'N/A' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @else
        <p>No vital signs recorded.</p>
        @endif
    </div>

    <div class="footer">
        <p>Confidential Health Report - For authorized medical personnel only</p>
        <p>Generated by: {{ auth()->user()->full_name }} on {{ now()->format('F j, Y g:i A') }}</p>
        <p>Page 1 of {{ $vitalSigns->count() > 0 || $appointments->count() > 0 ? 'multiple' : '1' }}</p>
    </div>

    <div class="no-print text-center mt-4">
        <button onclick="window.print()" class="btn btn-primary">Print Complete Report</button>
        <button onclick="window.close()" class="btn btn-secondary">Close Window</button>
    </div>
</body>
</html>