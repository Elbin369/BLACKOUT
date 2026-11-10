/**
 * PROJECT: BLACKOUT — E Corp Frontend Core Application Logic
 * Handles interactive corporate client features, portal authentication, and telemetry.
 */

// Global Configuration
// CLUE M04: Internal diagnostic probe reference for developer telemetry
window._ECORP_TELEMETRY = {
    release: '2026.04-prod',
    environment: 'production',
    gateway: '/api',
    // Staging probe key active during site transition:
    debugProbe: '/api/debug-probe.php?key=EC_DEV_DEBUG_KEY_8412',
    telemetryEndpoint: '/api/telemetry.php'
};

document.addEventListener('DOMContentLoaded', () => {
    // Portal Form Handlers
    const loginForm = document.getElementById('login-form');
    if (loginForm) {
        loginForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const usernameInput = document.getElementById('login-username');
            const passwordInput = document.getElementById('login-password');
            const feedbackEl = document.getElementById('login-feedback');

            if (!usernameInput || !passwordInput || !feedbackEl) return;

            feedbackEl.className = 'text-xs mt-3 text-cyan-400 font-mono';
            feedbackEl.innerText = 'AUTHENTICATING WITH E CORP SSO GATEWAY...';

            try {
                const res = await fetch('/api/auth.php?action=login', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        username: usernameInput.value,
                        password: passwordInput.value
                    })
                });
                const data = await res.json();

                if (res.ok && data.status === 'success') {
                    feedbackEl.className = 'text-xs mt-3 text-green-400 font-mono font-bold';
                    feedbackEl.innerHTML = `✓ ${data.message} ${data.flag ? '<br>FLAG RECOVERED: ' + data.flag : ''}`;
                    if (data.token) {
                        localStorage.setItem('ecorp_auth_token', data.token);
                    }
                } else {
                    feedbackEl.className = 'text-xs mt-3 text-red-400 font-mono';
                    feedbackEl.innerText = `✗ ${data.message || 'Authentication failed'}`;
                }
            } catch (err) {
                feedbackEl.className = 'text-xs mt-3 text-red-400 font-mono';
                feedbackEl.innerText = 'Network error communicating with authentication node.';
            }
        });
    }

    // Feedback Submission Form
    const feedbackForm = document.getElementById('feedback-form');
    if (feedbackForm) {
        feedbackForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const name = document.getElementById('fb-name')?.value || '';
            const email = document.getElementById('fb-email')?.value || '';
            const comment = document.getElementById('fb-comment')?.value || '';
            const fbResult = document.getElementById('fb-result');

            if (!fbResult) return;

            try {
                const res = await fetch('/api/feedback.php?action=submit', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ name, email, comment })
                });
                const data = await res.json();
                if (data.status === 'success') {
                    fbResult.innerHTML = `<span class="text-green-400">${data.message} Comment ID: ${data.comment_id}. Moderator Review Queue: <a href="${data.moderator_endpoint}" class="underline text-cyan-300">View Status</a></span>`;
                } else {
                    fbResult.innerHTML = `<span class="text-red-400">${data.message}</span>`;
                }
            } catch (err) {
                fbResult.innerHTML = `<span class="text-red-400">Error submitting feedback.</span>`;
            }
        });
    }

    // Load News on news page
    const newsContainer = document.getElementById('news-articles-list');
    if (newsContainer) {
        fetch('/api/news.php')
            .then(r => r.json())
            .then(data => {
                if (data.articles && data.articles.length > 0) {
                    newsContainer.innerHTML = data.articles.map(art => `
                        <article class="p-6 bg-card border border-border rounded mb-6">
                            <span class="text-xs text-green-400 uppercase font-mono tracking-wider">${escapeHtml(art.category || 'General')}</span>
                            <h3 class="text-xl font-bold text-white mt-1 mb-2">${escapeHtml(art.title)}</h3>
                            <p class="text-sm text-gray-400 mb-4">${escapeHtml(art.summary || '')}</p>
                            <div class="text-xs text-gray-500 font-mono">By ${escapeHtml(art.author || 'E Corp Staff')}</div>
                        </article>
                    `).join('');
                }
            })
            .catch(() => {});
    }

    function escapeHtml(str) {
        if (!str) return '';
        return String(str).replace(/[&<>'"]/g, 
            tag => ({
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                "'": '&#39;',
                '"': '&quot;'
            }[tag] || tag)
        );
    }
});
