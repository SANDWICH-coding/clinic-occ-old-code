{{-- Emergency Contacts --}}
<div class="bg-gray-50 p-6 rounded-lg">
    <h4 class="font-semibold text-gray-800 mb-4"> Emergency Contacts</h4>
    @if($medicalRecord->emergency_contact_name_1)
        <div class="mb-4">
            <strong>Primary:</strong> {{ $medicalRecord->emergency_contact_name_1 }}
            @if($medicalRecord->emergency_contact_relationship_1)
                <span class="text-sm text-gray-600">({{ ucfirst($medicalRecord->emergency_contact_relationship_1) }})</span>
            @endif
            @if($medicalRecord->emergency_contact_number_1)
                - <a href="tel:{{ $medicalRecord->emergency_contact_number_1 }}" class="text-blue-600">{{ $medicalRecord->emergency_contact_number_1 }}</a>
            @endif
        </div>
    @endif
    @if($medicalRecord->emergency_contact_name_2)
        <div>
            <strong>Secondary:</strong> {{ $medicalRecord->emergency_contact_name_2 }}
            @if($medicalRecord->emergency_contact_relationship_2)
                <span class="text-sm text-gray-600">({{ ucfirst($medicalRecord->emergency_contact_relationship_2) }})</span>
            @endif
            @if($medicalRecord->emergency_contact_number_2)
                - <a href="tel:{{ $medicalRecord->emergency_contact_number_2 }}" class="text-blue-600">{{ $medicalRecord->emergency_contact_number_2 }}</a>
            @endif
        </div>
    @endif
</div>
        </div>
    </div>
</div>
@endsection