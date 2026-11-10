/**
 * PROJECT: BLACKOUT — System Boot & Initialization Sequence
 */

document.addEventListener('DOMContentLoaded', () => {
    const overlay = document.getElementById('init-overlay');
    const logEl = document.getElementById('init-log');
    const progressBar = document.getElementById('init-progress');

    // Check if user already saw the boot sequence in this session
    const hasBooted = sessionStorage.getItem('blackout_booted');
    const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    if (hasBooted || prefersReducedMotion) {
        if (overlay) {
            overlay.classList.add('hidden');
        }
        return;
    }

    const bootSteps = [
        { text: 'INITIALIZING BLACKOUT SYSTEM...', delay: 300, progress: 20 },
        { text: 'ESTABLISHING SECURE TLS TUNNEL...', delay: 400, progress: 45 },
        { text: 'LOADING CORPORATE NETWORK ASSETS...', delay: 400, progress: 75 },
        { text: 'VERIFYING PERIMETER DEFENSE NODES...', delay: 350, progress: 90 },
        { text: 'SYSTEM STATUS: ONLINE [READY]', delay: 300, progress: 100 }
    ];

    let currentStep = 0;

    function runStep() {
        if (currentStep < bootSteps.length) {
            const step = bootSteps[currentStep];
            const line = document.createElement('div');
            line.textContent = `> ${step.text}`;
            if (logEl) {
                logEl.appendChild(line);
            }
            if (progressBar) {
                progressBar.style.width = `${step.progress}%`;
            }
            currentStep++;
            setTimeout(runStep, step.delay);
        } else {
            setTimeout(() => {
                if (overlay) {
                    overlay.style.transition = 'opacity 0.6s ease';
                    overlay.style.opacity = '0';
                    setTimeout(() => {
                        overlay.classList.add('hidden');
                        sessionStorage.setItem('blackout_booted', 'true');
                    }, 600);
                }
            }, 500);
        }
    }

    setTimeout(runStep, 200);
});
