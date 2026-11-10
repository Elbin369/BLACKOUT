/**
 * PROJECT: BLACKOUT — Simulated Interactive Terminal
 * Fully simulated in frontend JS — safe, sandboxed, responsive.
 */

document.addEventListener('DOMContentLoaded', () => {
    const termBody = document.getElementById('term-body');
    const termInput = document.getElementById('term-input');

    if (!termBody || !termInput) return;

    let commandHistory = [];
    let historyIndex = -1;

    const commands = {
        help: () => [
            '<span class="term-output-highlight">AVAILABLE TERMINAL COMMANDS:</span>',
            '  <span class="term-output-success">help</span>      - Display this list of operational commands',
            '  <span class="term-output-success">about</span>     - Overview of E Corp architecture & infrastructure',
            '  <span class="term-output-success">status</span>    - Display cluster and perimeter node health',
            '  <span class="term-output-success">whoami</span>    - Display current session authorization context',
            '  <span class="term-output-success">network</span>   - Display public routing and edge topology',
            '  <span class="term-output-success">scan</span>      - Run simulated edge boundary health probe',
            '  <span class="term-output-success">date</span>      - Display current UTC network synchronized time',
            '  <span class="term-output-success">clear</span>     - Clear terminal buffer output'
        ],
        about: () => [
            '<span class="term-output-highlight">E CORP ENTERPRISE FABRIC</span>',
            'E Corp is a premier global technology conglomerate providing autonomous enterprise infrastructure,',
            'distributed cloud fabrics, and resilient cyber defense solutions.',
            'Target Perimeter: Autonomous Gateway Node (Production Deployment 2026).'
        ],
        status: () => [
            '<span class="term-badge term-badge-info">STATUS</span> <span class="term-output-success">ALL SYSTEMS ONLINE</span>',
            'Core Cluster:      ecorp-dc-east-01 [ACTIVE]',
            'Ingress Gateway:   Port 80 / Port 443 [LISTENING]',
            'Internal Mesh:     Isolated Container Fabric [ENFORCED]',
            'Anomaly Sensor:    Autonomous Heuristic Filter v4.2 [ONLINE]'
        ],
        whoami: () => [
            'Current Identity:  <span class="term-output-highlight">guest.external@ecorp.perimeter</span>',
            'Authorization:     Level 0 (Unauthenticated Public Visitor)',
            'Session Token:     Cookie verification active on client headers.'
        ],
        network: () => [
            '<span class="term-output-highlight">ROUTING TOPOLOGY OVERVIEW:</span>',
            '  Edge Gateway:    10.0.0.1 (Ingress Balancer)',
            '  Services Mesh:   10.0.1.0/24 (API Microservices)',
            '  Internal Pod:    10.0.2.0/24 (Restricted Operations Node)',
            '  External DNS:    ns1.ecorp.corp, ns2.ecorp.corp'
        ],
        scan: () => [
            'Initiating boundary perimeter diagnostic...',
            '[✓] DNS Resolution verified: OK',
            '[✓] Edge Gateway Port 80/TCP: LISTENING',
            '[✓] REST API Router: ACTIVE',
            '[!] NOTICE: Public perimeter audit completed. No external breaches registered.'
        ],
        date: () => [
            'Network Synchronized Time: ' + new Date().toUTCString()
        ],
        clear: () => {
            termBody.innerHTML = '';
            return [];
        }
    };

    function appendLine(html) {
        const line = document.createElement('div');
        line.className = 'term-line';
        line.innerHTML = html;
        termBody.appendChild(line);
        termBody.scrollTop = termBody.scrollHeight;
    }

    termInput.addEventListener('keydown', (e) => {
        if (e.key === 'Enter') {
            const rawVal = termInput.value.trim();
            termInput.value = '';

            if (rawVal) {
                commandHistory.push(rawVal);
                historyIndex = commandHistory.length;
            }

            appendLine(`<span class="term-prompt">ecorp@blackout:~$</span> ${escapeHtml(rawVal)}`);

            if (!rawVal) return;

            const cmd = rawVal.toLowerCase().split(' ')[0];

            if (commands[cmd]) {
                const output = commands[cmd]();
                output.forEach(line => appendLine(line));
            } else {
                appendLine(`<span class="term-output-error">Command not recognized: "${escapeHtml(cmd)}". Type "help" for a list of commands.</span>`);
            }
        } else if (e.key === 'ArrowUp') {
            if (historyIndex > 0) {
                historyIndex--;
                termInput.value = commandHistory[historyIndex];
            }
        } else if (e.key === 'ArrowDown') {
            if (historyIndex < commandHistory.length - 1) {
                historyIndex++;
                termInput.value = commandHistory[historyIndex];
            } else {
                historyIndex = commandHistory.length;
                termInput.value = '';
            }
        }
    });

    function escapeHtml(str) {
        return str.replace(/[&<>'"]/g, 
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
