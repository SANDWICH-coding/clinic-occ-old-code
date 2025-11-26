{{-- resources/views/nurse/appointments/modals/accept.blade.php --}}
<div id="acceptModal" class="hidden fixed inset-0 z-50 overflow-y-auto bg-black bg-opacity-50 flex items-center justify-center" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-md mx-4 p-6 relative">
        <button onclick="closeAcceptModal()" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        </button>

        <div class="text-center mb-6">
            <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-green-100 mb-4">
                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
            </div>
            <h3 class="text-lg font-semibold text-gray-900" id="modal-title">Accept Appointment</h3>
            <p class="text-sm text-gray-500 mt-2">Confirm this appointment with the student?</p>
        </div>

        <form id="acceptForm" method="POST" class="space-y-4">
            @csrf
            @method('PUT') {{-- CHANGED FROM MISSING TO PUT --}}
            <div>
                <label for="accept_notes" class="block text-sm font-medium text-gray-700">Notes (optional)</label>
                <textarea id="accept_notes" name="notes" rows="3" 
                          class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
                          placeholder="Any additional information..."></textarea>
            </div>

            <div class="flex justify-end space-x-3 mt-6">
                <button type="button" onclick="closeAcceptModal()" 
                        class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50 transition">
                    Cancel
                </button>
                <button type="submit" 
                        class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-md transition">
                    Accept Appointment
                </button>
            </div>
        </form>
    </div>
</div>