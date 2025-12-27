<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="/js/sudoku.js"></script>
	<script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <title>sudoku</title>
</head>
<header>
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
				<svg xmlns="http://www.w3.org/2000/svg" class="inline h-8 w-8" viewBox="0 0 20 20" fill="currentColor">
					<path d="M11.983 3.513a1.75 1.75 0 00-3.966 0l-.259.518a1.75 1.75 0 01-2.487.773l-.518-.26a1.75 1.75 0 00-2.487.773l-.518.259a1.75 1.75 0 000 3.966l.518.26a1.75 1.75 0 01.773 2.487l-.26.518a1.75 1.75 0 000 3.966l.259.518a1.75 1.75 0 002.487.773l.518-.26a1.75 1.75 0 012.487.773l.259.518a1.75 1.75 0 003.966 0l.26-.518a1.75 1.75 0 01.773-2.487l.518.26a1.75 1.75 0 002.487-.773l.518-.259a1.75 1.75 0 000-3.966l-.518-.26a1.75 1.75 0 01-.773-2.487l.26-.518a1.75 1.75 0 000-3.966l-.259-.518a1.75 1.75 0 00-2.773-.773l-.518.26a1.75 1.75 0 01-2.487-.773l-.259-.518zM10 13a3 3 0 100-6 3 3 0 000 6z" />
				</svg>
				Settings
			</button>
		</div>
	</div>
</header>
<body class="bg-gradient-to-br from-amber-50 to-amber-100 min-h-screen flex items-center justify-center p-4">
    <main class="flex flex-col w-full max-w-2xl mx-auto space-y-8 items-center">
		<!-- head: difficulty, time, reset -->
		<div class="pt-20 w-full flex flex-col sm:flex-row sm:justify-between sm:items-center px-4 gap-3">
			<p class="text-lg text-indigo-600 font-semibold">Difficulty: <span id="game-difficulty">Easy</span></p>

			<div class="flex items-center justify-between sm:justify-end gap-3">
				<p class="text-lg text-indigo-600 font-semibold">Time: <span id="game-timer">00:00</span></p>
				<button id="drawer-open" class="bg-white hover:bg-indigo-50 active:bg-indigo-100 active:scale-95 text-indigo-700 font-bold py-2 px-4 rounded-xl transition-all duration-150 shadow-lg hover:shadow-xl">
					Menu
				</button>
			</div>
		</div>

		<!-- Sudoku Grid -->
		<div id="sudoku-grid" class="min-w-[356px] grid grid-cols-9 gap-1 bg-white p-2 rounded-lg shadow-lg"></div>

		<!-- actions: undo, erease, notes-on/off -->
		<div class="flex justify-between w-full max-w-md px-4">
			<button id="undo-button" class="w-24 bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 active:scale-95 text-white font-bold py-2 px-4 rounded-xl transition-all duration-150 shadow-lg hover:shadow-xl">
				Undo
			</button>
			<button id="erase-button" class="w-24 bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 active:scale-95 text-white font-bold py-2 px-4 rounded-xl transition-all duration-150 shadow-lg hover:shadow-xl">
				Erase
			</button>
			<button id="notes-toggle-button" class="w-24 bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 active:scale-95 text-white font-bold py-2 px-4 rounded-xl transition-all duration-150 shadow-lg hover:shadow-xl">
				Notes: Off
			</button>
		</div>

		<!-- number pad -->
		<div class="grid grid-cols-9 gap-2 w-full max-w-md px-4">
			@for ($i = 1; $i <= 9; $i++)
				<button class="number-button w-8 sm:w-10 h-12 bg-white hover:bg-indigo-50 active:bg-indigo-100 active:scale-95 text-indigo-600 font-bold rounded-xl transition-all duration-150 shadow-lg hover:shadow-xl" data-number="{{ $i }}">
					<p>{{ $i }}</p>
				</button>
			@endfor
		</div>
    </main>

	<div id="actions-drawer" class="fixed top-24 left-4 right-4 sm:left-auto sm:right-4 sm:w-80 hidden z-50">
		<div class="bg-white rounded-2xl shadow-2xl p-4">
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
</body>
</html>