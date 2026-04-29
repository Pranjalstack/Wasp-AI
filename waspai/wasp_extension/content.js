document.addEventListener('click', async function(e) {
    const link = e.target.closest('a');
    if (link && link.href.startsWith('http')) {
        const targetUrl = link.href;
        if (targetUrl.includes(window.location.hostname)) return;

        e.preventDefault(); 
        document.body.style.cursor = 'wait';

        try {
            const response = await fetch('http://localhost/waspai/extension_uplink.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ 'url': targetUrl })
            });

            const data = await response.json();
            document.body.style.cursor = 'default';

            if (data.status === 'MALICIOUS') {
                showWaspAlert(targetUrl);
            } else {
                window.location.href = targetUrl;
            }
        } catch (err) {
            window.location.href = targetUrl; // Fail-safe
        }
    }
}, true);

function showWaspAlert(url) {
    const div = document.createElement('div');
    div.innerHTML = `
        <div style="position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.9);z-index:999999;display:flex;align-items:center;justify-content:center;font-family:sans-serif;color:#fbff00;border:10px solid #ff2a2a;">
            <div style="text-align:center;background:#000;padding:50px;border:1px solid #ff2a2a;">
                <h1 style="color:#ff2a2a;font-size:3rem;margin:0;">THREAT DETECTED</h1>
                <p style="color:#fff;">WASP AI has flagged this link as MALICIOUS.</p>
                <div style="background:#111;padding:10px;margin:20px 0;color:#888;">${url}</div>
                <button id="closeWasp" style="background:#fbff00;border:none;padding:15px 30px;font-weight:bold;cursor:pointer;">GET ME OUT OF HERE</button>
            </div>
        </div>`;
    document.body.appendChild(div);
    document.getElementById('closeWasp').onclick = () => div.remove();
}