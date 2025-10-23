// Radio Player functionality
class RadioPlayer {
    constructor() {
        this.player = null;
        this.isPlaying = false;
        this.volume = 0.5;
        this.initializePlayer();
        this.bindEvents();
        this.setupServiceWorker();
    }

    initializePlayer() {
        // Initialize Video.js player
        this.player = videojs('radioPlayer', {
            controls: true,
            autoplay: false,
            preload: 'auto',
            fluid: true,
            controlBar: {
                playToggle: true,
                volumePanel: false,
                currentTimeDisplay: false,
                timeDivider: false,
                durationDisplay: false,
                progressControl: false,
                remainingTimeDisplay: false,
                fullscreenToggle: false
            }
        });

        // Set initial volume
        this.player.volume(this.volume);

        // Player event listeners
        this.player.on('play', () => {
            this.isPlaying = true;
            this.updatePlayButton();
            this.showNotification('Radio is now playing');
        });

        this.player.on('pause', () => {
            this.isPlaying = false;
            this.updatePlayButton();
        });

        this.player.on('error', () => {
            this.showNotification('Error loading stream. Please try again.', 'error');
        });

        this.player.on('waiting', () => {
            this.showNotification('Buffering...', 'info');
        });
    }

    bindEvents() {
        // Play/Pause button
        document.getElementById('playPauseBtn')?.addEventListener('click', () => {
            this.togglePlayPause();
        });

        // Volume button
        document.getElementById('volumeBtn')?.addEventListener('click', () => {
            this.toggleVolumeControl();
        });

        // Volume slider
        document.getElementById('volumeSlider')?.addEventListener('input', (e) => {
            this.setVolume(e.target.value / 100);
        });

        // Contact form
        document.getElementById('contactForm')?.addEventListener('submit', (e) => {
            e.preventDefault();
            this.handleContactForm(e.target);
        });

        // Smooth scrolling for navigation links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });

        // Update now playing information
        this.updateNowPlaying();
        setInterval(() => this.updateNowPlaying(), 60000); // Update every minute

        // Handle visibility change
        document.addEventListener('visibilitychange', () => {
            if (document.hidden && this.isPlaying) {
                this.showNotification('Radio is playing in background');
            }
        });
    }

    togglePlayPause() {
        if (this.isPlaying) {
            this.player.pause();
        } else {
            this.player.play().catch(error => {
                console.error('Play failed:', error);
                this.showNotification('Failed to start playback. Please try again.', 'error');
            });
        }
    }

    updatePlayButton() {
        const btn = document.getElementById('playPauseBtn');
        if (btn) {
            const icon = btn.querySelector('i');
            if (this.isPlaying) {
                icon.className = 'fas fa-pause';
                btn.innerHTML = '<i class="fas fa-pause"></i> Pause';
            } else {
                icon.className = 'fas fa-play';
                btn.innerHTML = '<i class="fas fa-play"></i> Play';
            }
        }
    }

    toggleVolumeControl() {
        const control = document.getElementById('volumeControl');
        if (control) {
            control.style.display = control.style.display === 'none' ? 'block' : 'none';
        }
    }

    setVolume(value) {
        this.volume = value;
        if (this.player) {
            this.player.volume(value);
        }
        
        // Update volume icon
        const btn = document.getElementById('volumeBtn');
        const icon = btn?.querySelector('i');
        if (icon) {
            if (value === 0) {
                icon.className = 'fas fa-volume-mute';
            } else if (value < 0.5) {
                icon.className = 'fas fa-volume-down';
            } else {
                icon.className = 'fas fa-volume-up';
            }
        }
    }

    async updateNowPlaying() {
        try {
            const response = await fetch('api/get-current-program.php');
            const data = await response.json();
            
            if (data.success) {
                const titleElement = document.getElementById('nowPlayingTitle');
                const hostElement = titleElement?.nextElementSibling;
                
                if (titleElement) {
                    titleElement.textContent = data.program?.title || 'Live Stream';
                }
                if (hostElement) {
                    hostElement.textContent = data.program?.host ? `Hosted by ${data.program.host}` : 'Various Artists';
                }
            }
        } catch (error) {
            console.error('Failed to update now playing:', error);
        }
    }

    handleContactForm(form) {
        const formData = new FormData(form);
        const submitBtn = form.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        
        // Show loading state
        submitBtn.innerHTML = '<span class="loading"></span> Sending...';
        submitBtn.disabled = true;

        // Simulate form submission (replace with actual API call)
        setTimeout(() => {
            this.showNotification('Message sent successfully!', 'success');
            form.reset();
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
        }, 2000);
    }

    showNotification(message, type = 'info') {
        // Create notification element
        const notification = document.createElement('div');
        notification.className = `alert alert-${type} position-fixed top-0 end-0 m-3 notification`;
        notification.style.zIndex = '9999';
        notification.style.transform = 'translateX(100%)';
        notification.style.transition = 'transform 0.3s ease';
        notification.innerHTML = `
            <div class="d-flex align-items-center">
                <i class="fas fa-${this.getNotificationIcon(type)} me-2"></i>
                ${message}
                <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
            </div>
        `;

        document.body.appendChild(notification);

        // Animate in
        setTimeout(() => {
            notification.style.transform = 'translateX(0)';
        }, 100);

        // Auto remove after 5 seconds
        setTimeout(() => {
            notification.style.transform = 'translateX(100%)';
            setTimeout(() => {
                notification.remove();
            }, 300);
        }, 5000);
    }

    getNotificationIcon(type) {
        const icons = {
            success: 'check-circle',
            error: 'exclamation-triangle',
            warning: 'exclamation-circle',
            info: 'info-circle'
        };
        return icons[type] || 'info-circle';
    }

    setupServiceWorker() {
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.register('/sw.js')
                .then(registration => {
                    console.log('ServiceWorker registered:', registration);
                })
                .catch(error => {
                    console.log('ServiceWorker registration failed:', error);
                });
        }
    }
}

// Initialize the radio player when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    new RadioPlayer();
});

// Additional utility functions
function formatTime(date) {
    return new Intl.DateTimeFormat('en-US', {
        hour: 'numeric',
        minute: '2-digit',
        hour12: true
    }).format(date);
}

function formatDate(date) {
    return new Intl.DateTimeFormat('en-US', {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    }).format(date);
}

// PWA Support
if ('serviceWorker' in navigator) {
    const swContent = `
self.addEventListener('install', event => {
    event.waitUntil(
        caches.open('fm-radio-v1').then(cache => {
            return cache.addAll([
                '/',
                '/assets/css/style.css',
                '/assets/js/main.js',
                'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css',
                'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css',
                'https://vjs.zencdn.net/8.6.1/video-js.css',
                'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js',
                'https://vjs.zencdn.net/8.6.1/video.min.js'
            ]);
        })
    );
});

self.addEventListener('fetch', event => {
    event.respondWith(
        caches.match(event.request).then(response => {
            return response || fetch(event.request);
        })
    );
});
    `;
    
    const blob = new Blob([swContent], { type: 'application/javascript' });
    const swUrl = URL.createObjectURL(blob);
    
    navigator.serviceWorker.register(swUrl);
}

// Web Audio API Visualizer (optional enhancement)
class AudioVisualizer {
    constructor(audioElement) {
        this.audio = audioElement;
        this.canvas = null;
        this.ctx = null;
        this.audioContext = null;
        this.analyser = null;
        this.source = null;
        this.dataArray = null;
        this.isInitialized = false;
    }

    init() {
        if (this.isInitialized) return;
        
        try {
            this.audioContext = new (window.AudioContext || window.webkitAudioContext)();
            this.analyser = this.audioContext.createAnalyser();
            this.source = this.audioContext.createMediaElementSource(this.audio);
            
            this.source.connect(this.analyser);
            this.analyser.connect(this.audioContext.destination);
            
            this.analyser.fftSize = 256;
            const bufferLength = this.analyser.frequencyBinCount;
            this.dataArray = new Uint8Array(bufferLength);
            
            this.isInitialized = true;
        } catch (error) {
            console.error('Failed to initialize audio visualizer:', error);
        }
    }

    createCanvas(container) {
        this.canvas = document.createElement('canvas');
        this.canvas.width = container.offsetWidth;
        this.canvas.height = 100;
        this.canvas.style.width = '100%';
        this.canvas.style.height = '100px';
        this.ctx = this.canvas.getContext('2d');
        
        container.appendChild(this.canvas);
        return this.canvas;
    }

    draw() {
        if (!this.isInitialized || !this.canvas) return;
        
        requestAnimationFrame(() => this.draw());
        
        this.analyser.getByteFrequencyData(this.dataArray);
        
        this.ctx.fillStyle = 'rgb(0, 0, 0)';
        this.ctx.fillRect(0, 0, this.canvas.width, this.canvas.height);
        
        const barWidth = (this.canvas.width / this.dataArray.length) * 2.5;
        let barHeight;
        let x = 0;
        
        for(let i = 0; i < this.dataArray.length; i++) {
            barHeight = this.dataArray[i] / 2;
            
            const r = barHeight + (25 * (i / this.dataArray.length));
            const g = 250 * (i / this.dataArray.length);
            const b = 50;
            
            this.ctx.fillStyle = `rgb(${r},${g},${b})`;
            this.ctx.fillRect(x, this.canvas.height - barHeight, barWidth, barHeight);
            
            x += barWidth + 1;
        }
    }
}

// Export for use in other files
window.RadioPlayer = RadioPlayer;
window.AudioVisualizer = AudioVisualizer;