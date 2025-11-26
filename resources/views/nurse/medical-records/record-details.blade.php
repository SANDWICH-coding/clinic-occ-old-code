{{-- resources/views/medical-records/nurse/record-details.blade.php --}}
@extends('layouts.nurse-app')

@section('title', 'Student Medical Record - ' . $user->full_name)

@section('content')
<div class="container-fluid py-4">
    <!-- Header Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0">Student Medical Record</h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('nurse.dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('nurse.medical-records.index') }}">Medical Records</a></li>
                            <li class="breadcrumb-item active">{{ $user->full_name }}</li>
                        </ol>
                    </nav>
                </div>
                <div class="btn-group">
                    <a href="{{ route('nurse.medical-records.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-1"></i> Back to Records
                    </a>
                    @if($user->medicalRecord)
                        <a href="{{ route('nurse.medical-records.edit', $user->medicalRecord) }}" class="btn btn-primary">
                            <i class="fas fa-edit me-1"></i> Edit Record
                        </a>
                        <button type="button" class="btn btn-success" onclick="printRecord()">
                            <i class="fas fa-print me-1"></i> Print
                        </button>
                    @else
                        <a href="{{ route('nurse.medical-records.create') }}?student_id={{ $user->id }}" class="btn btn-success">
                            <i class="fas fa-plus me-1"></i> Create Record
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Student Overview Card -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-primary">
                <div class="card-header bg-primary text-white">
                    <div class="row align-items-center">
                        <div class="col">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-user-injured me-2"></i>Patient Information
                            </h5>
                        </div>
                        <div class="col-auto">
                            @if($user->medicalRecord)
                                <span class="badge bg-success fs-6">Record Complete</span>
                            @else
                                <span class="badge bg-warning fs-6">No Record</span>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td class="fw-bold" width="30%">Full Name:</td>
                                    <td>{{ $user->full_name }}</td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">Student ID:</td>
                                    <td><span class="badge bg-secondary">{{ $user->student_id }}</span></td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">Email:</td>
                                    <td>{{ $user->email }}</td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">Phone:</td>
                                    <td>{{ $user->formatted_phone }}</td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">Age:</td>
                                    <td>{{ $user->age ?? 'Not provided' }} years old</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td class="fw-bold" width="30%">Course:</td>
                                    <td>{{ $user->course ?? 'Not specified' }}</td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">Year Level:</td>
                                    <td>{{ $user->year_level ?? 'Not specified' }}</td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">Section:</td>
                                    <td>{{ $user->section ?? 'Not specified' }}</td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">Academic Info:</td>
                                    <td>{{ $user->academic_info ?? 'Not available' }}</td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">Gender:</td>
                                    <td>{{ ucfirst(str_replace('_', ' ', $user->gender ?? 'Not specified')) }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if($user->medicalRecord)
        <!-- Health Status Overview -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card text-white bg-info h-100">
                    <div class="card-body text-center">
                        <i class="fas fa-chart-pie fa-2x mb-2"></i>
                        <h5>Record Completion</h5>
                        <h3>{{ $stats['record_completion'] }}%</h3>
                        <div class="progress bg-light">
                            <div class="progress-bar" style="width: {{ $stats['record_completion'] }}%"></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-white 
                    {{ $stats['health_risk_level'] === 'High' ? 'bg-danger' : 
                       ($stats['health_risk_level'] === 'Medium' ? 'bg-warning' : 'bg-success') }} h-100">
                    <div class="card-body text-center">
                        <i class="fas fa-heartbeat fa-2x mb-2"></i>
                        <h5>Risk Level</h5>
                        <h3>{{ $stats['health_risk_level'] }}</h3>
                        <small>Based on health conditions</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-white 
                    {{ $user->medicalRecord->is_fully_vaccinated ? 'bg-success' : 'bg-warning' }} h-100">
                    <div class="card-body text-center">
                        <i class="fas fa-syringe fa-2x mb-2"></i>
                        <h5>Vaccination</h5>
                        <h3>{{ $user->medicalRecord->is_fully_vaccinated ? 'Complete' : 'Incomplete' }}</h3>
                        <small>COVID-19 Vaccination</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-white bg-secondary h-100">
                    <div class="card-body text-center">
                        <i class="fas fa-clock fa-2x mb-2"></i>
                        <h5>Last Updated</h5>
                        <h6>{{ $stats['last_updated'] ? $stats['last_updated']->diffForHumans() : 'Never' }}</h6>
                        <small>Record modification</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Medical Information Grid -->
        <div class="row">
            <!-- Basic Vitals -->
            <div class="col-lg-6 mb-4">
                <div class="card h-100">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">
                            <i class="fas fa-thermometer-half me-2 text-danger"></i>
                            Basic Health Metrics
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="border rounded p-3 text-center">
                                    <h6 class="text-muted mb-1">Blood Type</h6>
                                    <h4 class="text-danger mb-0">
                                        {{ $user->medicalRecord->blood_type ?? 'Not specified' }}
                                    </h4>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="border rounded p-3 text-center">
                                    <h6 class="text-muted mb-1">Height</h6>
                                    <h4 class="text-primary mb-0">
                                        {{ $user->medicalRecord->height ? $user->medicalRecord->height . ' cm' : 'Not recorded' }}
                                    </h4>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="border rounded p-3 text-center">
                                    <h6 class="text-muted mb-1">Weight</h6>
                                    <h4 class="text-success mb-0">
                                        {{ $user->medicalRecord->weight ? $user->medicalRecord->weight . ' kg' : 'Not recorded' }}
                                    </h4>
                                </div>
                            </div>
                            <div class="col-md-6">
                                @if($user->medicalRecord->calculateBMI())
                                    <div class="border rounded p-3 text-center">
                                        <h6 class="text-muted mb-1">BMI</h6>
                                        <h4 class="{{ $user->medicalRecord->getBMIStatusColor() }} mb-0">
                                            {{ $user->medicalRecord->calculateBMI() }}
                                        </h4>
                                        <small class="text-muted">{{ $user->medicalRecord->getBMICategory() }}</small>
                                    </div>
                                @else
                                    <div class="border rounded p-3 text-center">
                                        <h6 class="text-muted mb-1">BMI</h6>
                                        <h4 class="text-muted mb-0">N/A</h4>
                                        <small class="text-muted">Insufficient data</small>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

           <!-- Emergency Contacts -->
<div class="col-lg-6 mb-4">
    <div class="card h-100">
        <div class="card-header bg-light">
            <h5 class="mb-0">
                <i class="fas fa-phone-alt me-2 text-warning"></i>
                Emergency Contacts
            </h5>
        </div>
        <div class="card-body">
            @if($user->hasEmergencyContacts())
                @php
                    $contacts = [];
                    if ($user->medicalRecord->emergency_contact_name_1) {
                        $contacts[] = [
                            'type' => 'primary',
                            'label' => 'Primary Contact',
                            'name' => $user->medicalRecord->emergency_contact_name_1,
                            'number' => $user->medicalRecord->emergency_contact_number_1,
                            'relationship' => $user->medicalRecord->emergency_contact_relationship_1
                        ];
                    }
                    if ($user->medicalRecord->emergency_contact_name_2) {
                        $contacts[] = [
                            'type' => 'secondary',
                            'label' => 'Secondary Contact',
                            'name' => $user->medicalRecord->emergency_contact_name_2,
                            'number' => $user->medicalRecord->emergency_contact_number_2,
                            'relationship' => $user->medicalRecord->emergency_contact_relationship_2
                        ];
                    }
                @endphp
                @foreach($contacts as $contact)
                    <div class="alert alert-light border-start border-4 
                        {{ $contact['type'] === 'primary' ? 'border-primary' : 'border-secondary' }} mb-3">
                        <div class="d-flex align-items-center mb-2">
                            <i class="fas fa-user-friends me-2"></i>
                            <strong>{{ $contact['label'] }}</strong>
                            @if($contact['relationship'])
                                <span class="badge bg-info ms-2">{{ $contact['relationship'] }}</span>
                            @endif
                        </div>
                        <div class="ms-3">
                            <div><i class="fas fa-user me-2"></i>{{ $contact['name'] }}</div>
                            <div><i class="fas fa-phone me-2"></i>{{ $contact['number'] }}</div>
                        </div>
                    </div>
                @endforeach
            @else
                <div class="text-center py-4">
                    <i class="fas fa-exclamation-triangle fa-2x text-warning mb-2"></i>
                    <p class="text-muted">No emergency contacts available</p>
                    <small class="text-danger">Please update the medical record with emergency contact information</small>
                </div>
            @endif
        </div>
    </div>
</div>

            <!-- Health Conditions -->
            <div class="col-lg-6 mb-4">
                <div class="card h-100">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">
                            <i class="fas fa-stethoscope me-2 text-info"></i>
                            Health Conditions & Allergies
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <h6 class="text-muted">Allergies</h6>
                            @if($user->medicalRecord->allergies)
                                <div class="alert alert-warning">
                                    <i class="fas fa-exclamation-triangle me-1"></i>
                                    {{ $user->medicalRecord->allergies }}
                                </div>
                            @else
                                <p class="text-success"><i class="fas fa-check me-1"></i> No known allergies</p>
                            @endif
                        </div>

                        <div class="mb-3">
                            <h6 class="text-muted">Past Illnesses</h6>
                            <p>{{ $user->medicalRecord->past_illnesses ?? 'None reported' }}</p>
                        </div>

                        <div class="row">
                            <div class="col-12">
                                <h6 class="text-muted">Special Conditions</h6>
                                <div class="d-flex flex-wrap gap-2">
                                    @if($user->medicalRecord->is_pwd)
                                        <span class="badge bg-info">PWD</span>
                                    @endif
                                    @if($user->medicalRecord->has_been_pregnant)
                                        <span class="badge bg-secondary">Pregnancy History</span>
                                    @endif
                                    @if($user->medicalRecord->has_undergone_surgery)
                                        <span class="badge bg-warning">Surgery History</span>
                                    @endif
                                    @if($user->medicalRecord->is_taking_maintenance_drugs)
                                        <span class="badge bg-danger">On Maintenance Drugs</span>
                                    @endif
                                    @if($user->medicalRecord->has_been_hospitalized_6_months)
                                        <span class="badge bg-dark">Recent Hospitalization</span>
                                    @endif
                                    @if(!$user->medicalRecord->is_pwd && !$user->medicalRecord->has_been_pregnant && 
                                        !$user->medicalRecord->has_undergone_surgery && !$user->medicalRecord->is_taking_maintenance_drugs && 
                                        !$user->medicalRecord->has_been_hospitalized_6_months)
                                        <span class="text-success">No special conditions reported</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Vaccination Information -->
            <div class="col-lg-6 mb-4">
                <div class="card h-100">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">
                            <i class="fas fa-syringe me-2 text-success"></i>
                            COVID-19 Vaccination Status
                        </h5>
                    </div>
                    <div class="card-body">
                        @php $vaccination = $user->medicalRecord->getVaccinationStatus() @endphp
                        
                        <div class="row g-3">
                            <div class="col-12">
                                <div class="d-flex align-items-center justify-content-between mb-3">
                                    <span class="fw-bold">Vaccination Status:</span>
                                    @if($vaccination['is_vaccinated'])
                                        <span class="badge bg-success fs-6">Fully Vaccinated</span>
                                    @else
                                        <span class="badge bg-danger fs-6">Not Vaccinated</span>
                                    @endif
                                </div>
                            </div>

                            @if($vaccination['is_vaccinated'])
                                <div class="col-md-6">
                                    <div class="border rounded p-3">
                                        <h6 class="text-muted mb-1">Vaccine Type</h6>
                                        <p class="mb-0">{{ $vaccination['vaccine_type'] ?? 'Not specified' }}</p>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="border rounded p-3">
                                        <h6 class="text-muted mb-1">Doses Received</h6>
                                        <p class="mb-0">{{ $vaccination['doses'] ?? 'Not specified' }}</p>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="border rounded p-3">
                                        <h6 class="text-muted mb-1">Booster Status</h6>
                                        @if($vaccination['has_booster'])
                                            <span class="badge bg-success">Received</span>
                                            @if($vaccination['booster_count'])
                                                <small class="text-muted">({{ $vaccination['booster_count'] }} doses)</small>
                                            @endif
                                        @else
                                            <span class="badge bg-warning">Not Received</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="border rounded p-3">
                                        <h6 class="text-muted mb-1">Vaccination Date</h6>
                                        <p class="mb-0">{{ $vaccination['vaccine_date'] ? $vaccination['vaccine_date']->format('M j, Y') : 'Not recorded' }}</p>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Medical History Details -->
            @if($user->medicalRecord->surgery_details || $user->medicalRecord->hospitalization_details_6_months || $user->medicalRecord->maintenance_drugs_specify)
            <div class="col-12 mb-4">
                <div class="card">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">
                            <i class="fas fa-history me-2 text-secondary"></i>
                            Detailed Medical History
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            @if($user->medicalRecord->surgery_details)
                            <div class="col-md-4 mb-3">
                                <div class="alert alert-info">
                                    <h6><i class="fas fa-cut me-1"></i> Surgery History</h6>
                                    <p class="mb-0">{{ $user->medicalRecord->surgery_details }}</p>
                                </div>
                            </div>
                            @endif
                            
                            @if($user->medicalRecord->hospitalization_details_6_months)
                            <div class="col-md-4 mb-3">
                                <div class="alert alert-warning">
                                    <h6><i class="fas fa-hospital me-1"></i> Recent Hospitalization</h6>
                                    <p class="mb-0">{{ $user->medicalRecord->hospitalization_details_6_months }}</p>
                                </div>
                            </div>
                            @endif
                            
                            @if($user->medicalRecord->maintenance_drugs_specify)
                            <div class="col-md-4 mb-3">
                                <div class="alert alert-secondary">
                                    <h6><i class="fas fa-pills me-1"></i> Maintenance Medications</h6>
                                    <p class="mb-0">{{ $user->medicalRecord->maintenance_drugs_specify }}</p>
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <!-- Additional Information -->
            @if($user->medicalRecord->notes_health_problems || $user->medicalRecord->family_history_details || $user->medicalRecord->pwd_disability_details)
            <div class="col-12 mb-4">
                <div class="card">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">
                            <i class="fas fa-notes-medical me-2 text-primary"></i>
                            Additional Notes & Information
                        </h5>
                    </div>
                    <div class="card-body">
                        @if($user->medicalRecord->notes_health_problems)
                            <div class="mb-3">
                                <h6 class="text-muted">Health Problems & Concerns</h6>
                                <div class="bg-light p-3 rounded">
                                    {{ $user->medicalRecord->notes_health_problems }}
                                </div>
                            </div>
                        @endif
                        
                        @if($user->medicalRecord->family_history_details)
                            <div class="mb-3">
                                <h6 class="text-muted">Family Medical History</h6>
                                <div class="bg-light p-3 rounded">
                                    {{ $user->medicalRecord->family_history_details }}
                                </div>
                            </div>
                        @endif

                        @if($user->medicalRecord->pwd_disability_details)
                            <div class="mb-3">
                                <h6 class="text-muted">Disability Information</h6>
                                <div class="bg-light p-3 rounded">
                                    <strong>PWD ID:</strong> {{ $user->medicalRecord->pwd_id ?? 'Not provided' }}<br>
                                    <strong>Details:</strong> {{ $user->medicalRecord->pwd_disability_details }}
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
            @endif

            <!-- Record Information -->
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">
                            <i class="fas fa-info-circle me-2 text-info"></i>
                            Record Metadata
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <table class="table table-borderless">
                                    <tr>
                                        <td class="fw-bold" width="40%">Record Created:</td>
                                        <td>{{ $user->medicalRecord->formatted_created_date }}</td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">Last Updated:</td>
                                        <td>{{ $user->medicalRecord->formatted_updated_date }}</td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">Created By:</td>
                                        <td>{{ $user->medicalRecord->creator_name }}</td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <h6 class="text-muted">Record Completeness</h6>
                                    <div class="progress" style="height: 25px;">
                                        <div class="progress-bar progress-bar-striped 
                                            {{ $stats['record_completion'] >= 80 ? 'bg-success' : 
                                               ($stats['record_completion'] >= 60 ? 'bg-warning' : 'bg-danger') }}" 
                                             role="progressbar" 
                                             style="width: {{ $stats['record_completion'] }}%"
                                             aria-valuenow="{{ $stats['record_completion'] }}" 
                                             aria-valuemin="0" 
                                             aria-valuemax="100">
                                            {{ $stats['record_completion'] }}%
                                        </div>
                                    </div>
                                </div>
                                
                                @if($user->medicalRecord->needsReview())
                                    <div class="alert alert-warning">
                                        <i class="fas fa-exclamation-triangle me-1"></i>
                                        This record needs review (last updated over 6 months ago)
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    @else
        <!-- No Medical Record -->
        <div class="row">
            <div class="col-12">
                <div class="card border-warning">
                    <div class="card-body text-center py-5">
                        <i class="fas fa-file-medical-alt fa-5x text-warning mb-4"></i>
                        <h3 class="text-warning">No Medical Record Found</h3>
                        <p class="text-muted fs-5 mb-4">This student doesn't have a medical record in the system yet.</p>
                        <div class="alert alert-info d-inline-block">
                            <i class="fas fa-info-circle me-1"></i>
                            A medical record is required for proper healthcare management
                        </div>
                        <div class="mt-4">
                            <a href="{{ route('nurse.medical-records.create') }}?student_id={{ $user->id }}" 
                               class="btn btn-success btn-lg">
                                <i class="fas fa-plus me-2"></i> Create Medical Record
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection

@push('styles')
<style>
    .card {
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        border: 1px solid rgba(0, 0, 0, 0.125);
        transition: all 0.3s ease;
    }

    .card:hover {
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
    }

    .card-header {
        border-bottom: 1px solid rgba(0, 0, 0, 0.125);
        font-weight: 600;
    }

    .progress {
        height: 1.5rem;
        border-radius: 0.5rem;
    }

    .alert {
        border: none;
        border-radius: 0.5rem;
    }

    .badge {
        font-size: 0.75em;
        font-weight: 500;
    }

    .table-borderless td {
        border: none;
        padding: 0.5rem 0;
    }

    .border-start {
        border-left-width: 4px !important;
    }

    @media print {
        .btn-group, .breadcrumb, .no-print {
            display: none !important;
        }
        
        .card {
            border: 1px solid #000 !important;
            box-shadow: none !important;
        }
        
        .card-header {
            background-color: #f8f9fa !important;
            color: #000 !important;
        }
    }
</style>
@endpush

@push('scripts')
<script>
function printRecord() {
    window.print();
}

// Add tooltip initialization if you're using Bootstrap tooltips
document.addEventListener('DOMContentLoaded', function() {
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});
</script>
@endpush