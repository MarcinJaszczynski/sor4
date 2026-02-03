@extends('portal.layout')

@section('title', 'Aktywacja Konta')

@section('content')
<div class="flex justify-center items-center mt-10">
    <div class="w-full max-w-md bg-white p-8 rounded-lg shadow-lg">
        <h2 class="text-2xl font-bold text-gray-700 mb-6 text-center">Aktywacja Konta</h2>
        
        <div class="mb-6 p-4 bg-blue-50 border-l-4 border-blue-400 text-blue-800">
            <p class="font-bold">Potwierdzono dostęp do wyjazdu:</p>
            <p class="text-lg">{{ $event->name }}</p>
            <p class="text-sm mt-2">Uzupełnij poniższe dane, aby utworzyć konto i uzyskać stały dostęp.</p>
        </div>

        <form action="{{ route('portal.register_submit') }}" method="POST">
            @csrf
            
            <div class="mb-4">
                <label for="name" class="block text-gray-700 text-sm font-bold mb-2">Imię i Nazwisko:</label>
                <input type="text" name="name" id="name" value="{{ old('name') }}"
                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring focus:border-blue-300"
                    required>
                @error('name') <p class="text-red-500 text-xs italic">{{ $message }}</p> @enderror
            </div>

            <div class="mb-4">
                <label for="email" class="block text-gray-700 text-sm font-bold mb-2">Adres E-mail:</label>
                <input type="email" name="email" id="email" value="{{ old('email') }}"
                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring focus:border-blue-300"
                    required>
                @error('email') <p class="text-red-500 text-xs italic">{{ $message }}</p> @enderror
            </div>

            <div class="mb-4">
                <label for="password" class="block text-gray-700 text-sm font-bold mb-2">Hasło:</label>
                <input type="password" name="password" id="password" 
                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring focus:border-blue-300"
                    required>
                @error('password') <p class="text-red-500 text-xs italic">{{ $message }}</p> @enderror
            </div>

            <div class="mb-6">
                <label for="password_confirmation" class="block text-gray-700 text-sm font-bold mb-2">Potwierdź Hasło:</label>
                <input type="password" name="password_confirmation" id="password_confirmation" 
                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring focus:border-blue-300"
                    required>
            </div>

            <div class="flex items-center justify-between">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline w-full transition duration-150">
                    Aktywuj i Zaloguj
                </button>
            </div>
            
            <div class="mt-4 text-center">
                <a href="{{ route('portal.login') }}" class="text-gray-500 text-sm hover:underline">Anuluj</a>
            </div>
        </form>
    </div>
</div>
@endsection