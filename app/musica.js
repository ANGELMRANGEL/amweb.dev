// Music App Logic
window.appMusica = {
    audio: null,
    isPlayingMusic: false,
    trackPosition: 0,
    
    open: function() {
        window.openDockModal('music-modal');
    },
    
    toggle: function() {
        const playBtn = document.getElementById('music-play-btn');
        const cover = document.getElementById('music-cover-art');
        if (!playBtn) return;
        
        if (!this.audio) {
            this.audio = new Audio('assets/uptown_funk.mp3');
            this.audio.addEventListener('timeupdate', () => {
                const progress = document.getElementById('music-progress-bar');
                if (progress && this.audio.duration) {
                    this.trackPosition = (this.audio.currentTime / this.audio.duration) * 100;
                    progress.style.width = this.trackPosition + '%';
                }
            });
            this.audio.addEventListener('ended', () => {
                this.isPlayingMusic = false;
                playBtn.innerHTML = '<svg width="16" height="16" fill="currentColor" viewBox="0 0 24 24" style="margin-left: 2px;"><path d="M8 5v14l11-7z"/></svg>';
                if (cover) cover.classList.remove('playing');
                const progress = document.getElementById('music-progress-bar');
                if (progress) progress.style.width = '0%';
            });
        }
        
        if (this.isPlayingMusic) {
            this.audio.pause();
            this.isPlayingMusic = false;
            playBtn.innerHTML = '<svg width="16" height="16" fill="currentColor" viewBox="0 0 24 24" style="margin-left: 2px;"><path d="M8 5v14l11-7z"/></svg>';
            if (cover) cover.classList.remove('playing');
        } else {
            this.audio.play().catch(err => console.error("Error playing audio:", err));
            this.isPlayingMusic = true;
            playBtn.innerHTML = '<svg width="16" height="16" fill="currentColor" viewBox="0 0 24 24"><path d="M6 19h4V5H6v14zm8-14v14h4V5h-4z"/></svg>';
            if (cover) cover.classList.add('playing');
        }
    },
    
    seek: function(event) {
        if (!this.audio || !this.audio.duration) return;
        const container = event.currentTarget;
        const rect = container.getBoundingClientRect();
        const clickX = event.clientX - rect.left;
        const width = rect.width;
        const percentage = clickX / width;
        this.audio.currentTime = percentage * this.audio.duration;
    },
    
    prev: function() {
        if (this.audio) {
            this.audio.currentTime = 0;
        }
    },
    
    next: function() {
        if (this.audio) {
            this.audio.currentTime = 0;
        }
    }
};
