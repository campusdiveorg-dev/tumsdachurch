<?php
$currentPage = basename($_SERVER['PHP_SELF']);
$upcomingMission = null;
$wordOfTheDay = null;
$announcements = [];
try {
    require_once 'db_connect.php';
    $pdo = getPublicDB();
    // Get upcoming mission
    $stmt = $pdo->query("SELECT * FROM missions WHERE is_upcoming = 1 ORDER BY sort_order ASC LIMIT 1");
    $upcomingMission = $stmt->fetch();

    // Get Word of the Day
    $stmt = $pdo->query("SELECT * FROM word_of_the_day ORDER BY id DESC LIMIT 1");
    $wordOfTheDay = $stmt->fetch();

    // Get Announcements
    $stmt = $pdo->query("SELECT * FROM announcements ORDER BY sort_order ASC, id DESC");
    $announcements = $stmt->fetchAll();
} catch (Exception $e) {
    // Fallback if DB tables aren't initialized yet
    $upcomingMission = null;
    $wordOfTheDay = null;
    $announcements = [];
}
include 'header.php';
?>

<section class="hero section py-0">
	<div id="heroCarousel" class="carousel slide" data-bs-ride="carousel" data-bs-interval="6000">
		<div class="carousel-indicators">
			<button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="0" class="active" aria-current="true" aria-label="Slide 1"></button>
			<button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="1" aria-label="Slide 2"></button>
			<button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="2" aria-label="Slide 3"></button>
			<button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="3" aria-label="Slide 4"></button>
		</div>
		<div class="carousel-inner">
			<div class="carousel-item active">
				<img src="assets/img/Sabbath.png" class="d-block w-100" alt="Worship">
				<div class="carousel-caption hero-caption hero-caption--top text-center">
					<h1 class="hero-title">WELCOME TO TUMSDA</h1>
					<p class="hero-subtitle holiday-tagline">The Church We Love The Most!</p>
					<button class="hero-btn hero-btn-welcome">Welcome and Worship With Us</button>
				</div>
			</div>
				<div class="carousel-item">
					<img src="assets/img/ChurchChoir.png" class="d-block w-100" alt="Church Choir">
					<div class="carousel-caption hero-caption hero-caption--bottom-left text-start">
						<h2 class="hero-title">Listen to the Heavenly Music</h2>
						<p class="hero-subtitle">Experience sacred music with the TUMSDA Church Choir</p>
						<a href="https://www.youtube.com/@tumsdachurchchoir" target="_blank" class="hero-btn">Listen Now</a>
					</div>
				</div>
				<div class="carousel-item">
					<img src="assets/img/ALO.png" class="d-block w-100" alt="Adventist Ladies Organisation">
					<div class="carousel-caption hero-caption hero-caption--bottom-left text-start">
						<h2 class="hero-title">Strong to Serve</h2>
						<p class="hero-subtitle">Fellowship and discipleship across all departments</p>
						<a href="departments.php" class="hero-btn">Explore Departments</a>
					</div>
				</div>
				<div class="carousel-item">
					<img src="assets/img/jpg/church.jpeg" class="d-block w-100" alt="Bible and Bible Alone">
					<div class="carousel-caption hero-caption hero-caption--bottom-left text-start">
						<h2 class="hero-title">Rooted in the Word</h2>
						<p class="hero-subtitle">Bible study and present truth for daily living.</p>
						<a href="ministries.php" class="hero-btn">Join a Ministry</a>
					</div>
				</div>
		</div>
		<button class="carousel-control-prev" type="button" data-bs-target="#heroCarousel" data-bs-slide="prev">
			<span class="carousel-control-prev-icon" aria-hidden="true"></span>
			<span class="visually-hidden">Previous</span>
		</button>
		<button class="carousel-control-next" type="button" data-bs-target="#heroCarousel" data-bs-slide="next">
			<span class="carousel-control-next-icon" aria-hidden="true"></span>
			<span class="visually-hidden">Next</span>
		</button>
	</div>
</section>

<section class="section">
	<div class="container">
		<div class="row justify-content-center">
			<div class="col-lg-8 text-center">
				<h2 class="fw-bold mb-3">About TUMSDA</h2>
				<p class="mb-3">TUMSDA Church is a Seventh-day Adventist Sabbath school in Ziwani District located at the Technical University of Mombasa (TUM) in Tudor. We nurture a deep love for the Bible [...]</p>
				<a href="about.php" class="btn btn-sm btn-outline-primary">Learn More</a>
			</div>
		</div>
		<div class="row g-3 mt-4">
			<div class="col-md-6">
				<div class="card elevated-card h-100 border-0">
					<div class="card-body text-center">
						<h5 class="card-title fw-bold">Our Mission</h5>
						<p class="mb-0">To make disciples of all people, communicating the everlasting gospel in the context of the three angels' messages of Revelation 14:6-12, leading them to accept Jesus as per[...]</p>
					</div>
				</div>
			</div>
			<div class="col-md-6">
				<div class="card elevated-card h-100 border-0">
					<div class="card-body text-center">
						<h5 class="card-title fw-bold">Our Vision</h5>
						<p class="mb-0">To uphold the distinctive message of the Seventh-day Adventist Church; to aspire to excellence in all aspects of their lives - academic, social and spiritual; to embrace the[...]</p>
					</div>
				</div>
			</div>
		</div>
	</div>
</section>

<section class="section bg-white">
	<div class="container">
		<div class="row g-4">
			<div class="col-lg-6">
				<!-- Upcoming Events -->
				<div class="mb-4">
					<h3 class="fw-semibold mb-3">Upcoming Events</h3>
					<div class="bg-light rounded-3 p-3">
						<div class="mt-3">
							<a href="about.php#calendar" class="btn btn-outline-primary btn-sm">View Full Calendar</a>
						</div>
					</div>
				</div>

				<!-- Weekly Meetings -->
				<div class="card border-0 shadow-sm">
					<div class="card-body">
						<h4 class="fw-semibold mb-3">Weekly Meetings</h4>
						<p class="mb-3 text-muted">Find our Weekly Meetings schedules where we meet as a family to engage one another and grow in different aspects, whether social, spiritual or even physically fro[...]</p>
						<a href="about.php#weekly-meetings" class="btn btn-outline-primary btn-sm">View our Weekly Meetings</a>
					</div>
				</div>
			</div>

			<div class="col-lg-6">
				<!-- Word of the Day -->
				<?php if ($wordOfTheDay): ?>
				<div class="mb-4" id="wordOfTheDayBlock">
					<h3 class="fw-semibold mb-3">Word of the Day</h3>
					<div class="p-4 bg-dark text-light rounded-3 position-relative">
						<button type="button" class="btn-close btn-close-white position-absolute top-0 end-0 m-3" aria-label="Close" onclick="document.getElementById('wordOfTheDayBlock').style.display='none'"></button>
						<blockquote class="mb-2 fs-5">"<?php echo htmlspecialchars($wordOfTheDay['content']); ?>"</blockquote>
						<div class="small opacity-75"><?php echo htmlspecialchars($wordOfTheDay['reference']); ?></div>
					</div>
				</div>
				<?php endif; ?>
				
				<!-- Church Notice Board & Announcements -->
				<?php if (!empty($announcements)): ?>
				<div>
					<h3 class="fw-semibold mb-3">Church Notice Board &amp; Announcements</h3>
					<div class="card border-0 shadow-sm">
						<div class="card-body">
							<?php foreach ($announcements as $i => $ann): ?>
							<div class="announcement-item <?php echo $i < count($announcements) - 1 ? 'mb-3 pb-3 border-bottom' : ''; ?>">
								<h6 class="fw-semibold text-primary mb-2"><?php echo htmlspecialchars($ann['title']); ?></h6>
								<p class="mb-0 small text-muted"><?php echo htmlspecialchars($ann['content']); ?></p>
							</div>
							<?php endforeach; ?>
						</div>
					</div>
				</div>
				<?php endif; ?>
			</div>
		</div>
	</div>
</section>

<!-- Upcoming Mission Section -->
<section class="homepage-mission-section">
	<div class="homepage-mission-container">
		<div class="homepage-mission-overlay">
			<div class="homepage-mission-content">
				<h2 class="homepage-mission-title">Upcoming Mission</h2>
				<?php if ($upcomingMission): ?>
					<h1 class="homepage-mission-event"><?php echo htmlspecialchars($upcomingMission['title']); ?></h1>
					<p class="homepage-mission-description">
						<?php echo htmlspecialchars($upcomingMission['description']); ?>
						<?php if ($upcomingMission['start_date'] && $upcomingMission['end_date']): ?>
							<br>From <?php echo date('F j, Y', strtotime($upcomingMission['start_date'])); ?> to <?php echo date('F j, Y', strtotime($upcomingMission['end_date'])); ?>
						<?php endif; ?>
					</p>
				<?php else: ?>
					<h1 class="homepage-mission-event">Stay Tuned for Our Next Mission</h1>
					<p class="homepage-mission-description">Check back soon for details on our upcoming evangelistic mission.</p>
				<?php endif; ?>
				<div class="homepage-mission-buttons">
					<a href="evangelism.php#missionAccordion" class="homepage-mission-btn">Find More</a>
					<button class="homepage-mission-btn support-btn">Support</button>
				</div>
			</div>
		</div>
	</div>
</section>

<?php include 'footer.php'; ?>
