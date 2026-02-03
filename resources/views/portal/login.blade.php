@extends('portal.layout')

@section('title', 'Logowanie')

@section('content')
<div class="flex flex-col md:flex-row justify-center items-start gap-8 mt-10 max-w-5xl mx-auto">
    
    <!-- Sekcja 1: Aktywacja kodem -->
    <div class="bg-white p-8 rounded-lg shadow-lg w-full md:w-1/2">
        <div class="border-b pb-4 mb-6">
            <h2 class="text-2xl font-bold text-gray-700">Pierwsze logowanie?</h2>
            <p class="text-sm text-gray-500 mt-1">Jeśli masz kod od organizatora, aktywuj swoje konto tutaj.</p>
        </div>
        
        <form action="{{ route('portal.validate_code') }}" method="POST">
            @csrf
            <div class="mb-6">
                <label for="access_code" class="block text-gray-700 text-sm font-bold mb-2">Kod dostępu:</label>
                <div class="flex">
                    <input type="text" name="access_code" id="access_code" 
                        class="shadow appearance-none border rounded-l w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring focus:border-green-300"
                        placeholder="Np. X82A-99B1" required>
                    <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded-r focus:outline-none transition duration-150">
                        Dalej <i class="fas fa-arrow-right ml-1"></i>
                    </button>
                </div>
            </div>
            <div class="bg-green-50 p-4 rounded text-sm text-green-800 border-l-4 border-green-400">
                <i class="fas fa-info-circle mr-1"></i> Po wpisaniu kodu utworzysz swoje indywidualne konto (email + hasło), którego będziesz używać przy kolejnych wizytach.
            </div>
        </form>
    </div>

    <!-- Sekcja 2: Logowanie standardowe -->
    <div class="bg-white p-8 rounded-lg shadow-lg w-full md:w-1/2">
        <div class="border-b pb-4 mb-6">
            <h2 class="text-2xl font-bold text-gray-700">Mam już konto</h2>
            <p class="text-sm text-gray-500 mt-1">Zaloguj się używając adresu e-mail i hasła.</p>
        </div>
        
        <form action="{{ route('portal.auth') }}" method="POST">
            @csrf
            
            <div class="mb-4">
                <label for="email" class="block text-gray-700 text-sm font-bold mb-2">Adres E-mail:</label>
                <input type="email" name="email" id="email" 
                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring focus:border-blue-300"
                    placeholder="Wprowadź swój email..." required>
            </div>

            <div class="mb-6">
                <label for="password" class="block text-gray-700 text-sm font-bold mb-2">Hasło:</label>
                <input type="password" name="password" id="password" 
                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring focus:border-blue-300"
                    placeholder="Wprowadź hasło..." required>
            </div>
            
            <div class="flex items-center justify-between">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded focus:outline-none focus:shadow-outline w-full transition duration-150">
                    <i class="fas fa-sign-in-alt mr-2"></i> Zaloguj się
                </button>
            </div>

            @if(session('error'))
            <div class="mt-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                <span class="block sm:inline">{{ session('error') }}</span>
            </div>
            @endif
        </form>
    </div>
</div>
@endsection