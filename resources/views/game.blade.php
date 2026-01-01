<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="/js/sudoku.js"></script>
	<script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <title>sudoku</title>
</head>
<header class="z-99">
	<!-- left:back button, right:settings -->
	<div class="fixed top-0 left-0 w-full h-18  bg-white backdrop-blur-sm shadow-lg">
		<div class="flex justify-between items-center pl-1 pr-1">
			<button id="back-button" class="text-indigo-600 text-lg pl-2 mt-2 font-semibold hover:underline flex flex-col items-center">
				<svg xmlns="http://www.w3.org/2000/svg" class="inline h-8 w-8" viewBox="0 0 20 20" fill="currentColor">
					<path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
				</svg>
				Back
			</button>
			<button id="settings-button" class="text-indigo-600 text-lg font-semibold hover:underline flex flex-col items-center">
				<svg class="w-8 h-8" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640"><path fill="#4f39f6" d="M259.1 73.5C262.1 58.7 275.2 48 290.4 48L350.2 48C365.4 48 378.5 58.7 381.5 73.5L396 143.5C410.1 149.5 423.3 157.2 435.3 166.3L503.1 143.8C517.5 139 533.3 145 540.9 158.2L570.8 210C578.4 223.2 575.7 239.8 564.3 249.9L511 297.3C511.9 304.7 512.3 312.3 512.3 320C512.3 327.7 511.8 335.3 511 342.7L564.4 390.2C575.8 400.3 578.4 417 570.9 430.1L541 481.9C533.4 495 517.6 501.1 503.2 496.3L435.4 473.8C423.3 482.9 410.1 490.5 396.1 496.6L381.7 566.5C378.6 581.4 365.5 592 350.4 592L290.6 592C275.4 592 262.3 581.3 259.3 566.5L244.9 496.6C230.8 490.6 217.7 482.9 205.6 473.8L137.5 496.3C123.1 501.1 107.3 495.1 99.7 481.9L69.8 430.1C62.2 416.9 64.9 400.3 76.3 390.2L129.7 342.7C128.8 335.3 128.4 327.7 128.4 320C128.4 312.3 128.9 304.7 129.7 297.3L76.3 249.8C64.9 239.7 62.3 223 69.8 209.9L99.7 158.1C107.3 144.9 123.1 138.9 137.5 143.7L205.3 166.2C217.4 157.1 230.6 149.5 244.6 143.4L259.1 73.5zM320.3 400C364.5 399.8 400.2 363.9 400 319.7C399.8 275.5 363.9 239.8 319.7 240C275.5 240.2 239.8 276.1 240 320.3C240.2 364.5 276.1 400.2 320.3 400z"/></svg>
				Settings
			</button>
		</div>
	</div>
</header>
<body class="bg-linear-to-br from-amber-50 to-amber-100 min-h-screen flex items-center justify-center p-2 sm:p-4">
    <main id="main-content" class="flex flex-col w-full max-w-5xl mx-auto space-y-2 sm:space-y-8 items-center">
		<!-- head: difficulty, time, reset -->
		<div class="pt-16 sm:pt-20 w-full flex flex-col sm:flex-row sm:justify-between sm:items-center px-4 gap-1 sm:gap-3">
			<p class="text-lg text-indigo-600 font-semibold">Difficulty: <span id="game-difficulty">Easy</span></p>

			<div class="flex items-center justify-between sm:justify-end gap-3">
				<p class="text-lg text-indigo-600 font-semibold">Time: <span id="game-timer">00:00</span></p>
				<div class="relative">
					<button id="drawer-open" class="bg-white hover:bg-indigo-50 active:bg-indigo-100 active:scale-95 text-indigo-700 font-bold py-2 px-4 rounded-xl transition-all duration-150 shadow-lg hover:shadow-xl">
						Menu
					</button>
					<div id="actions-drawer" class="absolute top-full right-0 mt-2 w-64 sm:w-80 hidden z-50">
						<div class="bg-white rounded-2xl shadow-2xl p-4 ring-1 ring-black/5">
							<div class="grid grid-cols-2 gap-3">
								<button id="drawer-reset" class="w-full bg-white hover:bg-indigo-50 active:bg-indigo-100 active:scale-95 text-indigo-700 font-bold py-3 px-4 rounded-xl transition-all duration-150 shadow-lg hover:shadow-xl">
									Reset
								</button>
								<button id="drawer-newgame" class="w-full bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 active:scale-95 text-white font-bold py-3 px-4 rounded-xl transition-all duration-150 shadow-lg hover:shadow-xl">
									New Game
								</button>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>

		<div class="flex flex-col landscape:flex-row items-center landscape:items-center justify-center gap-4 sm:gap-8 w-full max-w-6xl">
			<!-- Sudoku Grid -->
			<div id="sudoku-grid" class="grid grid-cols-9 gap-0.5 sm:gap-1 bg-white p-1 sm:p-2 rounded-lg shadow-lg shrink-0"></div>

			<!-- Controls Wrapper -->
			<div class="flex flex-col space-y-4 sm:space-y-6 w-full max-w-md px-2 sm:px-4 landscape:max-w-xs">
				<!-- actions: undo, erease, notes-on/off -->
				<div class="flex justify-between w-full gap-2">
					<button id="undo-button" class="flex-1 bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 active:scale-95 text-white font-bold py-2 px-1 sm:px-4 rounded-xl transition-all duration-150 shadow-lg hover:shadow-xl text-xs sm:text-base">
						Undo
					</button>
					<button id="erase-button" class="flex-1 bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 active:scale-95 text-white font-bold py-2 px-1 sm:px-4 rounded-xl transition-all duration-150 shadow-lg hover:shadow-xl text-xs sm:text-base">
						Erase
					</button>
					<button id="notes-toggle-button" class="flex-1 bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 active:scale-95 text-white font-bold py-2 px-1 sm:px-4 rounded-xl transition-all duration-150 shadow-lg hover:shadow-xl text-xs sm:text-base">
						Notes: Off
					</button>
				</div>

				<!-- number pad -->
				<div class="grid grid-cols-9 landscape:grid-cols-3 gap-2 w-full">
					@for ($i = 1; $i <= 9; $i++)
						<button class="number-button w-full aspect-square sm:aspect-auto sm:h-12 bg-white hover:bg-indigo-50 active:bg-indigo-100 active:scale-95 text-indigo-600 font-bold rounded-xl transition-all duration-150 shadow-lg hover:shadow-xl flex items-center justify-center" data-number="{{ $i }}">
							<p class="text-sm sm:text-lg">{{ $i }}</p>
						</button>
					@endfor
				</div>
			</div>
		</div>
    </main>

	<div id="settings-drawer" class="fixed bottom-3 left-0 right-0 hidden px-4 z-50">
		<div class="max-w-md mx-auto bg-white rounded-2xl shadow-2xl p-4 space-y-4">
			<div class="flex items-center justify-between">
				<p class="text-lg font-bold text-indigo-900">Settings</p>
				<button id="settings-close" class="bg-white hover:bg-indigo-50 active:bg-indigo-100 active:scale-95 text-indigo-700 font-bold py-2 px-3 rounded-xl transition-all duration-150 shadow-lg hover:shadow-xl">
					Close
				</button>
			</div>

			<label class="flex items-center justify-between gap-3">
				<div>
					<p class="font-semibold text-indigo-900">Selection highlight</p>
					<p class="text-sm text-indigo-700">Thicken borders for same row/col/box</p>
				</div>
				<input id="setting-selection-highlight" type="checkbox" class="h-5 w-5 accent-indigo-600" />
			</label>
		</div>
	</div>

	<div id="menu-backdrop" class="fixed hidden bg-black/20 backdrop-blur-sm"></div>
</body>
</html>