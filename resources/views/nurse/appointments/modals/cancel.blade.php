{{-- resources/views/nurse/appointments/modals/cancel.blade.php --}}
<div id="cancelModal" class="hidden fixed inset-0 z-50 overflow-y-auto bg-black bg-opacity-50 flex items-center justify-center" aria-labelledby="cancel-title" role="dialog" aria-modal="true">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-md mx-4 p-6 relative">
        <button onclick="closeCancelModal()" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        </button>

        <div class="text-center mb-6">
            <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100 mb-4">
                <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-12.728 12.728m0-12.728l12.728 12.728"></path>
                </svg>
            </div>
            <h3 class="text-lg font-semibold text-gray-900" id="cancel-title">Cancel Appointment</h3>
            <p class="text-sm text-gray-500 mt-2">This action cannot be undone.</p>
        </div>

        <form id="cancelForm" method="POST" class="space-y-4">
            @csrf
            @method('PUT') {{-- CHANGED FROM PATCH TO PUT --}}

            <div>
                <label for="cancellation_reason" class="block text-sm font-medium text-gray-700">Cancellation Reason <span class="text-red-500">*</span></label>
                <textarea id="cancellation_reason" name="cancellation_reason" rows="3" required
                          class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent"
                          placeholder="State the reason for cancellation..."></textarea>
            </div>

            <div class="flex justify-end space-x-3 mt-6">
                <button type="button" onclick="closeCancelModal()" 
                        class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50 transition">
                    Keep Appointment
                </button>
                <button type="submit" 
                        class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-md transition">
                    Cancel Appointment
                </button>
            </div>
        </form>
    </div>
</div>