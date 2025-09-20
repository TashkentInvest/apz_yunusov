@if ($errors->any())
<div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
    <div class="flex">
        <div class="flex-shrink-0">
            <i data-feather="alert-triangle" class="w-5 h-5 text-red-400"></i>
        </div>
        <div class="ml-3">
            <h3 class="text-sm font-medium text-red-800">Quyidagi xatoliklarni tuzating:</h3>
            <div class="mt-2 text-sm text-red-700">
                <ul class="list-disc pl-5 space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
</div>
@endif

@if(session('success'))
<div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-6" id="successMessage">
    <div class="flex">
        <div class="flex-shrink-0">
            <i data-feather="check-circle" class="w-5 h-5 text-green-400"></i>
        </div>
        <div class="ml-3">
            <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
        </div>
        <div class="ml-auto pl-3">
            <button onclick="document.getElementById('successMessage').remove()" class="text-green-400 hover:text-green-600">
                <i data-feather="x" class="w-4 h-4"></i>
            </button>
        </div>
    </div>
</div>
@endif

@if(session('error'))
<div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6" id="errorMessage">
    <div class="flex">
        <div class="flex-shrink-0">
            <i data-feather="alert-triangle" class="w-5 h-5 text-red-400"></i>
        </div>
        <div class="ml-3">
            <p class="text-sm font-medium text-red-800">{{ session('error') }}</p>
        </div>
        <div class="ml-auto pl-3">
            <button onclick="document.getElementById('errorMessage').remove()" class="text-red-400 hover:text-red-600">
                <i data-feather="x" class="w-4 h-4"></i>
            </button>
        </div>
    </div>
</div>
@endif

@if(session('warning'))
<div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6" id="warningMessage">
    <div class="flex">
        <div class="flex-shrink-0">
            <i data-feather="alert-triangle" class="w-5 h-5 text-yellow-400"></i>
        </div>
        <div class="ml-3">
            <p class="text-sm font-medium text-yellow-800">{{ session('warning') }}</p>
        </div>
        <div class="ml-auto pl-3">
            <button onclick="document.getElementById('warningMessage').remove()" class="text-yellow-400 hover:text-yellow-600">
                <i data-feather="x" class="w-4 h-4"></i>
            </button>
        </div>
    </div>
</div>
@endif

@if(session('info'))
<div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6" id="infoMessage">
    <div class="flex">
        <div class="flex-shrink-0">
            <i data-feather="info" class="w-5 h-5 text-blue-400"></i>
        </div>
        <div class="ml-3">
            <p class="text-sm font-medium text-blue-800">{{ session('info') }}</p>
        </div>
        <div class="ml-auto pl-3">
            <button onclick="document.getElementById('infoMessage').remove()" class="text-blue-400 hover:text-blue-600">
                <i data-feather="x" class="w-4 h-4"></i>
            </button>
        </div>
    </div>
</div>
@endif
