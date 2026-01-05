

document.addEventListener('DOMContentLoaded', function() {
	const bodyEl = document.body
	bodyEl.insertAdjacentHTML('beforeend', `
		<footer class="fixed bottom-0 left-0 right-0 bg-white/80 backdrop-blur-sm shadow-lg">
			<!-- main, daily changes, me -->
			<div class="max-w-2xl mx-auto flex justify-between items-center p-4 mb-2 landscape:p-1 landscape:mb-0">
				<button id="footer-main" class="text-indigo-600 font-semibold hover:underline flex flex-col items-center text-xl landscape:text-sm">
					<svg class="w-8 h-8" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640"><path fill="#4f39f6" d="M341.8 72.6C329.5 61.2 310.5 61.2 298.3 72.6L74.3 280.6C64.7 289.6 61.5 303.5 66.3 315.7C71.1 327.9 82.8 336 96 336L112 336L112 512C112 547.3 140.7 576 176 576L464 576C499.3 576 528 547.3 528 512L528 336L544 336C557.2 336 569 327.9 573.8 315.7C578.6 303.5 575.4 289.5 565.8 280.6L341.8 72.6zM304 384L336 384C362.5 384 384 405.5 384 432L384 528L256 528L256 432C256 405.5 277.5 384 304 384z"/></svg>
					Main
				</button>
				<button id="footer-daily-challenge" class="text-indigo-600 font-semibold hover:underline flex flex-col items-center text-xl landscape:text-sm">
					<svg class="w-8 h-8" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640"><path fill="#4f39f6" d="M416 64C433.7 64 448 78.3 448 96L448 128L480 128C515.3 128 544 156.7 544 192L544 480C544 515.3 515.3 544 480 544L160 544C124.7 544 96 515.3 96 480L96 192C96 156.7 124.7 128 160 128L192 128L192 96C192 78.3 206.3 64 224 64C241.7 64 256 78.3 256 96L256 128L384 128L384 96C384 78.3 398.3 64 416 64zM438 225.7C427.3 217.9 412.3 220.3 404.5 231L285.1 395.2L233 343.1C223.6 333.7 208.4 333.7 199.1 343.1C189.8 352.5 189.7 367.7 199.1 377L271.1 449C276.1 454 283 456.5 289.9 456C296.8 455.5 303.3 451.9 307.4 446.2L443.3 259.2C451.1 248.5 448.7 233.5 438 225.7z"/></svg>
					Daily Challenge
				</button>
				<button id="footer-me" class="text-indigo-600 font-semibold hover:underline flex flex-col items-center text-xl landscape:text-sm">
					<svg class="w-8 h-8" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640"><path fill="#4f39f6" d="M463 448.2C440.9 409.8 399.4 384 352 384L288 384C240.6 384 199.1 409.8 177 448.2C212.2 487.4 263.2 512 320 512C376.8 512 427.8 487.3 463 448.2zM64 320C64 178.6 178.6 64 320 64C461.4 64 576 178.6 576 320C576 461.4 461.4 576 320 576C178.6 576 64 461.4 64 320zM320 336C359.8 336 392 303.8 392 264C392 224.2 359.8 192 320 192C280.2 192 248 224.2 248 264C248 303.8 280.2 336 320 336z"/></svg>
					Me
				</button>
			</div>
		</footer>
	`)

	const mainBtnEl = document.getElementById('footer-main')
	const dailyChallengeBtnEl = document.getElementById('footer-daily-challenge')
	const meBtnEl = document.getElementById('footer-me')

	const isLoggedIn = document.querySelector('meta[name="is-logged-in"]')?.content === 'true'
	if (!isLoggedIn) {
		// dailyChallengeBtnEl.disabled = true
		dailyChallengeBtnEl.classList.add('opacity-50', 'cursor-not-allowed')
		dailyChallengeBtnEl.title = "Log in to play daily challenges"
	}

	mainBtnEl.addEventListener('click', function() {
		if (window.location.pathname === '/') {
			return
		}
		window.location.href = '/'
	})

	dailyChallengeBtnEl.addEventListener('click', function() {
		if (!isLoggedIn) {
			const modal = document.getElementById('confirmation-modal')
			if (modal) {
				const titleEl = document.getElementById('confirmation-title')
				const msgEl = document.getElementById('confirmation-message')
				const confirmBtn = document.getElementById('confirmation-confirm')
				const cancelBtn = document.getElementById('confirmation-cancel')
				const iconEl = document.getElementById('confirmation-icon')

				titleEl.textContent = "Login Required"
				msgEl.textContent = "You need to be logged in to play daily challenges."
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
			} else {
				if (confirm("You need to be logged in to play daily challenges. Go to login page?")) {
					window.location.href = '/login'
				}
			}
			return
		}
		window.location.href = '/play?type=daily'
	})

	meBtnEl.addEventListener('click', function() {
		if (window.location.pathname === '/me') {
			return
		}
		window.location.href = '/me'
	})
})