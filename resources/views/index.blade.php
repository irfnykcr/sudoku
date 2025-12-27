<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="/js/footer.js"></script>
    <script src="/js/index.js"></script>
	<script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <title>sudoku</title>
</head>
<body class="bg-gradient-to-br from-amber-50 to-amber-100 min-h-screen flex items-center justify-center p-4">
    <main class="flex flex-col w-full max-w-2xl mx-auto space-y-8 items-center">
        <!-- Daily Challenge Card -->
		<div class="bg-indigo-600 rounded-2xl shadow-xl p-8 transition-shadow duration-300 w-full max-w-xs sm:max-w-sm">
			<div class="text-center space-y-4 mt-4">
				<h2 class="text-lg font-bold text-white mb-2">Daily Challenge</h2>
				<p class="text-lg text-white">
					<span id="daily-challange-date" class="font-semibold">{{date("d-m-Y");}}</span>
				</p>
				<button id="daily-challange-play" class="w-32 bg-white text-indigo-600 font-bold py-4 px-4 rounded-xl transition-all duration-300 transform hover:scale-105 shadow-lg hover:shadow-xl">
					Play
				</button>
			</div>
        </div>
        
        <!-- Site Title and Best Score -->
		<div class="text-center space-y-3 pt-10 pb-10">
			<h2 class="sm:text-3xl lg:text-5xl text-xl font-extrabold text-indigo-600">
				sudoku.turkuazz.vip
			</h2>
			<p class="text-xl text-gray-700">
				Best Score: <span id="best-score" class="font-bold text-purple-600">--</span>
			</p>
		</div>

        <!-- Game Actions -->
		<div class="flex flex-col space-y-4 w-full max-w-xs sm:max-w-sm">
			<button id="continue-game" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-4 px-4 rounded-xl transition-all duration-300 transform hover:scale-105 shadow-lg hover:shadow-xl">
                Continue Game
            </button>
			<button id="next-game" class="w-full bg-white hover:bg-indigo-50 text-indigo-600 font-bold py-4 px-4 rounded-xl transition-all duration-300 transform hover:scale-105 shadow-lg hover:shadow-xl">
                New Game
            </button>
        </div>
    </main>
</body>
<footer class="fixed bottom-0 left-0 right-0 bg-white/80 backdrop-blur-sm shadow-lg">
	<!-- main, daily changes, me -->
	<div class="max-w-2xl mx-auto flex justify-between items-center p-4 mb-2">
		<button id="footer-main" class="text-indigo-600 font-semibold hover:underline flex flex-col items-center text-xl">
			<svg xmlns="http://www.w3.org/2000/svg" class="inline h-8 w-8" viewBox="0 0 20 20" fill="currentColor">
				<path d="M10.707 1.707a1 1 0 00-1.414 0l-7 7A1 1 0 003 10h1v7a1 1 0 001 1h4a1 1 0 001-1v-4h2v4a1 1 0 001 1h4a1 1 0 001-1v-7h1a1 1 0 00.707-1.707l-7-7z" />
			</svg>
			Main
		</button>
		<button id="footer-daily-challenge" class="text-indigo-600 font-semibold hover:underline flex flex-col items-center text-xl">
			<svg xmlns="http://www.w3.org/2000/svg" class="inline h-8 w-8" viewBox="0 0 20 20" fill="currentColor">
				<path d="M6 2a1 1 0 000 2h1v1a1 1 0 102 0V4h2v1a1 1 0 102 0V4h1a1 1 0 100-2H6zM3 8a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM3 11a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM4 14a1 1 0 100 2h12a1 1 0 100-2H4z" />
			</svg>
			Daily Challenge
		</button>
		<button id="footer-me" class="text-indigo-600 font-semibold hover:underline flex flex-col items-center text-xl">
			<svg xmlns="http://www.w3.org/2000/svg" class="inline h-8 w-8" viewBox="0 0 20 20" fill="currentColor">
				<path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-6-3a2 2 0 11-4 0 2 2 0 014 0zm-2 4a5 5 0 00-4.546 2.916A5.986 5.986 0 0010 16a5.986 5.986 0 004.546-2.084A5 5 0 0010 11z" clip-rule="evenodd" />
			</svg>
			Me
		</button>
	</div>
</footer>
</html>