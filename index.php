<?php
require_once 'includes/functions.php';
$settings = getAllSettings();
$currentProgram = getCurrentProgram();
$programs = getPrograms();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($settings['station_name']); ?></title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Video.js -->
    <link href="https://vjs.zencdn.net/8.6.1/video-js.css" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
    
    <style>
        :root {
            --primary-color: <?php echo $settings['primary_color']; ?>;
            --secondary-color: <?php echo $settings['secondary_color']; ?>;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top">
        <div class="container">
            <a class="navbar-brand" href="#">
                <?php if ($settings['logo']): ?>
                    <img src="<?php echo $settings['logo']; ?>" alt="Logo" height="40">
                <?php else: ?>
                    <i class="fas fa-radio"></i> <?php echo htmlspecialchars($settings['station_name']); ?>
                <?php endif; ?>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="#player">Listen Live</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#programs">Programs</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#about">About</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#contact">Contact</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>



<!-- ==========  PLAYER SECTION  (index.php)  ========== -->


                        <!-- ② Facebook-Live VIDEO (visible to all visitors) -->
                        <?php if (!empty($settings['facebook_embed_url'])): ?>
                            <div class="ratio ratio-16x9 mb-4">
                                <iframe src="<?= htmlspecialchars($settings['facebook_embed_url']) ?>"
                                        width="100%" height="315" style="border:none;overflow:hidden"
                                        scrolling="no" frameborder="0" allowfullscreen="true"
                                        allow="autoplay; clipboard-write; encrypted-media; picture-in-picture; web-share">
                                </iframe>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-info mb-4">
                                📺 <strong>Video feed coming soon!</strong>  Admin can paste the Facebook-live <b>Embed URL</b> in
                                <a href="<?= BASE_URL ?>/admin/settings.php" class="alert-link">Settings → General</a>.
                            </div>
                        <?php endif; ?>

                        <!-- Now-playing & controls (unchanged) -->
                        <div class="now-playing bg-dark text-white p-3 rounded">
                            <div class="row align-items-center">
                                <div class="col-md-8">
                                    <h5 class="mb-1">Now Playing</h5>
                                    <h3 class="mb-0" id="nowPlayingTitle">
                                        <?= $currentProgram ? htmlspecialchars($currentProgram['title']) : 'Live Stream' ?>
                                    </h3>
                                    <p class="mb-0 text-muted">
                                        <?= $currentProgram ? htmlspecialchars($currentProgram['host']) : 'Various Artists' ?>
                                    </p>
                                </div>
                                <div class="col-md-4 text-end">
                                    <div class="live-indicator">
                                        <span class="live-dot"></span> LIVE
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Player Controls -->
                        <div class="player-controls mt-3">
                            <div class="row">
                                <div class="col-6">
                                    <button id="playPauseBtn" class="btn btn-primary w-100">
                                        <i class="fas fa-play"></i> Play
                                    </button>
                                </div>
                                <div class="col-6">
                                    <button id="volumeBtn" class="btn btn-secondary w-100">
                                        <i class="fas fa-volume-up"></i> Volume
                                    </button>
                                </div>
                            </div>
                            <div class="volume-control mt-2" id="volumeControl" style="display: none;">
                                <input type="range" class="form-range" id="volumeSlider" min="0" max="100" value="50">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

    <!-- Programs Section -->
    <section id="programs" class="py-5">
        <div class="container">
            <h2 class="text-center mb-5">
                <i class="fas fa-calendar-alt text-primary"></i> Program Schedule
            </h2>
            
            <div class="row">
                <div class="col-lg-10 mx-auto">
                    <div class="schedule-tabs">
                        <ul class="nav nav-pills justify-content-center mb-4" id="scheduleTabs" role="tablist">
                            <?php
                            $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
                            $today = date('l');
                            foreach ($days as $day): ?>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link <?php echo $day === $today ? 'active' : ''; ?>" 
                                            id="<?php echo strtolower($day); ?>-tab" 
                                            data-bs-toggle="pill" 
                                            data-bs-target="#<?php echo strtolower($day); ?>" 
                                            type="button" role="tab">
                                        <?php echo $day; ?>
                                    </button>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                        
                        <div class="tab-content" id="scheduleTabContent">
                            <?php foreach ($days as $day): ?>
                                <div class="tab-pane fade <?php echo $day === $today ? 'show active' : ''; ?>" 
                                     id="<?php echo strtolower($day); ?>" role="tabpanel">
                                    <div class="programs-list">
                                        <?php
                                        $dayPrograms = array_filter($programs, function($program) use ($day) {
                                            return $program['day_of_week'] === $day;
                                        });
                                        
                                        if (empty($dayPrograms)): ?>
                                            <div class="alert alert-info">No programs scheduled for this day.</div>
                                        <?php else: ?>
                                            <?php foreach ($dayPrograms as $program): ?>
                                                <div class="program-item card mb-3">
                                                    <div class="card-body">
                                                        <div class="row align-items-center">
                                                            <div class="col-md-2">
                                                                <div class="program-time">
                                                                    <strong><?php echo date('g:i A', strtotime($program['start_time'])); ?></strong>
                                                                    <br>
                                                                    <small class="text-muted"><?php echo date('g:i A', strtotime($program['end_time'])); ?></small>
                                                                </div>
                                                            </div>
                                                            <div class="col-md-8">
                                                                <h5 class="card-title"><?php echo htmlspecialchars($program['title']); ?></h5>
                                                                <p class="card-text"><?php echo htmlspecialchars($program['description']); ?></p>
                                                                <small class="text-muted">
                                                                    <i class="fas fa-user"></i> Hosted by <?php echo htmlspecialchars($program['host']); ?>
                                                                </small>
                                                            </div>
                                                            <div class="col-md-2 text-end">
                                                                <?php if ($program['image']): ?>
                                                                    <img src="<?php echo $program['image']; ?>" 
                                                                         alt="<?php echo htmlspecialchars($program['title']); ?>" 
                                                                         class="img-fluid rounded" 
                                                                         style="max-height: 80px;">
                                                                <?php endif; ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section id="about" class="py-5 bg-light">
        <div class="container">
            <div class="row">
                <div class="col-lg-8 mx-auto text-center">
                    <h2 class="mb-4">About <?php echo htmlspecialchars($settings['station_name']); ?></h2>
                    <p class="lead">
                        <?php echo htmlspecialchars($settings['station_description']); ?>
                    </p>
                    <div class="row mt-5">
                        <div class="col-md-4">
                            <div class="feature-box">
                                <i class="fas fa-music fa-3x text-primary mb-3"></i>
                                <h4>Great Music</h4>
                                <p>Curated playlists and live shows featuring the best music.</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="feature-box">
                                <i class="fas fa-microphone fa-3x text-primary mb-3"></i>
                                <h4>Live Shows</h4>
                                <p>Engaging talk shows and interactive programs daily.</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="feature-box">
                                <i class="fas fa-users fa-3x text-primary mb-3"></i>
                                <h4>Community</h4>
                                <p>Connecting with our local community and beyond.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section id="contact" class="py-5">
        <div class="container">
            <div class="row">
                <div class="col-lg-8 mx-auto">
                    <h2 class="text-center mb-5">Contact Us</h2>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="contact-info">
                                <h4>Get in Touch</h4>
                                <p><i class="fas fa-map-marker-alt text-primary"></i> <?php echo htmlspecialchars($settings['location']); ?></p>
                                <p><i class="fas fa-phone text-primary"></i> <?php echo htmlspecialchars($settings['contact_phone']); ?></p>
                                <p><i class="fas fa-envelope text-primary"></i> <?php echo htmlspecialchars($settings['contact_email']); ?></p>
                            </div>
                            <div class="social-links mt-4">
                                <?php if ($settings['facebook_url']): ?>
                                    <a href="<?php echo $settings['facebook_url']; ?>" class="btn btn-outline-primary btn-sm me-2">
                                        <i class="fab fa-facebook-f"></i>
                                    </a>
                                <?php endif; ?>
                                <?php if ($settings['twitter_url']): ?>
                                    <a href="<?php echo $settings['twitter_url']; ?>" class="btn btn-outline-primary btn-sm me-2">
                                        <i class="fab fa-twitter"></i>
                                    </a>
                                <?php endif; ?>
                                <?php if ($settings['instagram_url']): ?>
                                    <a href="<?php echo $settings['instagram_url']; ?>" class="btn btn-outline-primary btn-sm">
                                        <i class="fab fa-instagram"></i>
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <form id="contactForm">
                                <div class="mb-3">
                                    <input type="text" class="form-control" placeholder="Your Name" required>
                                </div>
                                <div class="mb-3">
                                    <input type="email" class="form-control" placeholder="Your Email" required>
                                </div>
                                <div class="mb-3">
                                    <textarea class="form-control" rows="4" placeholder="Your Message" required></textarea>
                                </div>
                                <button type="submit" class="btn btn-primary w-100">Send Message</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-dark text-white py-4">
        <div class="container text-center">
            <p>&copy; <?php echo date('Y'); ?> <?php echo htmlspecialchars($settings['station_name']); ?>. All rights reserved.</p>
            <p class="mb-0">
                <a href="<?php echo ADMIN_URL; ?>/login.php" class="text-muted">Admin Login</a>
            </p>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Video.js -->
    <script src="https://vjs.zencdn.net/8.6.1/video.min.js"></script>
    
    <!-- Custom JS -->
    <script src="assets/js/main.js"></script>
</body>
</html>