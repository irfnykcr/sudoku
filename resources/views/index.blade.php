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
	<meta name="is-logged-in" content="{{ $is_logged_in ? 'true' : 'false' }}">
	<title>sudoku</title>
</head>
<body class="bg-linear-to-br from-amber-50 to-amber-100 flex items-center justify-center min-h-screen">
    <main class="flex flex-col landscape:flex-row w-full max-w-4xl mx-auto space-y-4 sm:space-y-6 lg:space-y-8 landscape:space-y-0 landscape:space-x-8 min-h-screen items-center justify-center -mt-20 p-4">
        <!-- Daily Challenge Card -->
		<div class="bg-indigo-600 rounded-2xl shadow-xl sm:p-2 transition-shadow duration-300 w-48 lg:w-64 max-w-xs sm:max-w-sm shrink-0">
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
        
        <div class="flex flex-col items-center space-y-4 sm:space-y-6 lg:space-y-8 w-full max-w-xs sm:max-w-sm">
            <!-- Site Title and Best Score -->
            <div class="text-center space-y-3 pt-5 pb-5 landscape:pt-0 landscape:pb-0">
                <h2 class="sm:text-5xl landscape:text-3xl text-xl font-extrabold text-indigo-600">
                    sudoku.turkuazz.vip
                </h2>
                <p class="text-xl text-gray-700">
                    Best Score: <span id="best-score" class="font-bold text-purple-600">--</span>
                </p>
            </div>

            <!-- Game Actions -->
            <div class="flex flex-col space-y-1 sm:space-y-2 lg:space-y-4 w-full">
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
        </div>

		<!-- Daily Modal -->
		<div id="daily-modal" class="fixed inset-0 bg-black/50 hidden z-50 flex items-center justify-center p-4">
			<div class="bg-white rounded-2xl shadow-2xl w-full max-w-md p-6">
				<div class="flex justify-between items-center mb-6">
                    <div class="flex items-center space-x-2">
                        <button id="daily-prev-month" class="p-1 hover:bg-gray-100 rounded-full">
                            <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                        </button>
					    <h2 id="daily-month-label" class="text-xl font-bold text-indigo-900">Daily Challenges</h2>
                        <button id="daily-next-month" class="p-1 hover:bg-gray-100 rounded-full">
                            <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                        </button>
                    </div>
					<button id="daily-modal-close" class="text-gray-500 hover:text-gray-700">
						<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
					</button>
				</div>
				<div id="daily-calendar-grid" class="grid grid-cols-7 gap-2">
					<!-- Populated by JS -->
				</div>
                
                <div class="mt-6 flex justify-center space-x-4 text-xs text-gray-600">
                    <div class="flex items-center">
                        <div class="w-3 h-3 bg-indigo-100 rounded-full mr-1 border border-indigo-200"></div>
                        <span>New</span>
                    </div>
                    <div class="flex items-center">
                        <div class="w-3 h-3 bg-yellow-400 rounded-full mr-1 shadow-sm"></div>
                        <span>Resumable</span>
                    </div>
                    <div class="flex items-center">
                        <div class="w-3 h-3 bg-green-500 rounded-full mr-1 shadow-sm"></div>
                        <span>Completed</span>
                    </div>
                </div>
			</div>
		</div>
    </main>
	<!-- Confirmation Modal -->
	<div id="confirmation-modal" class="fixed inset-0 bg-black/50 backdrop-blur-sm hidden z-50 flex items-center justify-center p-4">
		<div class="bg-white rounded-2xl shadow-2xl max-w-md w-full p-6 transform transition-all scale-100 opacity-100">
			<div class="text-center space-y-4">
				<div class="text-4xl" id="confirmation-icon">⚠️</div>
				<h2 class="text-2xl font-bold text-indigo-900" id="confirmation-title">Confirm Action</h2>
				<p class="text-indigo-700" id="confirmation-message">
					Are you sure you want to proceed?
				</p>
				<div class="flex gap-3 justify-center pt-2">
					<button id="confirmation-confirm" class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 px-6 rounded-xl transition-all shadow-lg">
						Confirm
					</button>
					<button id="confirmation-cancel" class="flex-1 bg-white hover:bg-indigo-50 text-indigo-700 font-bold py-3 px-6 rounded-xl transition-all shadow-lg border border-indigo-100">
						Cancel
					</button>
				</div>
			</div>
		</div>
	</div>
</body>

<div id="difficulty-drawer" class="fixed bottom-3 hidden z-50 sm:w-80 lg:w-[416px]">
	<div class="max-w-md mx-auto bg-white rounded-2xl shadow-2xl p-4 space-y-4 ring-1 ring-black/5">
		<div class="flex items-center justify-between">
			<p class="text-lg font-bold text-indigo-900">Select Difficulty</p>
			<button id="difficulty-close" class="bg-white hover:bg-indigo-50 active:bg-indigo-100 active:scale-95 text-indigo-700 font-bold py-2 px-3 rounded-xl transition-all duration-150 shadow-lg hover:shadow-xl">
				x
			</button>
		</div>
		<div class="grid grid-cols-2 gap-3 justify-center">
			<button class="difficulty-select w-full bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 active:scale-95 text-white font-bold py-3 px-4 rounded-xl transition-all duration-150 shadow-lg hover:shadow-xl" data-difficulty="Easy">Easy</button>
			<button class="difficulty-select w-full bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 active:scale-95 text-white font-bold py-3 px-4 rounded-xl transition-all duration-150 shadow-lg hover:shadow-xl" data-difficulty="Medium">Medium</button>
			<button class="difficulty-select w-full bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 active:scale-95 text-white font-bold py-3 px-4 rounded-xl transition-all duration-150 shadow-lg hover:shadow-xl" data-difficulty="Hard">Hard</button>
			<button class="difficulty-select w-full bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 active:scale-95 text-white font-bold py-3 px-4 rounded-xl transition-all duration-150 shadow-lg hover:shadow-xl" data-difficulty="Extreme">Extreme</button>
			<button class="difficulty-select col-span-2 w-full bg-indigo-500 hover:bg-indigo-600 active:bg-indigo-700 active:scale-95 text-white font-bold py-3 px-4 rounded-xl transition-all duration-150 shadow-lg hover:shadow-xl" data-difficulty="Test">Test</button>
		</div>
	</div>
</div>

<div id="menu-backdrop" class="fixed inset-0 z-0 hidden bg-black/20 backdrop-blur-sm"></div>
</html>