<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <script src="/js/footer.js"></script>
	<link rel="stylesheet" href="/css/app.css">
    <meta name="is-logged-in" content="{{ $is_logged_in ? 'true' : 'false' }}">
    <title>Register - Sudoku</title>
</head>
<body class="bg-linear-to-br from-amber-50 to-amber-100 min-h-screen flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-xl p-4 w-full max-w-md border border-indigo-100 mt-5">
        <div class="text-center mb-4">
            <h1 class="text-3xl font-extrabold text-indigo-900">Create Account</h1>
            <p class="text-indigo-600 mt-2">Join to track your progress and achievements</p>
        </div>

        <form action="/register" method="POST" class="space-y-2">
            @csrf
            
            @if($errors->any())
                <div class="bg-red-50 text-red-600 p-3 rounded-lg text-sm">
                    {{ $errors->first() }}
                </div>
            @endif

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Username</label>
                <input type="text" name="user" required 
                    class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 outline-none transition-all"
                    placeholder="Choose a username">
            </div>

            <div> <!-- add requirements check -->
                <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                <input type="password" name="password" required 
                    class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 outline-none transition-all"
                    placeholder="••••••••">
            </div>

            <div> <!-- add "is same" check -->
                <label class="block text-sm font-medium text-gray-700 mb-1">Confirm Password</label>
                <input type="password" name="password_confirmation" required 
                    class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 outline-none transition-all"
                    placeholder="••••••••">
            </div>

            <button type="submit" 
                class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 px-4 rounded-xl transition-all duration-300 transform hover:scale-[1.02] shadow-lg hover:shadow-xl">
                Create Account
            </button>
        </form>

        <div class="mt-6 text-center text-sm text-gray-600">
            Already have an account? 
            <a href="/login" class="text-indigo-600 font-bold hover:underline">Sign in</a>
        </div>
        <div class="mt-4 text-center">
            <a href="/" class="text-gray-400 hover:text-gray-600 text-sm">Back to Home</a>
        </div>
    </div>
</body>
</html>
