


document.addEventListener('DOMContentLoaded', async function() {
	const dailyButtonEl = document.getElementById('daily-challange-play')
	const continueButtonEl = document.getElementById('continue-game')
	const newButtonEl = document.getElementById('new-game')
	const continueInfoEl = document.getElementById('continue-info')
	const continueInfoTextEl = document.getElementById('continue-info-text')
	
	const bestScoreEl = document.getElementById('best-score')
	
	try {
		// fetch user stats
		const statsResponse = await fetch('/api/user/stats', {
			headers: { 'Accept': 'application/json' },
			credentials: 'include'
		})
		if (statsResponse.ok) {
			const stats = await statsResponse.json()
			bestScoreEl.textContent = stats.best_score.toLocaleString()
		}

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
			// continueButtonEl.disabled = true
			continueButtonEl.classList.add('opacity-50', 'cursor-login')
			continueInfoEl.classList.add('hidden')
			continueButtonEl.title = "Log in to continue your saved game"
			
			continueButtonEl.addEventListener('click', function(e) {
				e.preventDefault()
				e.stopImmediatePropagation()
				showLoginAlert("You need to be logged in to continue your saved game.")
			})
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


	const dailyModal = document.getElementById('daily-modal')
	const dailyModalClose = document.getElementById('daily-modal-close')
	const dailyCalendarGrid = document.getElementById('daily-calendar-grid')
    const dailyPrevMonth = document.getElementById('daily-prev-month')
    const dailyNextMonth = document.getElementById('daily-next-month')
    const dailyMonthLabel = document.getElementById('daily-month-label')

    let currentYear = new Date().getFullYear()
    let currentMonth = new Date().getMonth() + 1

	const isLoggedIn = document.querySelector('meta[name="is-logged-in"]')?.content === 'true'
	
	const showLoginAlert = (message) => {
		const modal = document.getElementById('confirmation-modal')
		const titleEl = document.getElementById('confirmation-title')
		const msgEl = document.getElementById('confirmation-message')
		const confirmBtn = document.getElementById('confirmation-confirm')
		const cancelBtn = document.getElementById('confirmation-cancel')
		const iconEl = document.getElementById('confirmation-icon')

		if (!modal) {
			alert(message)
			return
		}

		titleEl.textContent = "Login Required"
		msgEl.textContent = message
		confirmBtn.textContent = "Log In"
		cancelBtn.textContent = "Cancel"
		iconEl.textContent = "🔒"

		const close = () => {
			modal.classList.add('hidden')
			confirmBtn.onclick = null
			cancelBtn.onclick = null
			modal.onclick = null
		}

		confirmBtn.onclick = () => {
			window.location.href = '/login'
		}

		cancelBtn.onclick = close
		
		modal.onclick = (e) => {
			if (e.target === modal) close()
		}

		modal.classList.remove('hidden')
	}

	if (!isLoggedIn) {
		// dailyButtonEl.disabled = true
		dailyButtonEl.classList.add('opacity-50', 'cursor-not-allowed')
		dailyButtonEl.title = "Log in to play daily challenges"
	}

	dailyButtonEl.addEventListener('click', function() {
		if (!isLoggedIn) {
			showLoginAlert("You need to be logged in to play daily challenges.")
			return
		}
		dailyModal.classList.remove('hidden')
        currentYear = new Date().getFullYear()
        currentMonth = new Date().getMonth() + 1
		fetchCalendar()
	})

	dailyModalClose.addEventListener('click', function() {
		dailyModal.classList.add('hidden')
	})

	dailyModal.addEventListener('click', function(e) {
		if (e.target === dailyModal) {
			dailyModal.classList.add('hidden')
		}
	})

    dailyPrevMonth.addEventListener('click', () => {
        currentMonth--
        if (currentMonth < 1) {
            currentMonth = 12
            currentYear--
        }
        fetchCalendar()
    })

    dailyNextMonth.addEventListener('click', () => {
        currentMonth++
        if (currentMonth > 12) {
            currentMonth = 1
            currentYear++
        }
        fetchCalendar()
    })

	async function fetchCalendar() {
		try {
            const monthName = new Date(currentYear, currentMonth - 1).toLocaleString('default', { month: 'long' })
            dailyMonthLabel.textContent = `${monthName} ${currentYear}`

			const response = await fetch(`/api/game/calendar?year=${currentYear}&month=${currentMonth}`, {
				headers: { 'Accept': 'application/json' },
				credentials: 'include'
			})
			if (response.ok) {
				const data = await response.json()
				renderCalendar(data.calendar)
			}
		} catch (e) {
			console.error('Failed to fetch calendar:', e)
		}
	}

	function renderCalendar(data) {
		dailyCalendarGrid.innerHTML = ''
		
        const daysInMonth = new Date(currentYear, currentMonth, 0).getDate()
        const firstDate = new Date(currentYear, currentMonth - 1, 1)
        const startDay = firstDate.getDay() // 0 = Sun, 1 = Mon...
        
        // Headers
		const days = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun']
		days.forEach(d => {
			const el = document.createElement('div')
			el.className = 'text-center text-xs font-bold text-gray-500 py-1'
			el.textContent = d
			dailyCalendarGrid.appendChild(el)
		})

        // Empty slots (Mon start)
		const emptySlots = (startDay + 6) % 7
		for (let i = 0; i < emptySlots; i++) {
			const el = document.createElement('div')
			dailyCalendarGrid.appendChild(el)
		}

        const today = new Date()
        today.setHours(0, 0, 0, 0)

        for (let day = 1; day <= daysInMonth; day++) {
            const dateObj = new Date(currentYear, currentMonth - 1, day)
            const dateStr = `${currentYear}-${String(currentMonth).padStart(2, '0')}-${String(day).padStart(2, '0')}`
            
            const btn = document.createElement('button')
			btn.className = 'aspect-square rounded-lg flex items-center justify-center text-sm font-semibold transition-all duration-200 relative'
			btn.textContent = day

            // Determine status
            let status = 'new'
            if (dateObj > today) {
                status = 'future'
            } else if (data[dateStr]) {
                status = data[dateStr].status
            }

			if (status === 'future') {
				btn.classList.add('bg-gray-100', 'text-gray-400', 'cursor-not-allowed')
				btn.disabled = true
			} else {
				btn.classList.add('hover:scale-110')
				
				if (status === 'completed') {
					btn.classList.add('bg-green-500', 'text-white', 'shadow-md')
				} else if (status === 'in_progress') {
					btn.classList.add('bg-yellow-400', 'text-white', 'shadow-md')
				} else {
					btn.classList.add('bg-indigo-100', 'text-indigo-700', 'hover:bg-indigo-200')
				}

				btn.addEventListener('click', () => {
					window.location.href = `/play?type=daily&date=${dateStr}`
				})
			}

			dailyCalendarGrid.appendChild(btn)
        }
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
})