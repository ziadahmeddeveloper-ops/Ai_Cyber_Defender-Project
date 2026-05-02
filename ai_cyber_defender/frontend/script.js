// AI Cyber Defender - Figma Matched Script
const userSession = JSON.parse(localStorage.getItem('aiUser')) || null;

document.addEventListener('DOMContentLoaded', () => {
    checkAuth();
    setupForms();
    
    if (userSession) {
        if (document.getElementById('dashTotalScans')) {
            updateDashboard();
            setInterval(updateDashboard, 5000);
            setupRealtime();
        }
        if (document.getElementById('historyTableBody')) loadHistory();
        if (document.getElementById('trendTableBody')) loadReports('daily');
    }
});

let realtimeActive = true;
let realtimeInterval = null;

function setupRealtime() {
    const toggle = document.getElementById('realtimeToggle');
    if (!toggle) return;

    // Start by default if it's the dashboard
    startRealtime();
    
    toggle.addEventListener('click', () => {
        realtimeActive = !realtimeActive;
        if (realtimeActive) {
            toggle.innerHTML = '<i class="fa-solid fa-bolt" style="color: var(--accent);"></i> REAL-TIME: ON';
            toggle.style.borderColor = 'var(--accent)';
            toggle.style.color = 'var(--accent)';
            startRealtime();
        } else {
            toggle.innerHTML = '<i class="fa-solid fa-bolt"></i> REAL-TIME: OFF';
            toggle.style.borderColor = '#555';
            toggle.style.color = '#888';
            stopRealtime();
        }
    });
}

function startRealtime() {
    realtimeInterval = setInterval(async () => {
        try {
            await fetch(`../api/simulate_log.php?user_id=${userSession.id}`);
            updateDashboard();
        } catch (e) {}
    }, 4000); // Send an automated log every 4 seconds
}

function stopRealtime() {
    if (realtimeInterval) clearInterval(realtimeInterval);
}

function checkAuth() {
    const authElements = document.querySelectorAll('.auth-only');
    const navActions = document.getElementById('navActions');
    const path = window.location.pathname;

    if (userSession) {
        if (authElements) authElements.forEach(el => el.style.display = 'block');
        if (navActions) {
            navActions.innerHTML = `
                <div style="display: flex; align-items: center; gap: 15px;">
                    <span style="color: var(--text-secondary); font-size: 0.9rem; font-weight: 600;">${userSession.email}</span>
                    <button class="btn btn-outline" style="padding: 0.5rem 1.2rem; font-size: 0.8rem;" onclick="logout()">LOGOUT</button>
                </div>
            `;
        }
        // Update hero buttons if on index.html
        const heroBtn = document.querySelector('.hero .btn-primary');
        if (heroBtn && heroBtn.innerText.includes('LAUNCH') || heroBtn && heroBtn.innerText.includes('SCAN')) {
            heroBtn.href = 'scan.html';
            heroBtn.innerText = 'GO TO SCANNER';
        }
    } else {
        if (['dashboard.html', 'scan.html', 'reports.html', 'profile.html'].some(p => path.includes(p))) {
            window.location.href = 'login.html';
        }
    }
}

function logout() {
    localStorage.removeItem('aiUser');
    window.location.href = 'index.html';
}

function setupForms() {
    const loginForm = document.getElementById('loginForm');
    if (loginForm) {
        loginForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const email = document.getElementById('loginEmail').value;
            const password = document.getElementById('loginPassword').value;
            const res = await fetch('../api/login.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ email, password })
            });
            const data = await res.json();
            if (res.ok) {
                localStorage.setItem('aiUser', JSON.stringify(data.user));
                window.location.href = 'dashboard.html';
            } else { alert(data.message); }
        });
    }

    const scanForm = document.getElementById('scanForm');
    if (scanForm) {
        scanForm.addEventListener('submit', (e) => handleScan(e, 'url'));
    }
    const logScanForm = document.getElementById('logScanForm');
    if (logScanForm) {
        logScanForm.addEventListener('submit', (e) => handleScan(e, 'log'));
    }
}

async function performScan(type, btn) {
    const inputId = type === 'url' ? 'urlInput' : 'logInput';
    const input = document.getElementById(inputId);
    const target = input.value;
    
    if (!target) {
        alert("Please enter a target to scan.");
        return;
    }

    const originalText = btn.innerText;
    btn.innerText = 'ANALYZING...';
    btn.disabled = true;

    try {
        const res = await fetch('../api/ai.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ scan_target: target, scan_type: type, user_id: userSession.id })
        });
        const data = await res.json();
        if (res.ok) {
            displayResults(data.data, target);
        } else {
            alert(data.message);
        }
    } catch(err) { 
        console.error(err);
        alert("Analysis failed."); 
    } finally {
        btn.innerText = originalText;
        btn.disabled = false;
    }
}

async function handleScan(e, type) {
    e.preventDefault();
    const btn = e.target.querySelector('button');
    performScan(type, btn);
}

async function updateDashboard() {
    try {
        const res = await fetch(`../api/stats.php?user_id=${userSession.id}`);
        const data = await res.json();
        document.getElementById('dashTotalScans').innerText = data.total_scans;
        document.getElementById('dashMalicious').innerText = data.malicious_count;
        document.getElementById('dashSafe').innerText = data.safe_count;
        document.getElementById('dashUsers').innerText = data.active_users;
        
        const container = document.getElementById('latestThreatsContainer');
        if (container && data.latest_threats) {
            container.innerHTML = data.latest_threats.map(t => {
                // Prepare a detailed object for the modal
                const detailObj = {
                    prediction: t.status || 'malicious',
                    attack_type: t.scan_type === 'url' ? 'Phishing / Malware' : 'Brute Force / SQLi',
                    threat_score: 85 + Math.floor(Math.random() * 14),
                    attacker_ip: '185.234.' + Math.floor(Math.random() * 255) + '.' + Math.floor(Math.random() * 255),
                    username: 'User_' + Math.floor(Math.random() * 999),
                    host: 'Web-Server-01',
                    source_type: t.scan_type || 'system'
                };

                return `
                <div style="padding: 1.2rem; background: #131A2D; border-radius: 12px; margin-bottom: 15px; border-left: 4px solid #FF4D4D; display: flex; justify-content: space-between; align-items: center; border: 1px solid rgba(255,255,255,0.03);">
                    <div>
                        <div style="font-weight: 700; color: white; font-size: 0.95rem; margin-bottom: 6px;">Live Threat Detected</div>
                        <div style="color: var(--text-secondary); font-size: 0.8rem;">Target: <span style="color: var(--accent);">${t.scan_target}</span></div>
                        <div style="font-size: 0.7rem; color: var(--text-secondary); margin-top: 5px; opacity: 0.6;">${new Date(t.created_at).toLocaleString()}</div>
                    </div>
                    <button class="btn btn-outline" style="padding: 0.5rem 1.2rem; font-size: 0.7rem;" onclick='showFullDetails(${JSON.stringify(detailObj)})'>DETAILS</button>
                </div>
            `;
            }).join('') || '<div style="text-align:center; color:var(--text-secondary); padding: 2rem;">No active threats detected.</div>';
        }
        drawCharts(data);
    } catch (e) {}
}

let charts = {};
function drawCharts(data) {
    const trendCtx = document.getElementById('scanTrendChart');
    if (trendCtx) {
        if (!charts.trend) {
            charts.trend = new Chart(trendCtx, {
                type: 'line',
                data: { labels: ['Phase 1', 'Phase 2', 'Current'], datasets: [{ label: 'Activity', data: [0, data.total_scans / 2, data.total_scans], borderColor: '#00D1FF', backgroundColor: 'rgba(0, 209, 255, 0.1)', fill: true, tension: 0.4 }] },
                options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { display: false }, x: { grid: { display: false } } } }
            });
        } else {
            charts.trend.data.datasets[0].data = [0, data.total_scans / 2, data.total_scans];
            charts.trend.update();
        }
    }
    const distCtx = document.getElementById('threatDistributionChart');
    if (distCtx) {
        if (!charts.dist) {
            charts.dist = new Chart(distCtx, {
                type: 'doughnut',
                data: { labels: ['Secure', 'Threats'], datasets: [{ data: [data.safe_count, data.malicious_count], backgroundColor: ['#00E676', '#FF4D4D'], borderWidth: 0 }] },
                options: { responsive: true, maintainAspectRatio: false, cutout: '80%' }
            });
        } else {
            charts.dist.data.datasets[0].data = [data.safe_count, data.malicious_count];
            charts.dist.update();
        }
    }
}

function displayResults(data, target) {
    const scanResult = document.getElementById('scanResult');
    const tableBody = document.getElementById('resultTableBody');
    
    if (scanResult && tableBody) {
        scanResult.style.display = 'block';
        const isMalicious = data.prediction === 'malicious' || data.prediction === 'anomaly';
        const statusColor = isMalicious ? '#FF4D4D' : '#00E676';
        
        let currentScanData = data;
        tableBody.innerHTML = `
            <tr>
                <td style="font-weight: 700; color: white;">${target}</td>
                <td>${data.source_type || 'SYSTEM'}</td>
                <td style="color: ${statusColor}; font-weight: 800;">${data.prediction.toUpperCase()}</td>
                <td><span style="background: rgba(255,255,255,0.05); padding: 4px 10px; border-radius: 4px;">${data.threat_score}%</span></td>
                <td><button class="btn btn-outline" style="padding: 4px 12px; font-size: 0.7rem;" onclick='showFullDetails(${JSON.stringify(data).replace(/'/g, "&apos;")})'>DETAILS</button></td>
            </tr>
        `;
        scanResult.scrollIntoView({ behavior: 'smooth' });
        return;
    }

    const resSection = document.getElementById('resultSection');
    if (!resSection) return;
    resSection.style.display = 'block';
    resSection.scrollIntoView({ behavior: 'smooth' });

    const isMalicious = data.prediction === 'malicious' || data.prediction === 'anomaly';
    const statusColor = isMalicious ? 'var(--danger)' : 'var(--success)';

    resSection.innerHTML = `
        <div style="margin-bottom: 2rem;">
            <h3 style="font-size: 1.5rem; font-weight: 800; margin-bottom: 1rem;">ANALYSIS RESULT</h3>
            <div style="display: grid; grid-template-columns: 1fr; gap: 0.5rem; color: white; font-size: 1rem;">
                <div><strong>Prediction:</strong> <span style="color: ${statusColor}">${data.prediction}</span></div>
                <div><strong>Attack Type:</strong> ${data.attack_type || 'N/A'}</div>
                <div><strong>Threat Score:</strong> <span style="color: ${statusColor}">${data.threat_score}</span></div>
            </div>
        </div>
    `;
}

function showFullDetails(data) {
    const modal = document.getElementById('detailsModal');
    const content = document.getElementById('modalContent');
    if (!modal || !content) return;

    const isMalicious = data.prediction === 'malicious' || data.prediction === 'anomaly';
    const statusColor = isMalicious ? '#FF4D4D' : '#00E676';

    content.innerHTML = `
        <div style="margin-bottom: 2.5rem;">
            <h2 style="font-size: 1.8rem; font-weight: 800; margin-bottom: 1.5rem; color: white;">ANALYSIS RESULT</h2>
            <div style="display: grid; grid-template-columns: 1fr; gap: 0.6rem; color: white; font-size: 1.1rem; border-left: 4px solid ${statusColor}; padding-left: 1.5rem;">
                <div><strong>Prediction:</strong> <span style="color: ${statusColor}">${data.prediction}</span></div>
                <div><strong>Attack Type:</strong> ${data.attack_type || 'System Pattern'}</div>
                <div><strong>Attack Name:</strong> ${data.attack_name || data.attack_type || 'System Event'}</div>
                <div><strong>Threat Score:</strong> <span style="color: ${statusColor}">${data.threat_score}</span></div>
                <div><strong>Threat Level:</strong> <span style="color: ${statusColor}">${data.threat_score > 70 ? 'high' : 'low'}</span></div>
                <div><strong>Source Type:</strong> ${data.source_type || 'system'}</div>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 2.5rem;">
            <div style="background: rgba(255,255,255,0.03); padding: 1.5rem; border-radius: 12px; border: 1px solid rgba(255,255,255,0.05);">
                <div style="color: var(--text-secondary); font-size: 0.75rem; font-weight: 700; text-transform: uppercase; margin-bottom: 8px;">ATTACKER IP</div>
                <div style="font-weight: 700; color: white; font-size: 1.1rem;">${data.attacker_ip || '192.168.1.1'}</div>
            </div>
            <div style="background: rgba(255,255,255,0.03); padding: 1.5rem; border-radius: 12px; border: 1px solid rgba(255,255,255,0.05);">
                <div style="color: var(--text-secondary); font-size: 0.75rem; font-weight: 700; text-transform: uppercase; margin-bottom: 8px;">USERNAME</div>
                <div style="font-weight: 700; color: white; font-size: 1.1rem;">${data.username || 'Administrator'}</div>
            </div>
            <div style="background: rgba(255,255,255,0.03); padding: 1.5rem; border-radius: 12px; border: 1px solid rgba(255,255,255,0.05);">
                <div style="color: var(--text-secondary); font-size: 0.75rem; font-weight: 700; text-transform: uppercase; margin-bottom: 8px;">HOST</div>
                <div style="font-weight: 700; color: white; font-size: 1.1rem;">${data.host || 'N/A'}</div>
            </div>
            <div style="background: rgba(255,255,255,0.03); padding: 1.5rem; border-radius: 12px; border: 1px solid rgba(255,255,255,0.05);">
                <div style="color: var(--text-secondary); font-size: 0.75rem; font-weight: 700; text-transform: uppercase; margin-bottom: 8px;">EVENT TIME</div>
                <div style="font-weight: 700; color: white; font-size: 0.95rem;">${new Date().toISOString()}</div>
            </div>
        </div>

        <div style="margin-bottom: 2.5rem; background: rgba(0, 209, 255, 0.05); padding: 2rem; border-radius: 12px; border: 1px solid var(--border);">
            <h4 style="font-family: 'Outfit'; margin-bottom: 1rem; color: var(--accent);">Recommended Actions</h4>
            <ul style="color: var(--text-secondary); font-size: 1rem; padding-left: 20px; line-height: 1.8;">
                <li>Review the affected host and recent security events around the same time.</li>
                <li>Verify whether the action was authorized by the owner.</li>
                <li>Escalate to the incident response team if the pattern repeats.</li>
            </ul>
        </div>

        <div>
            <h4 style="color: var(--text-secondary); font-size: 0.8rem; margin-bottom: 1rem; text-transform: uppercase; letter-spacing: 1px;">Raw Context Data</h4>
            <pre style="font-family: monospace; color: #64748b; font-size: 0.85rem; overflow-x: auto; background: #060B18; padding: 1.5rem; border-radius: 8px;">${JSON.stringify(data, null, 4)}</pre>
        </div>
    `;

    modal.style.display = 'flex';
}

function closeModal() {
    const modal = document.getElementById('detailsModal');
    if (modal) modal.style.display = 'none';
}

async function loadHistory() {
    const table = document.getElementById('historyTableBody');
    if (!table) return;
    const res = await fetch(`../api/history.php?user_id=${userSession.id}`);
    const data = await res.json();
    table.innerHTML = data.data.map(r => `
        <tr>
            <td style="font-weight: 700; font-family: 'Outfit';">${r.scan_type.toUpperCase()}</td>
            <td style="color: var(--text-secondary); font-size: 0.9rem; max-width: 300px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">${r.scan_target}</td>
            <td>
                <span class="status-dot ${r.status==='Malicious'?'status-vulnerable':'status-secure'}"></span>
                <span style="font-weight: 600; color: ${r.status==='Malicious'?'var(--danger)':'var(--success)'}">${r.status}</span>
            </td>
            <td style="color: var(--text-secondary); font-size: 0.85rem;">${new Date(r.created_at).toLocaleString()}</td>
        </tr>
    `).join('') || '<tr><td colspan="4" style="text-align:center;">No scan history found.</td></tr>';
}

async function loadReports(period) {
    const table = document.getElementById('trendTableBody');
    if (!table) return;
    const res = await fetch(`../api/reports.php?period=${period}&user_id=${userSession.id}`);
    const data = await res.json();
    table.innerHTML = data.stats.map(r => `
        <tr>
            <td style="font-weight: 700;">${r.period_label}</td>
            <td>${r.total_scans}</td>
            <td style="color: var(--danger); font-weight: 700;">${r.total_threats}</td>
            <td style="color: var(--success); font-weight: 700;">${r.total_safe}</td>
        </tr>
    `).join('');
}
