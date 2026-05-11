<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Connexion Admin - COTA</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <style>
        body {
            background: linear-gradient(135deg, #0F172A 0%, #1E293B 50%, #0D1321 100%);
        }
        .login-card {
            background: rgba(30, 41, 59, 0.8);
            backdrop-filter: blur(20px);
        }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-4">
    <div class="login-card w-full max-w-md rounded-2xl border border-gray-700/50 shadow-2xl p-8">
        <!-- Logo -->
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-gradient-to-r from-indigo-500 to-purple-500 mb-4">
                <span class="text-4xl">⚽</span>
            </div>
            <h1 class="text-2xl font-bold text-white">COTA Admin</h1>
            <p class="text-gray-400 mt-2">Connectez-vous pour accéder au panel</p>
        </div>
        
        <!-- Alerts -->
        @if(session('error'))
            <div class="mb-6 p-4 bg-red-500/20 border border-red-500/50 text-red-400 rounded-lg text-sm">
                <i class="fa-solid fa-exclamation-circle mr-2"></i>
                {{ session('error') }}
            </div>
        @endif
        
        @if(session('success'))
            <div class="mb-6 p-4 bg-green-500/20 border border-green-500/50 text-green-400 rounded-lg text-sm">
                <i class="fa-solid fa-check-circle mr-2"></i>
                {{ session('success') }}
            </div>
        @endif
        
        <!-- Form -->
        <form method="POST" action="{{ route('admin.login.submit') }}" class="space-y-6">
            @csrf
            
            <!-- Email -->
            <div>
                <label for="email" class="block text-sm font-medium text-gray-300 mb-2">
                    Email
                </label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-4 flex items-center text-gray-500">
                        <i class="fa-solid fa-envelope"></i>
                    </span>
                    <input type="email" 
                           name="email" 
                           id="email" 
                           value="{{ old('email') }}"
                           class="w-full pl-11 pr-4 py-3 bg-gray-800/50 border border-gray-600 rounded-lg text-white placeholder-gray-500 focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition"
                           placeholder="admin@cota.app"
                           required 
                           autofocus>
                </div>
                @error('email')
                    <p class="mt-2 text-sm text-red-400">{{ $message }}</p>
                @enderror
            </div>
            
            <!-- Password -->
            <div>
                <label for="password" class="block text-sm font-medium text-gray-300 mb-2">
                    Mot de passe
                </label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-4 flex items-center text-gray-500">
                        <i class="fa-solid fa-lock"></i>
                    </span>
                    <input type="password" 
                           name="password" 
                           id="password" 
                           class="w-full pl-11 pr-4 py-3 bg-gray-800/50 border border-gray-600 rounded-lg text-white placeholder-gray-500 focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition"
                           placeholder="••••••••"
                           required>
                </div>
            </div>
            
            <!-- Remember -->
            <div class="flex items-center justify-between">
                <label class="flex items-center">
                    <input type="checkbox" name="remember" class="w-4 h-4 text-indigo-500 border-gray-600 rounded focus:ring-indigo-500 bg-gray-800">
                    <span class="ml-2 text-sm text-gray-400">Se souvenir de moi</span>
                </label>
            </div>
            
            <!-- Submit -->
            <button type="submit" 
                    class="w-full py-3 px-4 bg-gradient-to-r from-indigo-500 to-purple-500 hover:from-indigo-600 hover:to-purple-600 text-white font-semibold rounded-lg shadow-lg hover:shadow-xl transition-all duration-300 flex items-center justify-center gap-2">
                <i class="fa-solid fa-right-to-bracket"></i>
                Se connecter
            </button>
        </form>
        
        <!-- Footer -->
        <p class="mt-8 text-center text-sm text-gray-500">
            🔒 Accès réservé aux super administrateurs
        </p>
    </div>
</body>
</html>

