document.addEventListener('DOMContentLoaded', async function() {
    const achievementsContainer = document.getElementById('achievements-list')
    
    try {
        const response = await fetch('/api/user/stats', {
            headers: { 'Accept': 'application/json' },
            credentials: 'include'
        })
        
        if (response.ok) {
            const data = await response.json()
            
            document.getElementById('stat-best-score').textContent = data.best_score.toLocaleString()
            document.getElementById('stat-total-score').textContent = data.total_score.toLocaleString()
            
            const stats = data.stats || {}
            const total = Object.values(stats).reduce((a, b) => a + b, 0)
            document.getElementById('stat-games-completed').textContent = total.toLocaleString()
            
            const detailsContainer = document.getElementById('stats-details')
            if (detailsContainer) {
                detailsContainer.innerHTML = `
                    <div class="grid grid-cols-2 sm:grid-cols-5 gap-2 mt-4">
                        <div class="bg-green-50 p-2 rounded-lg text-center">
                            <div class="text-lg font-bold text-green-600">${stats.Easy || 0}</div>
                            <div class="text-xs text-gray-500">Easy</div>
                        </div>
                        <div class="bg-yellow-50 p-2 rounded-lg text-center">
                            <div class="text-lg font-bold text-yellow-600">${stats.Medium || 0}</div>
                            <div class="text-xs text-gray-500">Medium</div>
                        </div>
                        <div class="bg-orange-50 p-2 rounded-lg text-center">
                            <div class="text-lg font-bold text-orange-600">${stats.Hard || 0}</div>
                            <div class="text-xs text-gray-500">Hard</div>
                        </div>
                        <div class="bg-red-50 p-2 rounded-lg text-center">
                            <div class="text-lg font-bold text-red-600">${stats.Extreme || 0}</div>
                            <div class="text-xs text-gray-500">Extreme</div>
                        </div>
                        <div class="bg-indigo-50 p-2 rounded-lg text-center col-span-2 sm:col-span-1">
                            <div class="text-lg font-bold text-indigo-600">${stats.Daily || 0}</div>
                            <div class="text-xs text-gray-500">Daily</div>
                        </div>
                    </div>
                `
            }
            
            if (data.achievements && data.achievements.length > 0) {
                achievementsContainer.innerHTML = ''
                data.achievements.forEach(ach => {
                    const div = document.createElement('div')
                    div.className = 'flex items-center gap-4 bg-white p-4 rounded-xl shadow-sm border border-indigo-100'
                    div.innerHTML = `
                        <div class="text-3xl">🏆</div>
                        <div>
                            <h3 class="font-bold text-indigo-900">${ach.name}</h3>
                            <p class="text-sm text-indigo-600">${ach.description}</p>
                            <p class="text-xs text-gray-400 mt-1">Unlocked: ${new Date(ach.unlocked_at).toLocaleDateString()}</p>
                        </div>
                    `
                    achievementsContainer.appendChild(div)
                })
            } else {
                achievementsContainer.innerHTML = '<p class="text-gray-500 text-center italic py-8">No achievements yet. Keep playing!</p>'
            }
        }
    } catch (e) {
        console.error('Failed to load stats:', e)
        achievementsContainer.innerHTML = '<p class="text-red-500 text-center">Failed to load profile data.</p>'
    }
})
