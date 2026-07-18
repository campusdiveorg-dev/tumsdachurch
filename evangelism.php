<?php
$currentPage = basename($_SERVER['PHP_SELF']);
$missions = [];
try {
    require_once 'db_connect.php';
    $pdo = getPublicDB();
    $stmt = $pdo->query("SELECT * FROM missions ORDER BY sort_order ASC");
    $missions = $stmt->fetchAll();
} catch (Exception $e) {
    $missions = [];
}
include 'header.php';
?>

<!-- Hero Section -->
<section class="evangelism-hero section py-0">
	<div class="evangelism-hero-container">
		<div class="evangelism-hero-overlay">
			<div class="evangelism-hero-content">
				<h1 class="evangelism-hero-title">Evangelism &amp; Missions</h1>
				<p class="evangelism-hero-subtitle">Spreading the Gospel through dedicated missionary work and community outreach</p>
				<blockquote class="evangelism-verse">
					<p>"And this gospel of the kingdom will be preached in all the world as a witness to all the nations, and then the end will come."</p>
					<footer class="verse-reference">— Matthew 24:14</footer>
				</blockquote>
			</div>
		</div>
	</div>
</section>

<!-- Mission Overview Section -->
<section class="section">
	<div class="container">
		<div class="row justify-content-center">
			<div class="col-lg-10 text-center">
				<h2 class="fw-bold mb-3 title-with-underline">Our Missionary Journey</h2>
				<p class="mb-4">At TUMSDA, we believe in the power of evangelism to transform lives and communities. Our missionary work extends beyond our campus walls, reaching out to neighboring communities through service, teaching, and compassionate outreach.</p>
				<p class="mb-0">Through our annual missions, we engage in community service, Bible studies, health education, and spiritual outreach, making a lasting impact in the lives of those we serve.</p>
			</div>
		</div>
	</div>
</section>

<!-- Mission Activities Section -->
<section class="section bg-light">
	<div class="container">
		<div class="row g-4 mb-5">
			<div class="col-lg-12">
				<h3 class="activities-main-title text-center mb-4">Mission Activities</h3>
				<div class="activities-detailed-grid">
					<div class="activity-detailed-item">
						<div class="activity-content">
							<h4>Community Outreach</h4>
							<p>Door-to-door witnessing, Bible studies, and public evangelistic meetings.</p>
						</div>
					</div>
					<div class="activity-detailed-item">
						<div class="activity-content">
							<h4>Medical Missionary</h4>
							<p>Offering free medical check-ups, treatments, and health education to serve the physical needs of the community.</p>
						</div>
					</div>
					<div class="activity-detailed-item">
						<div class="activity-content">
							<h4>Health Ministry</h4>
							<p>Practical training in lifestyle health, wellness, and preventive care for stronger, healthier families.</p>
						</div>
					</div>
					<div class="activity-detailed-item">
						<div class="activity-content">
							<h4>Children's Ministry</h4>
							<p>Engaging programs designed to nurture young hearts in the love of Jesus through songs, Bible stories, and activities.</p>
						</div>
					</div>
					<div class="activity-detailed-item">
						<div class="activity-content">
							<h4>Family Life Ministry</h4>
							<p>Strengthening homes through Christ-centered seminars and counseling.</p>
						</div>
					</div>
					<div class="activity-detailed-item">
						<div class="activity-content">
							<h4>Community Transformation</h4>
							<p>Practical acts of service such as clean-up exercises, helping vulnerable families, and creating sustainable impact.</p>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</section>

<!-- Mission History Accordion Section -->
<section class="section mission-history-section">
	<div class="container">
		<div class="row justify-content-center">
			<div class="col-lg-10">
				<h2 class="mission-history-title">Our Mission History</h2>
				<p class="mission-history-subtitle">Discover the impact of our evangelistic missions through the years</p>

				<div class="mission-accordion" id="missionAccordion">
					<?php foreach ($missions as $index => $mission): ?>
					<div class="mission-accordion-item <?php echo $index === 0 ? 'active' : ''; ?>">

						<!-- Custom Accordion Header / Toggle Button -->
						<button
							class="mission-accordion-header"
							type="button"
							data-toggle-index="<?php echo $index; ?>"
							aria-expanded="<?php echo $index === 0 ? 'true' : 'false'; ?>"
						>
							<span class="mission-accordion-left">
								<?php if ($mission['is_upcoming']): ?>
									<span class="mission-badge upcoming">UPCOMING</span>
								<?php else: ?>
									<span class="mission-badge past">PAST</span>
								<?php endif; ?>
								<span class="mission-accordion-title"><?php echo htmlspecialchars($mission['title']); ?></span>
							</span>
							<?php if ($mission['start_date'] && $mission['end_date']): ?>
							<span class="mission-accordion-date">
								<i class="fas fa-calendar-alt me-1"></i>
								<?php echo date('M Y', strtotime($mission['start_date'])); ?> – <?php echo date('M Y', strtotime($mission['end_date'])); ?>
							</span>
							<?php endif; ?>
							<span class="mission-accordion-chevron"><i class="fas fa-chevron-down"></i></span>
						</button>

						<!-- Custom Accordion Content Wrapper -->
						<div 
							class="mission-accordion-collapse" 
							id="mission-collapse-<?php echo $index; ?>"
							style="display: <?php echo $index === 0 ? 'block' : 'none'; ?>;"
						>
							<div class="mission-accordion-body">

								<!-- Theme Banner -->
								<?php if ($mission['theme_text'] || $mission['theme_verse'] || $mission['theme_song']): ?>
								<div class="mission-theme-banner" style="position: relative; overflow: hidden;">
									<?php if ($mission['logo_path']): ?>
										<img src="<?php echo htmlspecialchars(asset_url($mission['logo_path'])); ?>" alt="Mission Logo" class="d-none d-md-block position-absolute end-0 top-0 m-4 shadow-sm rounded-3 bg-white p-2" style="max-height: 60px; max-width: 120px; object-fit: contain; z-index: 10; border: 1px solid rgba(0,0,0,0.05);" onError="this.style.display='none'">
									<?php endif; ?>
									<div class="mission-theme-inner">
										<div class="d-flex align-items-center gap-3 mb-2 flex-wrap">
											<?php if ($mission['logo_path']): ?>
												<img src="<?php echo htmlspecialchars(asset_url($mission['logo_path'])); ?>" alt="Mission Logo" class="d-block d-md-none shadow-sm rounded-3 bg-white p-1" style="max-height: 40px; max-width: 80px; object-fit: contain; border: 1px solid rgba(0,0,0,0.05);" onError="this.style.display='none'">
											<?php endif; ?>
											<h3 class="mission-theme-title m-0"><?php echo htmlspecialchars($mission['title']); ?></h3>
										</div>
										<?php if ($mission['theme_text']): ?>
										<p class="mission-theme-text"><?php echo htmlspecialchars($mission['theme_text']); ?></p>
										<?php endif; ?>
										<div class="mission-theme-meta">
											<?php if ($mission['theme_verse']): ?>
											<span class="mission-meta-tag"><i class="fas fa-book-open me-1"></i><?php echo htmlspecialchars($mission['theme_verse']); ?></span>
											<?php endif; ?>
											<?php if ($mission['theme_song']): ?>
											<span class="mission-meta-tag"><i class="fas fa-music me-1"></i><?php echo htmlspecialchars($mission['theme_song']); ?></span>
											<?php endif; ?>
											<?php if ($mission['start_date'] && $mission['end_date']): ?>
											<span class="mission-meta-tag"><i class="fas fa-calendar-alt me-1"></i><?php echo date('F j', strtotime($mission['start_date'])); ?> – <?php echo date('F j, Y', strtotime($mission['end_date'])); ?></span>
											<?php endif; ?>
										</div>
									</div>
								</div>
								<?php endif; ?>

								<!-- Description -->
								<?php if ($mission['description']): ?>
								<div class="mission-description-section">
									<div class="mission-description-card">
										<div class="mission-desc-icon"><i class="fas fa-globe-africa"></i></div>
										<p><?php echo htmlspecialchars($mission['description']); ?></p>
									</div>
								</div>
								<?php endif; ?>

								<!-- Call to Action (upcoming missions only) -->
								<?php if ($mission['is_upcoming']): ?>
								<div class="mission-cta-section">
									<div class="mission-cta-card">
										<div class="mission-cta-icon"><i class="fas fa-hands-helping"></i></div>
										<h4>Join Us in This Mission</h4>
										<p>We warmly invite every member and friend of TUMSDA to take part in this mission — whether by going with us physically, giving in support (financial or material), or standing with us in prayer.</p>
										<div class="mission-cta-buttons">
											<a href="https://whatsapp.com/channel/0029Vb5zZEjBKfi4xoxGlI25" target="_blank" class="btn-mission primary">
												<i class="fab fa-whatsapp me-2"></i>Join Us
											</a>
											<button class="btn-mission outline support-btn">
												<i class="fas fa-hand-holding-heart me-2"></i>Support
											</button>
											<button class="btn-mission outline mission-chair-btn">
												<i class="fas fa-user-tie me-2"></i>Mission Chair
											</button>
										</div>
									</div>
								</div>
								<?php endif; ?>

							</div>
						</div>
					</div>
					<?php endforeach; ?>
				</div>

			</div>
		</div>
	</div>
</section>

<!-- Custom Vanilla Accordion Logic -->
<script>
document.addEventListener('DOMContentLoaded', function() {
	const headers = document.querySelectorAll('.mission-accordion-header');
	
	headers.forEach(header => {
		header.addEventListener('click', function() {
			const index = this.getAttribute('data-toggle-index');
			const content = document.getElementById('mission-collapse-' + index);
			const parentItem = this.closest('.mission-accordion-item');
			const isExpanded = this.getAttribute('aria-expanded') === 'true';
			
			// If already expanded, close it
			if (isExpanded) {
				this.setAttribute('aria-expanded', 'false');
				content.style.display = 'none';
				parentItem.classList.remove('active');
			} else {
				// Close all other accordion items
				document.querySelectorAll('.mission-accordion-header').forEach(h => {
					h.setAttribute('aria-expanded', 'false');
					const idx = h.getAttribute('data-toggle-index');
					const c = document.getElementById('mission-collapse-' + idx);
					if (c) c.style.display = 'none';
					
					const pi = h.closest('.mission-accordion-item');
					if (pi) pi.classList.remove('active');
				});
				
				// Open current one
				this.setAttribute('aria-expanded', 'true');
				content.style.display = 'block';
				parentItem.classList.add('active');
			}
		});
	});
});
</script>

<?php include 'footer.php'; ?>
