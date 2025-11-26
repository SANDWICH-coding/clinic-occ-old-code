@extends('layouts.nurse-app')

@section('title', 'Medical Data Management')

@section('content')
<div class="min-h-screen bg-gray-100 p-8">
    <div class="max-w-7xl mx-auto">
        <div class="bg-white rounded-xl shadow-lg overflow-hidden">
            {{-- Header --}}
            <div class="bg-gradient-to-r from-blue-600 to-purple-600 text-white p-6 md:p-8">
                <div class="flex items-center space-x-3">
                    <i class="fas fa-database text-3xl"></i>
                    <h1 class="text-3xl font-semibold">Medical Data Management</h1>
                </div>
                <p class="text-blue-100 mt-2">Manage symptoms and illnesses</p>
            </div>

            {{-- Success Message --}}
            <div class="p-6">
                @if(session('success'))
                    <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6">
                        <div class="flex items-center">
                            <i class="fas fa-check-circle mr-3 text-xl"></i>
                            <p>{{ session('success') }}</p>
                        </div>
                    </div>
                @endif

                {{-- Tabs Navigation --}}
                <div class="border-b border-gray-200 mb-6">
                    <nav class="-mb-px flex space-x-8">
                        <button onclick="switchTab('symptoms')" id="tab-symptoms" class="tab-button {{ request('tab') !== 'illnesses' ? 'active border-blue-500 text-blue-600' : 'border-transparent text-gray-500' }} border-b-2 py-4 px-1 font-medium">
                            <i class="fas fa-heartbeat mr-2"></i>Symptoms ({{ $symptoms->total() }})
                        </button>
                        <button onclick="switchTab('illnesses')" id="tab-illnesses" class="tab-button {{ request('tab') === 'illnesses' ? 'active border-purple-500 text-purple-600' : 'border-transparent text-gray-500' }} border-b-2 py-4 px-1 font-medium">
                            <i class="fas fa-stethoscope mr-2"></i>Illnesses ({{ $illnesses->total() }})
                        </button>
                    </nav>
                </div>

                {{-- SYMPTOMS TAB - Updated header --}}
                <div id="content-symptoms" class="tab-content {{ request('tab') === 'illnesses' ? 'hidden' : '' }}">
                    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
                        <h2 class="text-2xl font-semibold text-gray-800">Symptoms</h2>
                        <a href="{{ route('nurse.medical-data.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded-lg font-medium hover:bg-blue-700">
                            <i class="fas fa-plus mr-1"></i> Create New
                        </a>
                    </div>

                    {{-- Search --}}
                    <form method="GET" class="mb-6">
                        <input type="hidden" name="tab" value="symptoms">
                        <div class="flex gap-4">
                            <input type="text" name="search_symptom" value="{{ request('search_symptom') }}" 
                                   class="flex-grow px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" 
                                   placeholder="Search symptoms...">
                            <button type="submit" class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </form>

                    @if($symptoms->count() > 0)
                        <div class="overflow-x-auto bg-gray-50 rounded-lg shadow-sm">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-800 text-white">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase">Name</th>
                                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase">Linked Illnesses</th>
                                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase">Created</th>
                                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($symptoms as $symptom)
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-6 py-4">
                                                <span class="text-sm font-medium text-gray-900">{{ $symptom->name }}</span>
                                            </td>
                                            <td class="px-6 py-4">
                                                <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                    {{ $symptom->possible_illnesses_count }} illness(es)
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 text-sm text-gray-500">
                                                {{ $symptom->created_at->format('M d, Y') }}
                                            </td>
                                            <td class="px-6 py-4">
                                                <div class="flex items-center gap-2">
                                                    <a href="{{ route('nurse.medical-data.show', $symptom->id) }}?type=symptom" class="text-green-600 hover:text-green-800" title="View">
                                                        <i class="fas fa-eye text-lg"></i>
                                                    </a>
                                                    <a href="{{ route('nurse.medical-data.edit', $symptom->id) }}?type=symptom" class="text-blue-600 hover:text-blue-800" title="Edit">
                                                        <i class="fas fa-edit text-lg"></i>
                                                    </a>
                                                    <form action="{{ route('nurse.medical-data.destroy', $symptom->id) }}" method="POST" class="inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <input type="hidden" name="type" value="symptom">
                                                        <button type="submit" class="text-red-600 hover:text-red-800" title="Delete" onclick="return confirm('Delete this symptom?')">
                                                            <i class="fas fa-trash text-lg"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-6">{{ $symptoms->appends(['tab' => 'symptoms', 'search_symptom' => request('search_symptom')])->links() }}</div>
                    @else
                        <div class="text-center py-12 bg-gray-50 rounded-lg">
                            <i class="fas fa-heartbeat text-6xl text-gray-300 mb-4"></i>
                            <h3 class="text-xl font-semibold text-gray-800">No symptoms found</h3>
                        </div>
                    @endif
                </div>

                {{-- ILLNESSES TAB - Updated header --}}
                <div id="content-illnesses" class="tab-content {{ request('tab') !== 'illnesses' ? 'hidden' : '' }}">
                    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
                        <h2 class="text-2xl font-semibold text-gray-800">Illnesses</h2>
                        <a href="{{ route('nurse.medical-data.create') }}" class="bg-purple-600 text-white px-4 py-2 rounded-lg font-medium hover:bg-purple-700">
                            <i class="fas fa-plus mr-1"></i> Create New
                        </a>
                    </div>

                    {{-- Search --}}
                    <form method="GET" class="mb-6">
                        <input type="hidden" name="tab" value="illnesses">
                        <div class="flex gap-4">
                            <input type="text" name="search_illness" value="{{ request('search_illness') }}" 
                                   class="flex-grow px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500" 
                                   placeholder="Search illnesses...">
                            <button type="submit" class="px-6 py-3 bg-purple-600 text-white rounded-lg hover:bg-purple-700">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </form>

                    @if($illnesses->count() > 0)
                        <div class="overflow-x-auto bg-gray-50 rounded-lg shadow-sm">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-800 text-white">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase">Name</th>
                                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase">Linked Symptoms</th>
                                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase">Created</th>
                                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($illnesses as $illness)
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-6 py-4">
                                                <span class="text-sm font-medium text-gray-900">{{ $illness->name }}</span>
                                            </td>
                                            <td class="px-6 py-4">
                                                <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                                    {{ $illness->symptoms_count }} symptom(s)
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 text-sm text-gray-500">
                                                {{ $illness->created_at->format('M d, Y') }}
                                            </td>
                                            <td class="px-6 py-4">
                                                <div class="flex items-center gap-2">
                                                    <a href="{{ route('nurse.medical-data.show', $illness->id) }}?type=illness" class="text-green-600 hover:text-green-800" title="View">
                                                        <i class="fas fa-eye text-lg"></i>
                                                    </a>
                                                    <a href="{{ route('nurse.medical-data.edit', $illness->id) }}?type=illness" class="text-purple-600 hover:text-purple-800" title="Edit">
                                                        <i class="fas fa-edit text-lg"></i>
                                                    </a>
                                                    <form action="{{ route('nurse.medical-data.destroy', $illness->id) }}" method="POST" class="inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <input type="hidden" name="type" value="illness">
                                                        <button type="submit" class="text-red-600 hover:text-red-800" title="Delete" onclick="return confirm('Delete this illness?')">
                                                            <i class="fas fa-trash text-lg"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-6">{{ $illnesses->appends(['tab' => 'illnesses', 'search_illness' => request('search_illness')])->links() }}</div>
                    @else
                        <div class="text-center py-12 bg-gray-50 rounded-lg">
                            <i class="fas fa-stethoscope text-6xl text-gray-300 mb-4"></i>
                            <h3 class="text-xl font-semibold text-gray-800">No illnesses found</h3>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function switchTab(tab) {
    document.querySelectorAll('.tab-button').forEach(btn => {
        btn.classList.remove('active', 'border-blue-500', 'border-purple-500', 'text-blue-600', 'text-purple-600');
        btn.classList.add('border-transparent', 'text-gray-500');
    });
    document.querySelectorAll('.tab-content').forEach(content => content.classList.add('hidden'));
    
    if (tab === 'symptoms') {
        document.getElementById('tab-symptoms').classList.add('active', 'border-blue-500', 'text-blue-600');
        document.getElementById('tab-symptoms').classList.remove('border-transparent', 'text-gray-500');
    } else {
        document.getElementById('tab-illnesses').classList.add('active', 'border-purple-500', 'text-purple-600');
        document.getElementById('tab-illnesses').classList.remove('border-transparent', 'text-gray-500');
    }
    
    document.getElementById('content-' + tab).classList.remove('hidden');
}
</script>
@endpush
@endsection