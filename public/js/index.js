


document.addEventListener('DOMContentLoaded', async function() {
	const dailyButtonEl = document.getElementById('daily-challange-play')
	const continueButtonEl = document.getElementById('continue-game')
	const newButtonEl = document.getElementById('new-game')
	const continueInfoEl = document.getElementById('continue-info')
	const continueInfoTextEl = document.getElementById('continue-info-text')
	
	try {
		const response = await fetch('/api/game/load', {
			headers: { 'Accept': 'application/json' },
			credentials: 'include'
		})
		
		if (response.ok) {
			const data = await response.json()
			if (data.game && !data.game.isCompleted) {
				console.log("logged in and saved game found")
				const game = data.game
				const minutes = Math.floor(game.elapsedSeconds / 60)
				const seconds = game.elapsedSeconds % 60
				const timeStr = String(minutes).padStart(2, '0') + ':' + String(seconds).padStart(2, '0')
				continueInfoTextEl.textContent = `${timeStr} - ${game.difficulty}`
				continueButtonEl.disabled = false
				continueButtonEl.classList.remove('opacity-50', 'cursor-not-allowed')
				continueInfoEl.classList.remove('hidden')
			} else {
				console.log("logged in but no saved game found")
				continueButtonEl.disabled = true
				continueButtonEl.classList.add('opacity-50', 'cursor-not-allowed')
				continueInfoEl.classList.add('hidden')
			}
		} else if (response.status === 401) {
			console.log("not logged in")
			continueButtonEl.disabled = true
			continueButtonEl.classList.add('opacity-50', 'cursor-login')
			continueInfoEl.classList.add('hidden')
			continueButtonEl.title = "Log in to continue your saved game"
		} else {
			console.log("error checking saved game")
			continueButtonEl.disabled = true
			continueButtonEl.classList.add('opacity-50', 'cursor-not-allowed')
			continueInfoEl.classList.add('hidden')
		}
	} catch (e) {
		console.error('Failed to check saved game:', e)
		continueButtonEl.disabled = true
		continueButtonEl.classList.add('opacity-50', 'cursor-not-allowed')
		continueInfoEl.classList.add('hidden')
	}


	continueButtonEl.addEventListener('click', function() {
		if (continueButtonEl.disabled) return
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
			if (!continueButtonEl.disabled) {
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