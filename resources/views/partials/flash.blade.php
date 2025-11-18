@if(session('success'))
    <div class="max-w-4xl mx-auto mt-4">
        <div class="bg-green-100 border border-green-300 text-green-800 px-4 py-3 rounded">
            {{ session('success') }}
        </div>
    </div>
@endif

@if(session('error'))
    <div class="max-w-4xl mx-auto mt-4">
        <div class="bg-red-100 border border-red-300 text-red-800 px-4 py-3 rounded">
            {{ session('error') }}
        </div>
    </div>
@endif

@if($errors->any())
    <div class="max-w-4xl mx-auto mt-4">
        <div class="bg-yellow-50 border border-yellow-200 text-yellow-800 px-4 py-3 rounded">
            <ul class="list-disc pl-5">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    </div>
@endif
