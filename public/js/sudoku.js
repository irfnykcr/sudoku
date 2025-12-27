/** @typedef {0|1|2|3|4|5|6|7|8|9} Digit */
/** @typedef {'Easy'|'Medium'|'Hard'|'Extreme'} Difficulty */
/** @typedef {{ puzzle: Digit[], solution: Digit[], values: Digit[], notesMask: number[], difficulty: Difficulty, elapsedSeconds: number, notesMode: boolean, version: number }} SavedState */
/** @typedef {{ difficulty?: Difficulty, gameName?: string }} BootOptions */

const SudokuApp = (() => {
	const VERSION = 1
	const SIZE = 9
	const CELL_COUNT = SIZE * SIZE
	const ALL_MASK = 0b111111111
	const SETTINGS_VERSION = 1
	const SETTINGS_KEY = `sudoku:settings:v${SETTINGS_VERSION}`

	const storageKeyFor = gameName => `sudoku:game:${gameName}:v${VERSION}`

	const clampInt = n => {
		const num = typeof n === 'number' ? n : Number(n)
		const v = Number.isFinite(num) ? Math.trunc(num) : 0
		return v
	}

	const rowOf = idx => Math.floor(idx / 9)
	const colOf = idx => idx % 9
	const boxOf = idx => Math.floor(rowOf(idx) / 3) * 3 + Math.floor(colOf(idx) / 3)

	const shuffleInPlace = a => {
		for (let i = a.length - 1; i > 0; i -= 1) {
			const j = Math.floor(Math.random() * (i + 1))
			const tmp = a[i]
			a[i] = a[j]
			a[j] = tmp
		}
		return a
	}

	const popCount9 = m => {
		let x = m & ALL_MASK
		let c = 0
		while (x) {
			c += x & 1
			x >>= 1
		}
		return c
	}

	const lowestBit = m => m & -m
	const bitToDigit = bit => Math.log2(bit) + 1

	const Peers = (() => {
		const peers = Array.from({ length: CELL_COUNT }, () => [])
		for (let idx = 0; idx < CELL_COUNT; idx += 1) {
			const r = rowOf(idx)
			const c = colOf(idx)
			const b = boxOf(idx)
			const set = new Set()
			for (let i = 0; i < 9; i += 1) {
				set.add(r * 9 + i)
				set.add(i * 9 + c)
			}
			const br = Math.floor(b / 3) * 3
			const bc = (b % 3) * 3
			for (let rr = br; rr < br + 3; rr += 1) {
				for (let cc = bc; cc < bc + 3; cc += 1) set.add(rr * 9 + cc)
			}
			set.delete(idx)
			peers[idx] = Array.from(set)
		}
		return { peers }
	})()

	class SudokuRules {
		static conflicts(board) {
			const bad = new Set()
			const addDupes = indices => {
				const seen = new Map()
				for (const idx of indices) {
					const v = board[idx]
					if (!v) continue
					const arr = seen.get(v) ?? []
					arr.push(idx)
					seen.set(v, arr)
				}
				for (const arr of seen.values()) {
					if (arr.length > 1) for (const idx of arr) bad.add(idx)
				}
			}
			for (let r = 0; r < 9; r += 1) addDupes(Array.from({ length: 9 }, (_, i) => r * 9 + i))
			for (let c = 0; c < 9; c += 1) addDupes(Array.from({ length: 9 }, (_, i) => i * 9 + c))
			for (let br = 0; br < 3; br += 1) {
				for (let bc = 0; bc < 3; bc += 1) {
					const indices = []
					for (let rr = br * 3; rr < br * 3 + 3; rr += 1) {
						for (let cc = bc * 3; cc < bc * 3 + 3; cc += 1) indices.push(rr * 9 + cc)
					}
					addDupes(indices)
				}
			}
			return bad
		}

		static isSolved(board) {
			for (let i = 0; i < CELL_COUNT; i += 1) if (!board[i]) return false
			return SudokuRules.conflicts(board).size === 0
		}
	}

	class SudokuSolver {
		static candidatesMask(board, idx) {
			let used = 0
			for (const p of Peers.peers[idx]) {
				const v = board[p]
				if (v) used |= 1 << (v - 1)
			}
			return (~used) & ALL_MASK
		}

		static solve(start) {
			const board = start.slice()
			const ok = SudokuSolver.#search(board, false)
			return ok ? board : null
		}

		static countSolutions(start, limit = 2) {
			const board = start.slice()
			return SudokuSolver.#searchCount(board, limit)
		}

		static #selectCell(board) {
			let bestIdx = -1
			let bestCount = 10
			let bestMask = 0
			for (let idx = 0; idx < CELL_COUNT; idx += 1) {
				if (board[idx]) continue
				const mask = SudokuSolver.candidatesMask(board, idx)
				const c = popCount9(mask)
				if (c === 0) return { idx, mask, count: 0 }
				if (c < bestCount) {
					bestIdx = idx
					bestCount = c
					bestMask = mask
					if (c === 1) break
				}
			}
			return { idx: bestIdx, mask: bestMask, count: bestCount }
		}

		static #search(board, randomize) {
			const { idx, mask, count } = SudokuSolver.#selectCell(board)
			if (idx === -1) return true
			if (count === 0) return false

			const bits = []
			let m = mask
			while (m) {
				const bit = lowestBit(m)
				bits.push(bit)
				m ^= bit
			}
			if (randomize) shuffleInPlace(bits)

			for (const bit of bits) {
				board[idx] = bitToDigit(bit)
				if (SudokuSolver.#search(board, randomize)) return true
				board[idx] = 0
			}
			return false
		}

		static #searchCount(board, limit) {
			const { idx, mask, count } = SudokuSolver.#selectCell(board)
			if (idx === -1) return 1
			if (count === 0) return 0

			let total = 0
			let m = mask
			while (m) {
				const bit = lowestBit(m)
				m ^= bit
				board[idx] = bitToDigit(bit)
				total += SudokuSolver.#searchCount(board, limit - total)
				board[idx] = 0
				if (total >= limit) return total
			}
			return total
		}

		static fillRandom(board) {
			const b = board.slice()
			const ok = SudokuSolver.#search(b, true)
			return ok ? b : null
		}
	}

	class SudokuGenerator {
		static #difficultyTargets() {
			return {
				Easy: 78,
				Medium: 34,
				Hard: 28,
				Extreme: 22,
			}
		}

		/** @param {Difficulty} difficulty */
		static generate(difficulty) {
			const targets = SudokuGenerator.#difficultyTargets()
			const givensTarget = targets[difficulty] ?? targets.Medium

			const empty = Array.from({ length: CELL_COUNT }, () => 0)
			const solution = SudokuSolver.fillRandom(empty)
			if (!solution) throw new Error('Failed to generate solution')

			const puzzle = solution.slice()
			const indices = shuffleInPlace(Array.from({ length: CELL_COUNT }, (_, i) => i))
			let givens = CELL_COUNT

			for (const idx of indices) {
				if (givens <= givensTarget) break
				const prev = puzzle[idx]
				puzzle[idx] = 0
				const count = SudokuSolver.countSolutions(puzzle, 2)
				if (count !== 1) puzzle[idx] = prev
				else givens -= 1
			}

			return { puzzle, solution, difficulty }
		}
	}

	class GameClock {
		#startMs = 0
		#elapsedMs = 0
		#timer = null
		#onTick

		constructor(onTick) {
			this.#onTick = onTick
		}

		start() {
			if (this.#timer) return
			this.#startMs = Date.now()
			this.#timer = window.setInterval(() => {
				this.#onTick(this.elapsedSeconds())
			}, 250)
		}

		stop() {
			if (!this.#timer) return
			window.clearInterval(this.#timer)
			this.#timer = null
			this.#elapsedMs += Date.now() - this.#startMs
		}

		resetTo(seconds) {
			this.#elapsedMs = Math.max(0, clampInt(seconds)) * 1000
			this.#startMs = Date.now()
			this.#onTick(this.elapsedSeconds())
		}

		elapsedSeconds() {
			const running = this.#timer ? Date.now() - this.#startMs : 0
			return Math.floor((this.#elapsedMs + running) / 1000)
		}
	}

	class UndoStack {
		#items = []
		#limit

		constructor(limit = 200) {
			this.#limit = limit
		}

		push(action) {
			this.#items.push(action)
			if (this.#items.length > this.#limit) this.#items.shift()
		}

		pop() {
			return this.#items.pop() ?? null
		}

		clear() {
			this.#items = []
		}
	}

	class SudokuGame {
		puzzle
		solution
		values
		fixed
		notesMask
		notesMode = false
		difficulty = 'Medium'
		undo = new UndoStack(300)

		constructor(spec) {
			this.puzzle = spec.puzzle.slice()
			this.solution = spec.solution.slice()
			this.values = spec.puzzle.slice()
			this.fixed = this.puzzle.map(v => Boolean(v))
			this.notesMask = Array.from({ length: CELL_COUNT }, () => 0)
			this.difficulty = /** @type {Difficulty} */ (spec.difficulty)
		}

		reset() {
			this.values = this.puzzle.slice()
			this.notesMask = Array.from({ length: CELL_COUNT }, () => 0)
			this.notesMode = false
			this.undo.clear()
		}

		canEdit(idx) {
			return !this.fixed[idx]
		}

		valueAt(idx) {
			return this.values[idx]
		}

		notesAt(idx) {
			return this.notesMask[idx]
		}

		setValue(idx, value) {
			if (!this.canEdit(idx)) return false
			const prevValue = this.values[idx]
			const prevNotes = this.notesMask[idx]
			if (prevValue === value) return false

			this.values[idx] = value
			if (value) this.notesMask[idx] = 0

			this.undo.push({
				type: 'value',
				idx,
				prevValue,
				prevNotes,
				nextValue: value,
				nextNotes: this.notesMask[idx],
			})
			return true
		}

		toggleNote(idx, n) {
			if (!this.canEdit(idx)) return false
			if (this.values[idx]) return false
			const bit = 1 << (n - 1)
			const prev = this.notesMask[idx]
			const next = prev ^ bit
			if (prev === next) return false
			this.notesMask[idx] = next
			this.undo.push({ type: 'note', idx, prevMask: prev, nextMask: next })
			return true
		}

		erase(idx) {
			if (!this.canEdit(idx)) return false
			if (this.values[idx] === 0 && this.notesMask[idx] === 0) return false
			const prevValue = this.values[idx]
			const prevNotes = this.notesMask[idx]
			this.values[idx] = 0
			this.notesMask[idx] = 0
			this.undo.push({
				type: 'erase',
				idx,
				prevValue,
				prevNotes,
			})
			return true
		}

		undoOne() {
			const action = this.undo.pop()
			if (!action) return false
			if (!this.canEdit(action.idx)) return false
			if (action.type === 'value') {
				this.values[action.idx] = action.prevValue
				this.notesMask[action.idx] = action.prevNotes
				return true
			}
			if (action.type === 'note') {
				this.notesMask[action.idx] = action.prevMask
				return true
			}
			if (action.type === 'erase') {
				this.values[action.idx] = action.prevValue
				this.notesMask[action.idx] = action.prevNotes
				return true
			}
			return false
		}
	}

	class Storage {
		#key

		constructor(key) {
			this.#key = key
		}

		load() {
			try {
				const raw = localStorage.getItem(this.#key)
				if (!raw) return null
				/** @type {SavedState} */
				const data = JSON.parse(raw)
				if (!data || data.version !== VERSION) return null
				if (!Array.isArray(data.puzzle) || !Array.isArray(data.solution) || !Array.isArray(data.values)) return null
				if (data.puzzle.length !== CELL_COUNT || data.solution.length !== CELL_COUNT || data.values.length !== CELL_COUNT) return null
				const puzzle = data.puzzle.map(clampInt)
				const solution = data.solution.map(clampInt)
				const values = data.values.map(clampInt)
				const notesMask = Array.isArray(data.notesMask) && data.notesMask.length === CELL_COUNT ? data.notesMask.map(clampInt) : Array.from({ length: CELL_COUNT }, () => 0)
				const difficulty = typeof data.difficulty === 'string' ? data.difficulty : 'Medium'
				const elapsedSeconds = clampInt(data.elapsedSeconds)
				const notesMode = Boolean(data.notesMode)

				const isDigit = v => Number.isInteger(v) && v >= 0 && v <= 9
				if (!puzzle.every(isDigit) || !solution.every(isDigit) || !values.every(isDigit)) return null
				const givens = puzzle.reduce((acc, v) => acc + (v ? 1 : 0), 0)
				if (givens === 0) return null
				if (SudokuRules.conflicts(puzzle).size !== 0) return null
				if (!SudokuRules.isSolved(solution)) return null
				for (let i = 0; i < CELL_COUNT; i += 1) {
					if (puzzle[i] && puzzle[i] !== solution[i]) return null
					if (values[i] && values[i] !== solution[i] && puzzle[i]) return null
				}

				return { puzzle, solution, values, notesMask, difficulty, elapsedSeconds, notesMode }
			} catch {
				return null
			}
		}

		save(s) {
			try {
				const payload = {
					version: VERSION,
					difficulty: s.game.difficulty,
					elapsedSeconds: s.elapsedSeconds,
					notesMode: s.game.notesMode,
					puzzle: s.game.puzzle,
					solution: s.game.solution,
					values: s.game.values,
					notesMask: s.game.notesMask,
				}
				localStorage.setItem(this.#key, JSON.stringify(payload))
			} catch {
				return
			}
		}

		clear() {
			try {
				localStorage.removeItem(this.#key)
			} catch {
				return
			}
		}
	}

	class SudokuUI {
		#gridEl
		#difficultyEl
		#timerEl
		#undoBtn
		#eraseBtn
		#notesBtn
		#numberBtns
		#backBtn
		#settingsBtn
		#drawerOpenBtn
		#drawerEl
		#drawerReset
		#drawerNewGame
		#resetBtn
		#settingsDrawerEl
		#settingsCloseBtn
		#selectionHighlightToggle

		#cellEls = []
		#selectedIdx = 0
		#game
		#clock
		#saveTimer = null
		#bootDifficulty
		#storage
		#completedUnits = new Set()
		#glowTokenByIdx = Array.from({ length: CELL_COUNT }, () => 0)
		#nextGlowToken = 1
		#solvedShown = false
		#locked = false
		#settings = { selectionHighlight: true }

		/** @param {Required<BootOptions>} options */
		constructor(options) {
			this.#bootDifficulty = options.difficulty
			this.#storage = new Storage(storageKeyFor(options.gameName))
			this.#gridEl = document.getElementById('sudoku-grid')
			this.#difficultyEl = document.getElementById('game-difficulty')
			this.#timerEl = document.getElementById('game-timer')
			this.#undoBtn = document.getElementById('undo-button')
			this.#eraseBtn = document.getElementById('erase-button')
			this.#notesBtn = document.getElementById('notes-toggle-button')
			this.#numberBtns = Array.from(document.querySelectorAll('.number-button'))
			this.#backBtn = document.getElementById('back-button')
			this.#settingsBtn = document.getElementById('settings-button')
			this.#drawerOpenBtn = document.getElementById('drawer-open')
			this.#drawerEl = document.getElementById('actions-drawer')
			this.#drawerReset = document.getElementById('drawer-reset')
			this.#drawerNewGame = document.getElementById('drawer-newgame')
			this.#resetBtn = document.getElementById('reset-button')
			this.#settingsDrawerEl = document.getElementById('settings-drawer')
			this.#settingsCloseBtn = document.getElementById('settings-close')
			this.#selectionHighlightToggle = document.getElementById('setting-selection-highlight')

			if (!(this.#gridEl instanceof HTMLElement)) throw new Error('Missing #sudoku-grid')
			if (!(this.#difficultyEl instanceof HTMLElement)) throw new Error('Missing #game-difficulty')
			if (!(this.#timerEl instanceof HTMLElement)) throw new Error('Missing #game-timer')
			if (!(this.#undoBtn instanceof HTMLButtonElement)) throw new Error('Missing #undo-button')
			if (!(this.#eraseBtn instanceof HTMLButtonElement)) throw new Error('Missing #erase-button')
			if (!(this.#notesBtn instanceof HTMLButtonElement)) throw new Error('Missing #notes-toggle-button')

			this.#clock = new GameClock(elapsed => {
				this.#timerEl.textContent = SudokuUI.formatTime(elapsed)
				this.#scheduleSave()
			})

			this.#settings = this.#loadSettings()
			this.#applySettingsToUI()

			this.#game = this.#loadOrCreateGame()
			this.#applyLoadedStateToUI()
			this.#buildGrid()
			this.#locked = this.#isCompleted()
			this.#solvedShown = this.#locked
			this.#bindEvents()
			this.#render()
			this.#updateInteractionState()
			if (!this.#locked) this.#clock.start()
		}

		#isCompleted() {
			if (!SudokuRules.isSolved(this.#game.values)) return false
			for (let i = 0; i < CELL_COUNT; i += 1) {
				if (this.#game.values[i] !== this.#game.solution[i]) return false
			}
			return true
		}

		#applyDisabled(btn, disabled) {
			if (!(btn instanceof HTMLButtonElement)) return
			btn.disabled = disabled
			btn.classList.toggle('opacity-50', disabled)
			btn.classList.toggle('cursor-not-allowed', disabled)
		}

		#updateInteractionState() {
			const disabled = this.#locked
			if (this.#gridEl instanceof HTMLElement) {
				this.#gridEl.classList.toggle('pointer-events-none', disabled)
				this.#gridEl.classList.toggle('opacity-75', disabled)
			}
			this.#applyDisabled(this.#undoBtn, disabled)
			this.#applyDisabled(this.#eraseBtn, disabled)
			this.#applyDisabled(this.#notesBtn, disabled)
			for (const b of this.#numberBtns) this.#applyDisabled(b, disabled)
		}

		#loadSettings() {
			try {
				const raw = localStorage.getItem(SETTINGS_KEY)
				if (!raw) return { selectionHighlight: true }
				const data = JSON.parse(raw)
				if (!data || typeof data !== 'object') return { selectionHighlight: true }
				return {
					selectionHighlight: Boolean(data.selectionHighlight),
				}
			} catch {
				return { selectionHighlight: true }
			}
		}

		#saveSettings() {
			try {
				localStorage.setItem(SETTINGS_KEY, JSON.stringify(this.#settings))
			} catch {
				return
			}
		}

		#applySettingsToUI() {
			if (this.#selectionHighlightToggle instanceof HTMLInputElement) {
				this.#selectionHighlightToggle.checked = Boolean(this.#settings.selectionHighlight)
			}
		}

		#openDrawer() {
			if (!(this.#drawerEl instanceof HTMLElement)) return
			this.#drawerEl.classList.remove('hidden')
		}

		#closeDrawer() {
			if (!(this.#drawerEl instanceof HTMLElement)) return
			this.#drawerEl.classList.add('hidden')
		}

		#toggleDrawer() {
			if (!(this.#drawerEl instanceof HTMLElement)) return
			const open = !this.#drawerEl.classList.contains('hidden')
			if (open) this.#closeDrawer()
			else {
				this.#closeSettings()
				this.#openDrawer()
			}
		}

		#openSettings() {
			if (!(this.#settingsDrawerEl instanceof HTMLElement)) return
			this.#closeDrawer()
			this.#settingsDrawerEl.classList.remove('hidden')
		}

		#closeSettings() {
			if (!(this.#settingsDrawerEl instanceof HTMLElement)) return
			this.#settingsDrawerEl.classList.add('hidden')
		}

		#toggleSettings() {
			if (!(this.#settingsDrawerEl instanceof HTMLElement)) return
			const open = !this.#settingsDrawerEl.classList.contains('hidden')
			if (open) this.#closeSettings()
			else this.#openSettings()
		}

		#unitKey(kind, index) {
			return `${kind}:${index}`
		}

		#isUnitComplete(indices) {
			let mask = 0
			for (const idx of indices) {
				const v = this.#game.valueAt(idx)
				if (!v) return false
				mask |= 1 << (v - 1)
			}
			return mask === ALL_MASK
		}

		#glowCells(indices) {
			this.#glowCellsFor(indices, 650)
		}

		#glowCellsFor(indices, durationMs) {
			const token = this.#nextGlowToken
			this.#nextGlowToken += 1
			for (const idx of indices) {
				if (idx < 0 || idx >= CELL_COUNT) continue
				this.#glowTokenByIdx[idx] = token
			}
			this.#render()
			window.setTimeout(() => {
				let changed = false
				for (const idx of indices) {
					if (idx < 0 || idx >= CELL_COUNT) continue
					if (this.#glowTokenByIdx[idx] !== token) continue
					this.#glowTokenByIdx[idx] = 0
					changed = true
				}
				if (changed) this.#render()
			}, Math.max(0, clampInt(durationMs)))
		}

		#checkAndAnimateCompletions(affectedIdx) {
			const r = rowOf(affectedIdx)
			const c = colOf(affectedIdx)
			const b = boxOf(affectedIdx)
			const rowIndices = Array.from({ length: 9 }, (_, i) => r * 9 + i)
			const colIndices = Array.from({ length: 9 }, (_, i) => i * 9 + c)
			const br = Math.floor(b / 3) * 3
			const bc = (b % 3) * 3
			const boxIndices = []
			for (let rr = br; rr < br + 3; rr += 1) {
				for (let cc = bc; cc < bc + 3; cc += 1) boxIndices.push(rr * 9 + cc)
			}
			const candidates = [
				{ key: this.#unitKey('r', r), indices: rowIndices },
				{ key: this.#unitKey('c', c), indices: colIndices },
				{ key: this.#unitKey('b', b), indices: boxIndices },
			]
			for (const u of candidates) {
				if (this.#completedUnits.has(u.key)) continue
				if (!this.#isUnitComplete(u.indices)) continue
				this.#completedUnits.add(u.key)
				this.#glowCells(u.indices)
			}
		}

		static formatTime(totalSeconds) {
			const s = Math.max(0, clampInt(totalSeconds))
			const mm = String(Math.floor(s / 60)).padStart(2, '0')
			const ss = String(s % 60).padStart(2, '0')
			return `${mm}:${ss}`
		}

		#loadOrCreateGame() {
			const loaded = this.#storage.load()
			if (loaded) {
				const game = new SudokuGame({ puzzle: loaded.puzzle, solution: loaded.solution, difficulty: loaded.difficulty })
				game.values = loaded.values.map(v => Math.max(0, Math.min(9, v)))
				game.fixed = game.puzzle.map(v => Boolean(v))
				game.notesMask = loaded.notesMask.map(m => m & ALL_MASK)
				game.notesMode = loaded.notesMode
				game.difficulty = loaded.difficulty
				this.#clock.resetTo(loaded.elapsedSeconds)
				return game
			}
			const spec = SudokuGenerator.generate(this.#bootDifficulty)
			this.#clock.resetTo(0)
			return new SudokuGame(spec)
		}

		#applyLoadedStateToUI() {
			this.#difficultyEl.textContent = this.#game.difficulty
			this.#notesBtn.textContent = this.#game.notesMode ? 'Notes: On' : 'Notes: Off'
			this.#timerEl.textContent = SudokuUI.formatTime(this.#clock.elapsedSeconds())
		}

		#buildGrid() {
			this.#gridEl.textContent = ''
			this.#cellEls = []
			for (let idx = 0; idx < CELL_COUNT; idx += 1) {
				const cell = document.createElement('button')
				cell.type = 'button'
				cell.dataset.idx = String(idx)
				cell.className = this.#cellBaseClass(idx)
				cell.tabIndex = -1
				this.#gridEl.appendChild(cell)
				this.#cellEls.push(cell)
			}
			const firstEditable = this.#findFirstEditableIndex()
			this.#selectCell(firstEditable ?? 0)
		}

		#cellBaseClass(idx) {
			const r = rowOf(idx)
			const c = colOf(idx)
			const thickL = c % 3 === 0
			const thickT = r % 3 === 0
			const thickR = c === 8
			const thickB = r === 8

			const border = ['border', 'border-indigo-200']
			if (thickL) border.push('border-l-2', 'border-l-indigo-400')
			if (thickT) border.push('border-t-2', 'border-t-indigo-400')
			if (thickR) border.push('border-r-2', 'border-r-indigo-400')
			if (thickB) border.push('border-b-2', 'border-b-indigo-400')

			return [
				'relative',
				'flex',
				'items-center',
				'justify-center',
				'bg-white',
				'rounded-sm',
				...border,
				'h-9',
				'w-9',
				'sm:h-11',
				'sm:w-11',
				'md:h-12',
				'md:w-12',
				'select-none',
				'focus:outline-none',
				'transition-colors',
			].join(' ')
		}

		#bindEvents() {
			this.#gridEl.addEventListener('click', e => {
				if (this.#locked) return
				const btn = e.target instanceof Element ? e.target.closest('button') : null
				if (!btn) return
				const idx = clampInt(btn.dataset.idx)
				if (idx < 0 || idx >= CELL_COUNT) return
				this.#selectCell(idx)
			})

			window.addEventListener('keydown', e => {
				if (this.#locked) return
				if (e.defaultPrevented) return
				if (e.ctrlKey || e.metaKey || e.altKey) return
				const t = e.target
				if (t instanceof HTMLInputElement || t instanceof HTMLTextAreaElement || t instanceof HTMLSelectElement) return

				const key = e.key
				if (key >= '1' && key <= '9') {
					e.preventDefault()
					this.#inputDigit(clampInt(key))
					return
				}
				if (key === 'Backspace' || key === 'Delete' || key === '0') {
					e.preventDefault()
					if (!this.#ensureEditableSelection()) return
					if (this.#game.erase(this.#selectedIdx)) {
						this.#render()
						this.#scheduleSave()
					}
					return
				}

				const code = e.code
				if (typeof code === 'string' && code.startsWith('Numpad')) {
					const d = clampInt(code.slice('Numpad'.length))
					if (d >= 1 && d <= 9) {
						e.preventDefault()
						this.#inputDigit(d)
						return
					}
					if (d === 0) {
						e.preventDefault()
						if (!this.#ensureEditableSelection()) return
						if (this.#game.erase(this.#selectedIdx)) {
							this.#render()
							this.#scheduleSave()
						}
					}
				}
			})

			this.#undoBtn.addEventListener('click', () => {
				if (this.#locked) return
				if (this.#game.undoOne()) {
					this.#render()
					this.#scheduleSave()
					this.#checkAndAnimateCompletions(this.#selectedIdx)
				}
			})

			this.#eraseBtn.addEventListener('click', () => {
				if (this.#locked) return
				if (!this.#ensureEditableSelection()) return
				if (this.#game.erase(this.#selectedIdx)) {
					this.#render()
					this.#scheduleSave()
					this.#checkAndAnimateCompletions(this.#selectedIdx)
				}
			})

			this.#notesBtn.addEventListener('click', () => {
				if (this.#locked) return
				this.#game.notesMode = !this.#game.notesMode
				this.#notesBtn.textContent = this.#game.notesMode ? 'Notes: On' : 'Notes: Off'
				this.#scheduleSave()
			})

			if (this.#drawerOpenBtn instanceof HTMLButtonElement) {
				this.#drawerOpenBtn.addEventListener('click', () => this.#toggleDrawer())
			}
			if (this.#drawerReset instanceof HTMLButtonElement) {
				this.#drawerReset.addEventListener('click', () => {
					const ok = window.confirm('Reset this game?')
					if (!ok) return
					this.#closeDrawer()
					this.#resetGameToPuzzle()
				})
			}
			if (this.#drawerNewGame instanceof HTMLButtonElement) {
				this.#drawerNewGame.addEventListener('click', () => {
					const ok = window.confirm('Start a new game?')
					if (!ok) return
					this.#closeDrawer()
					this.#newGame(this.#bootDifficulty)
				})
			}

			if (this.#settingsBtn instanceof HTMLButtonElement) {
				this.#settingsBtn.addEventListener('click', () => this.#toggleSettings())
			}
			if (this.#settingsCloseBtn instanceof HTMLButtonElement) {
				this.#settingsCloseBtn.addEventListener('click', () => this.#closeSettings())
			}
			if (this.#selectionHighlightToggle instanceof HTMLInputElement) {
				this.#selectionHighlightToggle.addEventListener('change', () => {
					this.#settings.selectionHighlight = Boolean(this.#selectionHighlightToggle.checked)
					this.#saveSettings()
					this.#render()
				})
			}

			if (this.#resetBtn instanceof HTMLButtonElement) {
				this.#resetBtn.addEventListener('click', () => {
					const ok = window.confirm('Reset this game?')
					if (!ok) return
					this.#resetGameToPuzzle()
				})
			}

			for (const btn of this.#numberBtns) {
				if (!(btn instanceof HTMLButtonElement)) continue
				btn.addEventListener('click', () => {
					if (this.#locked) return
					const raw = btn.dataset.number
					const n = clampInt(raw)
					if (n < 1 || n > 9) return
					this.#inputDigit(n)
				})
			}

			if (this.#backBtn instanceof HTMLButtonElement) {
				this.#backBtn.addEventListener('click', () => {
					history.back()
				})
			}

			document.addEventListener('click', e => {
				const t = e.target instanceof Node ? e.target : null
				if (!t) return
				if (this.#drawerEl instanceof HTMLElement && !this.#drawerEl.classList.contains('hidden')) {
					if (this.#drawerEl.contains(t)) return
					if (this.#drawerOpenBtn instanceof HTMLElement && this.#drawerOpenBtn.contains(t)) return
					this.#closeDrawer()
				}
				if (this.#settingsDrawerEl instanceof HTMLElement && !this.#settingsDrawerEl.classList.contains('hidden')) {
					if (this.#settingsDrawerEl.contains(t)) return
					if (this.#settingsBtn instanceof HTMLElement && this.#settingsBtn.contains(t)) return
					this.#closeSettings()
				}
			})

			window.addEventListener('beforeunload', () => this.#saveNow())
		}

		#resetGameToPuzzle() {
			this.#game.reset()
			this.#completedUnits = new Set()
			this.#solvedShown = false
			this.#locked = false
			this.#difficultyEl.textContent = this.#game.difficulty
			this.#notesBtn.textContent = 'Notes: Off'
			this.#clock.stop()
			this.#clock.resetTo(0)
			this.#render()
			this.#updateInteractionState()
			this.#clock.start()
			this.#saveNow()
		}

		/** @param {Difficulty} difficulty */
		#newGame(difficulty) {
			this.#storage.clear()
			const spec = SudokuGenerator.generate(difficulty)
			this.#game = new SudokuGame(spec)
			this.#completedUnits = new Set()
			this.#solvedShown = false
			this.#locked = false
			this.#clock.stop()
			this.#clock.resetTo(0)
			this.#applyLoadedStateToUI()
			this.#buildGrid()
			this.#render()
			this.#updateInteractionState()
			this.#clock.start()
			this.#saveNow()
		}

		#findFirstEditableIndex() {
			for (let i = 0; i < CELL_COUNT; i += 1) {
				if (this.#game.canEdit(i) && this.#game.valueAt(i) === 0) return i
			}
			for (let i = 0; i < CELL_COUNT; i += 1) {
				if (this.#game.canEdit(i)) return i
			}
			return null
		}

		#ensureEditableSelection() {
			if (this.#game.canEdit(this.#selectedIdx)) return true
			const next = this.#findFirstEditableIndex()
			if (next == null) return false
			this.#selectedIdx = next
			return true
		}

		#inputDigit(n) {
			if (this.#locked) return
			if (!this.#ensureEditableSelection()) return
			let changed = false
			if (this.#game.notesMode) changed = this.#game.toggleNote(this.#selectedIdx, n)
			else changed = this.#game.setValue(this.#selectedIdx, n)
			if (!changed) return
			this.#render()
			this.#scheduleSave()
			this.#checkAndAnimateCompletions(this.#selectedIdx)
			this.#maybeSolved()
		}

		#maybeSolved() {
			if (this.#solvedShown) return
			if (!SudokuRules.isSolved(this.#game.values)) return
			for (let i = 0; i < CELL_COUNT; i += 1) {
				if (this.#game.values[i] !== this.#game.solution[i]) return
			}
			this.#solvedShown = true
			this.#locked = true
			this.#clock.stop()
			this.#saveNow()
			this.#updateInteractionState()
			const all = Array.from({ length: CELL_COUNT }, (_, i) => i)
			const durationMs = 850
			this.#glowCellsFor(all, durationMs)
			window.setTimeout(() => {
				window.alert('Completed')
			}, durationMs + 50)
		}

		#selectCell(idx) {
			if (this.#locked) return
			this.#selectedIdx = idx
			this.#render()
		}

		#scheduleSave() {
			if (this.#saveTimer) return
			this.#saveTimer = window.setTimeout(() => {
				this.#saveTimer = null
				this.#saveNow()
			}, 400)
		}

		#saveNow() {
			this.#storage.save({ game: this.#game, elapsedSeconds: this.#clock.elapsedSeconds() })
		}

		#render() {
			const conflicts = SudokuRules.conflicts(this.#game.values)
			const selected = this.#selectedIdx
			const selectedVal = this.#game.valueAt(selected)
			const selRow = rowOf(selected)
			const selCol = colOf(selected)
			const selBox = boxOf(selected)

			for (let idx = 0; idx < CELL_COUNT; idx += 1) {
				const cell = this.#cellEls[idx]
				const v = this.#game.valueAt(idx)
				const isFixed = this.#game.fixed[idx]
				const isSelected = idx === selected
				const sameUnit = rowOf(idx) === selRow || colOf(idx) === selCol || boxOf(idx) === selBox
				const sameNumber = selectedVal && v === selectedVal
				const isConflict = conflicts.has(idx)
				const isGlow = this.#glowTokenByIdx[idx] !== 0

				const classes = new Set(this.#cellBaseClass(idx).split(' '))
				if (sameUnit && this.#settings.selectionHighlight) {
					classes.add('ring-2')
					classes.add('ring-indigo-200')
				}
				if (sameNumber) classes.add('bg-amber-100')
				if (isSelected) {
					classes.add('ring-2')
					classes.add('ring-indigo-600')
					classes.add('bg-indigo-200')
					classes.add('z-10')
				}
				if (isFixed) {
					classes.add('font-extrabold')
					classes.add('text-indigo-900')
					classes.add('bg-indigo-100')
				} else {
					classes.add('font-bold')
					classes.add('text-indigo-700')
				}
				if (isConflict) {
					classes.add('text-red-600')
					classes.add('bg-red-50')
				}
				if (isGlow && !isConflict) {
					classes.add('shadow-lg')
					classes.add('shadow-emerald-500/30')
				}
				cell.className = Array.from(classes).join(' ')
				cell.replaceChildren()

				if (isGlow && !isConflict) {
					const overlay = document.createElement('div')
					overlay.className = 'pointer-events-none absolute inset-0 bg-emerald-200/70'
					cell.appendChild(overlay)
				}

				if (v) {
					const span = document.createElement('span')
					span.textContent = String(v)
					span.className = 'relative z-10 text-lg md:text-xl'
					cell.appendChild(span)
					continue
				}

				const notes = this.#game.notesAt(idx)
				if (!notes) {
					cell.textContent = ''
					continue
				}
				const notesGrid = document.createElement('div')
				notesGrid.className = 'relative z-10 grid grid-cols-3 gap-x-0.5 gap-y-0 text-[10px] leading-3 text-gray-500'
				for (let n = 1; n <= 9; n += 1) {
					const item = document.createElement('div')
					item.className = 'text-center'
					item.textContent = (notes & (1 << (n - 1))) ? String(n) : ''
					notesGrid.appendChild(item)
				}
				cell.appendChild(notesGrid)
			}
		}
	}

	/** @param {BootOptions} [options] */
	const boot = options => {
		const difficulty = options?.difficulty ?? 'Easy'
		const gameName = typeof options?.gameName === 'string' && options.gameName.trim() ? options.gameName.trim() : 'normal'
		try {
			new SudokuUI({ difficulty, gameName })
		} catch (e) {
			const msg = e instanceof Error ? e.message : String(e)
			console.error('Sudoku init failed:', msg)
		}
	}

	return { boot }
})()

document.addEventListener('DOMContentLoaded', () => SudokuApp.boot({ difficulty: 'Easy', gameName: 'normal' }))