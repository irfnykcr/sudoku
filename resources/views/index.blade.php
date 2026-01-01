<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="/js/index.js"></script>
	<script src="/js/footer.js"></script>
	<script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
	<link rel="stylesheet" href="/css/app.css">
	<meta name="csrf-token" content="{{ csrf_token() }}">
	<title>sudoku</title>
</head>
<body class="bg-linear-to-br from-amber-50 to-amber-100 flex items-center justify-center">
    <main class="flex flex-col w-full max-w-2xl mx-auto space-y-4 sm:space-y-6 lg:space-y-8 min-h-screen items-center justify-center -mt-20">
        <!-- Daily Challenge Card -->
		<div class="bg-indigo-600 rounded-2xl shadow-xl sm:p-2 transition-shadow duration-300 w-48 lg:w-64 max-w-xs sm:max-w-sm">
			<div class="flex flex-col mb-3 text-center justify-center items-center sm:space-y-4">
				<svg class="w-24 h-24 sm:w-32 sm:h-32 -mb-1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640"><path fill="#ffffff" d="M416 64C433.7 64 448 78.3 448 96L448 128L480 128C515.3 128 544 156.7 544 192L544 480C544 515.3 515.3 544 480 544L160 544C124.7 544 96 515.3 96 480L96 192C96 156.7 124.7 128 160 128L192 128L192 96C192 78.3 206.3 64 224 64C241.7 64 256 78.3 256 96L256 128L384 128L384 96C384 78.3 398.3 64 416 64zM438 225.7C427.3 217.9 412.3 220.3 404.5 231L285.1 395.2L233 343.1C223.6 333.7 208.4 333.7 199.1 343.1C189.8 352.5 189.7 367.7 199.1 377L271.1 449C276.1 454 283 456.5 289.9 456C296.8 455.5 303.3 451.9 307.4 446.2L443.3 259.2C451.1 248.5 448.7 233.5 438 225.7z"/></svg>				<h2 class="text-lg font-bold text-white mb-2">Daily Challenge</h2>
				<p class="text-lg text-white">
					<span id="daily-challange-date" class="font-semibold">{{date("d-m-Y");}}</span>
				</p>
				<button id="daily-challange-play" class="w-32 bg-white text-indigo-600 font-bold py-4 px-4 rounded-xl transition-all duration-300 transform hover:scale-105 shadow-lg hover:shadow-xl">
					Play
				</button>
			</div>
        </div>
        
        <!-- Site Title and Best Score -->
		<div class="text-center space-y-3 pt-5 pb-5">
			<h2 class="sm:text-5xl text-xl font-extrabold text-indigo-600">
				sudoku.turkuazz.vip
			</h2>
			<p class="text-xl text-gray-700">
				Best Score: <span id="best-score" class="font-bold text-purple-600">--</span>
			</p>
		</div>

        <!-- Game Actions -->
		<div class="flex flex-col space-y-1 sm:space-y-2 lg:space-y-4 w-full max-w-xs sm:max-w-sm">
			<button id="continue-game" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 px-2 sm:py-4 sm:px-4 rounded-xl transition-all duration-300 transform hover:scale-105 shadow-lg hover:shadow-xl">
                <p>Continue Game</p>
				<a id="continue-info" class="text-xs flex flex-row items-center justify-center space-x-1 mt-1 hidden">
					<svg class="w-3 h-3 mr-1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640"><path fill="#ffffff" d="M320 64C461.4 64 576 178.6 576 320C576 461.4 461.4 576 320 576C178.6 576 64 461.4 64 320C64 178.6 178.6 64 320 64zM296 184L296 320C296 328 300 335.5 306.7 340L402.7 404C413.7 411.4 428.6 408.4 436 397.3C443.4 386.2 440.4 371.4 429.3 364L344 307.2L344 184C344 170.7 333.3 160 320 160C306.7 160 296 170.7 296 184z"/></svg>
					<p id="continue-info-text">00:00 - Easy</p>
				</a>
            </button>
			<button id="new-game" class="w-full bg-white hover:bg-indigo-50 text-indigo-600 font-bold py-3 px-2 sm:py-4 sm:px-4 rounded-xl transition-all duration-300 transform hover:scale-105 shadow-lg hover:shadow-xl">
                New Game
            </button>
        </div>
    </main>
</body>

<div id="difficulty-drawer" class="fixed bottom-3 hidden z-50 sm:w-80 lg:w-[416px]">
	<div class="max-w-md mx-auto bg-white rounded-2xl shadow-2xl p-4 space-y-4 ring-1 ring-black/5">
		<div class="flex items-center justify-between">
			<p class="text-lg font-bold text-indigo-900">Select Difficulty</p>
			<button id="difficulty-close" class="bg-white hover:bg-indigo-50 active:bg-indigo-100 active:scale-95 text-indigo-700 font-bold py-2 px-3 rounded-xl transition-all duration-150 shadow-lg hover:shadow-xl">
				x
			</button>
		</div>
		<div class="grid grid-cols-2 gap-3">
			<button class="difficulty-select w-full bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 active:scale-95 text-white font-bold py-3 px-4 rounded-xl transition-all duration-150 shadow-lg hover:shadow-xl" data-difficulty="Easy">Easy</button>
			<button class="difficulty-select w-full bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 active:scale-95 text-white font-bold py-3 px-4 rounded-xl transition-all duration-150 shadow-lg hover:shadow-xl" data-difficulty="Medium">Medium</button>
			<button class="difficulty-select w-full bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 active:scale-95 text-white font-bold py-3 px-4 rounded-xl transition-all duration-150 shadow-lg hover:shadow-xl" data-difficulty="Hard">Hard</button>
			<button class="difficulty-select w-full bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 active:scale-95 text-white font-bold py-3 px-4 rounded-xl transition-all duration-150 shadow-lg hover:shadow-xl" data-difficulty="Expert">Expert</button>
		</div>
	</div>
</div>

<div id="menu-backdrop" class="fixed inset-0 z-0 hidden bg-black/20 backdrop-blur-sm"></div>
</html>