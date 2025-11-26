<div id="viewModal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center z-50 p-4 overflow-y-auto">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-5xl mx-auto my-8 max-h-[90vh] overflow-hidden">
        <div class="flex items-center justify-between p-4 sm:p-6 border-b sticky top-0 bg-white z-10">
            <h3 class="text-xl font-bold text-gray-800">Appointment Details</h3>
            <button onclick="closeViewModal()" class="text-gray-400 hover:text-gray-600 p-2"><i class="fas fa-times text-xl"></i></button>
        </div>
        <div id="viewModalContent" class="p-4 sm:p-6"></div>
    </div>
</div>