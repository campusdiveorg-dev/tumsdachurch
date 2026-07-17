
		</main>
		<footer class="site-footer">
			<div class="container">
				<div class="row">
					<div class="col-lg-8">
						<div class="footer-section">
							<p class="footer-official-statement">tumsda.org <!-- to be changed once confirmed -->is the official website of the Seventh-day Adventist Church, Technical University of Mombasa.</p>
						</div>
					</div>
					<div class="col-lg-4">
						<div class="footer-section">
							<div class="footer-legal-links">
								<a href="https://adventist.org/trademark-and-logo-usage" target="_blank" class="legal-item">
									<div class="legal-bars">
										<div class="legal-bar legal-bar-1"></div>
										<div class="legal-bar legal-bar-2"></div>
										<div class="legal-bar legal-bar-3"></div>
									</div>
									<span>TRADEMARK AND LOGO USAGE</span>
								</a>
								<a href="https://adventist.org/legal" target="_blank" class="legal-item">
									<div class="legal-bars">
										<div class="legal-bar legal-bar-1"></div>
										<div class="legal-bar legal-bar-2"></div>
										<div class="legal-bar legal-bar-3"></div>
									</div>
									<span>LEGAL NOTICE</span>
								</a>
								<a href="https://privacy.adventist.org/" target="_blank" class="legal-item">
									<div class="legal-bars">
										<div class="legal-bar legal-bar-1"></div>
										<div class="legal-bar legal-bar-2"></div>
										<div class="legal-bar legal-bar-3"></div>
									</div>
									<span>PRIVACY POLICY</span>
								</a>
							</div>
						</div>
					</div>
				</div>
				<div class="footer-copyright-section">
					<p class="footer-copyright">&copy; <?php echo date("Y"); ?> Technical University of Mombasa SDA Church.</p>
					<p class="footer-address">Tom Mboya Street Tudor, Msa. P.O Box 90420-80100 MSA Kenya</p>
				</div>
			</div>
		</footer>
	</div>
	<div class="seventh-section">
		<div class="seventh-section-content">
			<picture>
				<source type="image/webp" srcset="assets/img/webp/icon.webp">
				<img src="assets/img/icon.png" alt="TUMSDA Logo" class="seventh-section-logo logo-white">
			</picture>
			<picture>
				<source type="image/webp" srcset="assets/img/webp/icon2.webp">
				<img src="assets/img/icon2.png" alt="TUMSDA Logo" class="seventh-section-logo logo-black">
			</picture>
		</div>
	</div>

	<div id="supportPopup" class="popup-overlay">
		<div class="popup-card">
			<button class="popup-close" id="supportClose">&times;</button>
			<div class="popup-content">
				<h3>Support</h3>
				<p>Little is much when God is in it. Support our mission through M-Pesa STK Push:</p>
				
				<form id="mpesaForm" class="mt-3">
					<div class="mb-3">
						<label class="form-label">Full Name</label>
						<input type="text" name="donor_name" class="form-control" required placeholder="e.g. John Kamau" maxlength="150">
					</div>
					<div class="mb-3">
						<label class="form-label">Phone Number (e.g. 254712345678)</label>
						<input type="tel" name="phone" class="form-control" required placeholder="2547xxxxxxxx">
					</div>
					<div class="mb-3">
						<label class="form-label">Amount (KES)</label>
						<input type="number" name="amount" class="form-control" required min="1" placeholder="100">
					</div>
					<div class="mb-3">
						<label class="form-label">Purpose</label>
						<select name="purpose" class="form-select" required>
							<option value="tithe">Tithe</option>
							<option value="offering">Offering</option>
							<option value="mission_support" selected>Mission Support</option>
							<option value="other">Other</option>
						</select>
					</div>
					<div id="mpesaMessage" class="alert d-none"></div>
					<button type="submit" class="btn btn-primary w-100">Send STK Push</button>
				</form>

				
				<div class="mt-4 text-muted small">
					<p>Or use Till Number: <strong>3482464</strong> (Name: RHODA MUTANU)</p>
				</div>
				
				<p class="mt-3"><strong>Thank You! May God Bless You Abundantly!</strong></p>
			</div>
		</div>
	</div>

	<!-- Mission Chair Popup Card -->
	<?php
	$upcomingMission = null;
	try {
	    require_once __DIR__ . '/db_connect.php';
	    $pdo = getPublicDB();
	    $stmt = $pdo->query("SELECT * FROM missions WHERE is_upcoming = 1 LIMIT 1");
	    $upcomingMission = $stmt->fetch();
	} catch (Exception $e) {
	    // Fail silently
	}
	$mTitle = ($upcomingMission && $upcomingMission['title']) ? htmlspecialchars($upcomingMission['title']) : 'our upcoming mission';
	$mLocation = ($upcomingMission && $upcomingMission['title']) ? htmlspecialchars(explode(' ', $upcomingMission['title'])[0] ?? $upcomingMission['title']) : '';
	?>
	<div id="missionChairPopup" class="popup-overlay">
		<div class="popup-card" style="border-radius: 20px; box-shadow: 0 15px 50px rgba(0,0,0,0.15); max-width: 500px;">
			<button class="popup-close" id="missionChairClose">&times;</button>
			<div class="popup-content">
				<div class="d-flex align-items-center justify-content-between mb-3 border-bottom pb-2">
					<h3 class="m-0" style="font-family: 'League Spartan', sans-serif; font-weight: 700; color: var(--brand);">Mission Chair Message</h3>
					<?php if ($upcomingMission && $upcomingMission['logo_path']): ?>
						<img src="<?php echo htmlspecialchars($upcomingMission['logo_path']); ?>" alt="Mission Logo" style="max-height: 40px; max-width: 100px; object-fit: contain;" onError="this.style.display='none'">
					<?php endif; ?>
				</div>
				<div class="mission-chair-message" style="font-family: 'Cambria', serif; line-height: 1.6; color: #475569;">
					<p>As we prepare for <?php echo $mTitle; ?><?php echo $mLocation ? " in " . $mLocation : ""; ?>, my heart is filled with anticipation and prayer. Each mission is more than a program; it is an opportunity to touch lives for eternity.</p>
					<p>I urge you, my brothers and sisters, to partner with us in any way you can. Come with us to the field if you are able. If you cannot, support with your resources. And above all, remember to pray for the mission.</p>
					<p>Let us go to <?php echo $mLocation ? $mLocation : "the field"; ?> with one voice, one heart, and one mission: to proclaim the soon return of Jesus.</p>
				</div>
				<div class="mission-chair-signature mt-4 pt-3 border-top d-flex align-items-center gap-3">
					<?php if ($upcomingMission && $upcomingMission['chair_photo_path']): ?>
						<img src="<?php echo htmlspecialchars($upcomingMission['chair_photo_path']); ?>" alt="Mission Chair" class="rounded-circle border shadow-sm" style="width: 48px; height: 48px; object-fit: cover;" onError="this.src='assets/img/avatar.png'">
					<?php else: ?>
						<div class="rounded-circle bg-light border d-flex align-items-center justify-content-center shadow-sm" style="width: 48px; height: 48px; color: #64748b;"><i class="fas fa-user-tie"></i></div>
					<?php endif; ?>
					<div>
						<p class="m-0"><strong><?php echo htmlspecialchars($upcomingMission['chair_name'] ?? 'Daniel Mochoge'); ?></strong></p>
						<p class="m-0 text-muted small" style="font-size: 0.8rem;">Mission Chair</p>
					</div>
				</div>
			</div>
		</div>
	</div>

	<!-- Sabbath Gallery Popup Card -->
	<div id="galleryPopup" class="popup-overlay">
		<div class="popup-card">
			<button class="popup-close" id="galleryClose">&times;</button>
			<div class="popup-content">
				<h3>Sabbath Gallery Collections</h3>
				<p class="text-muted mb-4">Explore our collection of Sabbath photos from various special events and celebrations throughout the year.</p>
				<div class="gallery-links-grid">
					<a href="https://photos.app.goo.gl/PUo6c4YmVQ3y2vvx6" target="_blank" class="gallery-link">
						<i class="fas fa-crown"></i>
						<span>Finalist Sabbath 2025</span>
					</a>
					<a href="https://photos.app.goo.gl/UjttHTMwkvJ6Z7F18" target="_blank" class="gallery-link">
						<i class="fas fa-users"></i>
						<span>CUCASO 2025</span>
					</a>
					<a href="https://photos.app.goo.gl/DBQUrjHioUXGJf6H8" target="_blank" class="gallery-link">
						<i class="fas fa-female"></i>
						<span>ALO Sabbath 2024</span>
					</a>
					<a href="https://photos.app.goo.gl/sHDiLWxWK4cU5fcb9" target="_blank" class="gallery-link">
						<i class="fas fa-users"></i>
						<span>ALUMNI Sabbath 2024</span>
					</a>
					<a href="https://photos.app.goo.gl/iJLVVn3DaYG5jkP96" target="_blank" class="gallery-link">
						<i class="fas fa-graduation-cap"></i>
						<span>Graduates' Sabbath 2024</span>
					</a>
					<a href="https://photos.app.goo.gl/o1GsUc6vFgjYFKwYA" target="_blank" class="gallery-link">
						<i class="fas fa-gem"></i>
						<span>Jewel's Sabbath 2024</span>
					</a>
				</div>
			</div>
		</div>
	</div>

	<!-- Contact Us Popup Card -->
	<div id="contactPopup" class="popup-overlay">
		<div class="popup-card">
			<button class="popup-close" id="contactClose">&times;</button>
			<div class="popup-content">
				<h3>Contact Us</h3>
				<p>Get in touch with us for any questions, prayer requests, or to learn more about our church family.</p>
				<div class="contact-info-grid">
					<div class="contact-info-item">
						<i class="fas fa-map-marker-alt"></i>
						<div>
							<h5>Location</h5>
							<p>Tom Mboya Street Tudor, Msa<br>P.O Box 90420-80100 MSA Kenya</p>
						</div>
					</div>
					<div class="contact-info-item">
						<i class="fas fa-phone"></i>
						<div>
							<h5>Phone</h5>
							<p><a href="tel:+254712345678">+254712345678</a></p>
						</div>
					</div>
					<div class="contact-info-item">
						<i class="fas fa-envelope"></i>
						<div>
							<h5>Email</h5>
							<p><a href="mailto:tumsda@gmail.com">tumsda@gmail.com</a></p>
						</div>
					</div>
					<div class="contact-info-item">
						<i class="fas fa-clock"></i>
						<div>
							<h5>Service Times</h5>
							<p>Sabbath School: 9:00 AM<br>Divine Service: 11:00 AM</p>
						</div>
					</div>
				</div>
				<div class="contact-cta">
					<a href="leadership.php#contact" class="btn btn-primary">Send Message</a>
					<a href="https://whatsapp.com/channel/0029Vb5zZEjBKfi4xoxGlI25" target="_blank" class="btn btn-outline-primary">WhatsApp</a>
				</div>
			</div>
		</div>
	</div>
	<script src="assets/js/bootstrap.bundle.min.js"></script>
</body>
</html>
