<?php
$currentPage = basename($_SERVER['PHP_SELF']);
include 'header.php';
?>

<!-- Leadership Section -->
<section class="section leadership-section">
	<div class="container">
		<div class="row justify-content-center">
			<div class="col-lg-10">
				<div class="leadership-header-card">
					<div class="leadership-header-content text-center">
						<h1 class="fw-bold mb-3">Church Leadership</h1>
						<p class="mb-0">Meet our dedicated church leaders who guide and serve our congregation with love and commitment.</p>
					</div>
				</div>
			</div>
		</div>
		
		<div class="row g-4 justify-content-center">
			<div class="col-lg-4">
				<div class="leadership-card">
					<div class="leadership-image">
						<img src="assets/img/Cephas.jpg" alt="Elder Hosea Chesigor" class="leadership-photo">
					</div>
					<div class="leadership-content">
						<h4 class="leadership-name">Elder Cephas Mukaria</h4>
						<p class="leadership-position">Chairperson, 1st Elder</p>
						<blockquote class="leadership-statement">
							This is a faithful saying that in TUMSDA, hearts are transformed, edified, and lives forever changed by Christ's radiant light.
						</blockquote>
						<p class="leadership-signature">- Cephas Mukaria</p>
					</div>
				</div>
			</div>
			
			<div class="col-lg-4">
				<div class="leadership-card">
					<div class="leadership-image">
						<img src="assets/img/jpg/Gibson.jpg" alt="Elder Gibson Kiprono" class="leadership-photo">
					</div>
					<div class="leadership-content">
						<h4 class="leadership-name">Elder Gibson Kiprono</h4>
						<p class="leadership-position">Assistant Chairperson, Personal Ministries, 2nd Elder</p>
						<blockquote class="leadership-statement">
							Just as Jesus's gentle arms, TUMSDA is a sweet haven of rest!
						</blockquote>
						<p class="leadership-signature">- Gibson Kiprono</p>
					</div>
				</div>
			</div>
			
			<div class="col-lg-4">
				<div class="leadership-card">
					<div class="leadership-image">
						<img src="assets/img/jpg/Daniel.jpg" alt="Elder Daniel Muchoge" class="leadership-photo">
					</div>
					<div class="leadership-content">
						<h4 class="leadership-name">Elder Daniel Muchoge</h4>
						<p class="leadership-position">Assistant Chairperson, Planning, 3rd Elder</p>
						<blockquote class="leadership-statement">
							TUMSDA, a home of watchmen and light bearers!
						</blockquote>
						<p class="leadership-signature">- Daniel Muchoge</p>
					</div>
				</div>
			</div>
		</div>
	</div>
</section>

<!-- Contact Us Section -->
<section class="section contact-section bg-light">
	<div class="container">
		<div class="row">
			<div class="col-12">
				<h2 class="fw-bold mb-4 text-center">Contact Us</h2>
				<p class="lead text-center mb-5">Get in touch with us for any questions, prayer requests, or to learn more about our church</p>
			</div>
		</div>
		
		<div class="row g-4">
			<div class="col-lg-7">
				<form method="post" action="https://api.web3forms.com/submit" class="card shadow-sm">
					<!-- Replace {YOUR_FORM_ID} with your actual Formspree form ID before going live -->
					<input type="hidden" name="access_key" value="f0ddf1cb-9e8c-494f-a7a1-262385c5a479">
					<div class="card-body">
						<div class="mb-3">
							<label class="form-label">Name</label>
							<input type="text" name="name" class="form-control-custom" required>
						</div>
						<div class="mb-3">
							<label class="form-label">Email</label>
							<input type="email" name="email" class="form-control-custom" required>
						</div>
						<div class="mb-3">
							<label class="form-label">Message</label>
							<textarea name="message" class="form-control-custom" rows="5" required></textarea>
						</div>
						<button type="submit" class="td-btn-primary">Send Message</button>
					</div>
					</form>
				</div>
				<div class="col-lg-5">
					<div class="contact-info-card card shadow-sm">
						<div class="card-body">
							<h5 class="mb-3">Contact Information</h5>
							<p><strong>Location:</strong><br>Tom Mboya Street Tudor, Msa<br>P.O Box 90420-80100 MSA Kenya</p>
							<p><strong>Phone:</strong> <a href="tel:+254712345678">+254712345678</a></p>
							<p><strong>Email:</strong> <a href="mailto:tumsda@gmail.com">tumsda@gmail.com</a></p>
							<p><strong>Service Times:</strong><br>Sabbath School: 9:00 AM<br>Divine Service: 11:00 AM</p>
							<div class="mt-3">
								<a href="https://wa.me/254712345678" target="_blank" rel="noopener" class="btn btn-outline-primary">WhatsApp</a>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</section>

<?php include 'footer.php'; ?>
