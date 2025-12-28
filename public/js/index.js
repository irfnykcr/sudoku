


document.addEventListener('DOMContentLoaded', function() {
	const dailyButtonEl = document.getElementById('daily-challange-play')
	const continueButtonEl = document.getElementById('continue-game')
	const newButtonEl = document.getElementById('new-game')
	const continueInfoEl = document.getElementById('continue-info')
	const continueInfoTextEl = document.getElementById('continue-info-text')
	
	if (localStorage.getItem('sudoku:game:normal:v1')) {
		const savedGame = localStorage.getItem('sudoku:game:normal:v1')
		const savedState = JSON.parse(savedGame)
		const minutes = Math.floor(savedState.elapsedSeconds / 60)
		const seconds = savedState.elapsedSeconds % 60
		const timeStr = String(minutes).padStart(2, '0') + ':' + String(seconds).padStart(2, '0')
		continueInfoTextEl.textContent = `${timeStr} - ${savedState.difficulty}`
		continueButtonEl.disabled = false
		continueButtonEl.classList.remove('opacity-50', 'cursor-not-allowed')
		continueInfoEl.classList.remove('hidden')
	} else {
		continueButtonEl.disabled = true
		continueButtonEl.classList.add('opacity-50', 'cursor-not-allowed')
		continueInfoEl.classList.add('hidden')
	}


	continueButtonEl.addEventListener('click', function() {
		if (!localStorage.getItem('sudoku:game:normal:v1')) {
			return
		}
		window.location.href = "/play"
	})
	
	const difficultyDrawerEl = document.getElementById('difficulty-drawer')
	const difficultyCloseBtn = document.getElementById('difficulty-close')
	const difficultySelectBtns = document.querySelectorAll('.difficulty-select')
	const menuBackdropEl = document.getElementById('menu-backdrop')

	function closeDifficultyDrawer() {
		difficultyDrawerEl.classList.add('hidden')
		if (menuBackdropEl) menuBackdropEl.classList.add('hidden')
	}
	function openDifficultyDrawer() {
		difficultyDrawerEl.classList.remove('hidden')
		if (menuBackdropEl) menuBackdropEl.classList.remove('hidden')
	}

	newButtonEl.addEventListener('click', function() {
		openDifficultyDrawer()
	})

	difficultyCloseBtn.addEventListener('click', function() {
		closeDifficultyDrawer()
	})

	if (menuBackdropEl) {
		menuBackdropEl.addEventListener('click', closeDifficultyDrawer)
	}

	document.addEventListener('keydown', function(e) {
		if (e.key === 'Escape' && !difficultyDrawerEl.classList.contains('hidden')) {
			closeDifficultyDrawer()
		}
	})

	difficultySelectBtns.forEach(btn => {
		btn.addEventListener('click', function() {
			if (localStorage.getItem('sudoku:game:normal:v1')) {
				const ok = window.confirm('A saved game exists. Starting a new game will overwrite the saved progress. Continue?')
				if (!ok) return
			}
			const difficulty = this.dataset.difficulty
			window.location.href = `/play?new=${difficulty}`
		})
	})


	dailyButtonEl.addEventListener('click', function() {
		window.alert("not implemented yet")
	})

})