<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <script src="/js/user.js"></script>
    <script src="/js/footer.js"></script>
	<link rel="stylesheet" href="/css/app.css">
    <meta name="is-logged-in" content="{{ $is_logged_in ? 'true' : 'false' }}">
    <title>My Profile - Sudoku</title>
</head>
<body class="bg-linear-to-br from-amber-50 to-amber-100 min-h-screen pb-24">
    <div class="max-w-2xl mx-auto p-6 space-y-8">
        <!-- Header -->
        <div class="flex justify-between items-center">
            <h1 class="text-3xl font-extrabold text-indigo-900">My Profile</h1>
            <form action="/logout" method="POST">
                @csrf
                <button type="submit" class="bg-red-100 text-red-600 px-4 py-2 rounded-lg font-semibold hover:bg-red-200 transition-colors cursor-pointer">
                    Logout
                </button>
            </form>
        </div>

        <!-- User Info -->
        <div class="bg-white rounded-2xl shadow-xl p-6 border border-indigo-100">
            <div class="flex items-center gap-4 mb-6">
                <div class="w-16 h-16 bg-indigo-100 rounded-full flex items-center justify-center text-2xl font-bold text-indigo-600 uppercase">
                    {{ substr($username, 0, 1) }}
                </div>
                <div>
                    <h2 class="text-xl font-bold text-gray-900">{{ $username }}</h2>
                    <p class="text-indigo-600">Sudoku Player</p>
                </div>
            </div>

            <!-- Stats Grid -->
            <div class="grid grid-cols-3 gap-4">
                <div class="text-center p-4 bg-indigo-50 rounded-xl">
                    <div class="text-2xl font-bold text-indigo-600" id="stat-games-completed">--</div>
                    <div class="text-xs text-gray-600 uppercase tracking-wide mt-1">Games</div>
                </div>
                <div class="text-center p-4 bg-purple-50 rounded-xl">
                    <div class="text-2xl font-bold text-purple-600" id="stat-best-score">--</div>
                    <div class="text-xs text-gray-600 uppercase tracking-wide mt-1">Best Score</div>
                </div>
                <div class="text-center p-4 bg-amber-50 rounded-xl">
                    <div class="text-2xl font-bold text-amber-600" id="stat-total-score">--</div>
                    <div class="text-xs text-gray-600 uppercase tracking-wide mt-1">Total Score</div>
                </div>
            </div>
            
            <!-- Detailed Stats -->
            <div id="stats-details"></div>
        </div>

        <!-- Achievements -->
        <div>
            <h2 class="text-xl font-bold text-indigo-900 mb-4">Achievements</h2>
            <div id="achievements-list" class="space-y-3">
                <!-- Populated by JS -->
                <div class="animate-pulse space-y-3">
                    <div class="h-20 bg-white/50 rounded-xl"></div>
                    <div class="h-20 bg-white/50 rounded-xl"></div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>