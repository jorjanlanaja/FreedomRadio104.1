/* ==========================================
   FM Radio – Front-End Player (index.js)
   ========================================== */
(function () {
    /* ---- basic refs ---- */
    const player   = document.getElementById('radioPlayer');
    const playBtn  = document.getElementById('playPauseBtn');
    const volBtn   = document.getElementById('volumeBtn');
    const volSlide = document.getElementById('volumeSlider');
    const volCtrl  = document.getElementById('volumeControl');
    const titleEl  = document.getElementById('nowPlayingTitle');
    const hostEl   = titleEl?.nextElementSibling;

    if (!player) return;           // safety

    /* ---- play / pause ---- */
    let lastVol = Number(localStorage.getItem('fmVol') || 0.5);
    player.volume = lastVol;
    if (volSlide) volSlide.value  = lastVol * 100;

    function togglePlay() {
        if (player.paused) {
            player.play().catch(() => notify('Play failed – stream offline?','warning'));
            playBtn.innerHTML = '<i class="fas fa-pause"></i> Pause';
        } else {
            player.pause();
            playBtn.innerHTML = '<i class="fas fa-play"></i> Play';
        }
    }
    playBtn?.addEventListener('click', togglePlay);
    player.addEventListener('play',  () => playBtn && (playBtn.innerHTML = '<i class="fas fa-pause"></i> Pause'));
    player.addEventListener('pause', () => playBtn && (playBtn.innerHTML = '<i class="fas fa-play"></i> Play'));

    /* ---- volume ---- */
    function setVolume(v) {
        v = Math.max(0, Math.min(1, v));
        player.volume = v;
        localStorage.setItem('fmVol', v);
        if (!volSlide) return;
        volSlide.value = v * 100;
        updateVolIcon(v);
    }
    function updateVolIcon(v) {
        if (!volBtn) return;
        const ic = volBtn.querySelector('i');
        if (!ic) return;
        ic.className =
            v === 0 ? 'fas fa-volume-mute'
          : v < 0.5 ? 'fas fa-volume-down'
                    : 'fas fa-volume-up';
    }
    volBtn?.addEventListener('click', () => {
        volCtrl.style.display = (volCtrl.style.display === 'none' ? 'block' : 'none');
    });
    volSlide?.addEventListener('input', e => setVolume(e.target.value / 100));

    /* ---- now-playing refresh ---- */
    async function refreshNowPlaying() {
        try {
            const r = await fetch('api/get-current-program.php');
            const j = await r.json();
            if (!j.success) return;
            const p = j.program;
            if (titleEl) titleEl.textContent = p?.title || 'Live Stream';
            if (hostEl)  hostEl.textContent  = p?.host  ? `Hosted by ${p.host}` : 'Various Artists';
        } catch {}
    }
    refreshNowPlaying();
    setInterval(refreshNowPlaying, 30000); // 30 s

    /* ---- tiny visualiser (optional) ---- */
    const canvas = document.getElementById('visual');
    if (canvas && (window.AudioContext || window.webkitAudioContext)) {
        const ctx    = new (window.AudioContext || window.webkitAudioContext)();
        const source = ctx.createMediaElementSource(player);
        const analyser = ctx.createAnalyser();
        source.connect(analyser);
        analyser.connect(ctx.destination);
        analyser.fftSize = 256;
        const data = new Uint8Array(analyser.frequencyBinCount);
        const c    = canvas.getContext('2d');
        const W    = canvas.width;
        const H    = canvas.height;
        function draw() {
            requestAnimationFrame(draw);
            analyser.getByteFrequencyData(data);
            c.clearRect(0, 0, W, H);
            const barW = W / data.length * 2.5;
            let x = 0;
            for (let v of data) {
                const barH = (v / 255) * H;
                c.fillStyle = `rgb(${v+50},100,200)`;
                c.fillRect(x, H - barH, barW, barH);
                x += barW + 1;
            }
        }
        draw();
    }

    /* ---- generic toast ---- */
    function notify(msg, type = 'info') {
        const colours = { info: '#17a2b8', warning: '#ffc107', danger: '#dc3545' };
        const toast = document.createElement('div');
        toast.className = 'position-fixed top-0 end-0 m-3 alert alert-' + (type==='info'?'primary':type);
        toast.style.zIndex = 9999;
        toast.innerHTML = `${msg}<button type="button" class="btn-close ms-2" data-bs-dismiss="alert"></button>`;
        document.body.appendChild(toast);
        setTimeout(() => toast.remove(), 5000);
    }
})();