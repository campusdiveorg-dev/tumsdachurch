<?php
$currentPage = basename($_SERVER['PHP_SELF']);
$ministries = [];
try {
    require_once 'db_connect.php';
    $pdo = getPublicDB();
    $stmt = $pdo->query("SELECT * FROM departments_ministries WHERE type = 'ministry' ORDER BY sort_order ASC");
    $ministries = $stmt->fetchAll();
} catch (Exception $e) {
    $ministries = [];
}
include 'header.php';
?>

<section class="section">
	<div class="container">
		<!-- Solid block with text -->
		<div class="page-hero-block">
			<div class="page-hero-content">
				<h1 class="page-hero-title">Ministries</h1>
				<p class="page-hero-description">Join a ministry and grow in fellowship and service.</p>
			</div>
		</div>
		<div class="row g-4 mt-4">
			<?php if (empty($ministries)): ?>
			<div class="col-12 text-center py-5">
				<p class="text-muted">No ministries found.</p>
			</div>
			<?php else: ?>
			<?php foreach ($ministries as $min): ?>
			<div class="col-lg-4">
				<div class="card ministry-card h-100 shadow-sm">
					<div class="card-body d-flex flex-column justify-content-between">
						<div>
							<h5 class="card-title mb-3 d-flex align-items-center gap-2">
								<?php if ($min['logo_path']): ?>
									<img src="<?php echo htmlspecialchars($min['logo_path']); ?>" alt="Logo" class="rounded-circle border shadow-sm" style="width: 32px; height: 32px; object-fit: cover;" onError="this.style.display='none'">
								<?php endif; ?>
								<span><?php echo htmlspecialchars($min['name']); ?></span>
							</h5>
							<p class="card-text mb-3"><?php echo htmlspecialchars($min['description']); ?></p>
							<?php if ($min['scripture_quote']): ?>
							<blockquote class="blockquote mb-3">
								<footer class="blockquote-footer">
									<cite title="Source Title">
										"<?php echo htmlspecialchars($min['scripture_quote']); ?>" 
										<?php if ($min['scripture_reference']): ?>
											<?php echo htmlspecialchars($min['scripture_reference']); ?>
										<?php endif; ?>
									</cite>
								</footer>
							</blockquote>
							<?php endif; ?>
						</div>
						
						<div>
							<?php if ($min['chair_name']): ?>
							<div class="mt-3 pt-3 border-top d-flex align-items-center justify-content-between">
								<div class="d-flex align-items-center gap-2">
									<?php if ($min['chair_photo_path']): ?>
										<img src="<?php echo htmlspecialchars($min['chair_photo_path']); ?>" alt="<?php echo htmlspecialchars($min['chair_name']); ?>" class="rounded-circle border shadow-sm" style="width: 36px; height: 36px; object-fit: cover;" onError="this.src='assets/img/avatar.png'">
									<?php else: ?>
										<div class="rounded-circle bg-light border d-flex align-items-center justify-content-center shadow-sm" style="width: 36px; height: 36px; color: #64748b;"><i class="fas fa-user-tie"></i></div>
									<?php endif; ?>
									<div>
										<span class="d-block text-muted small" style="font-size: 0.7rem; font-weight: 600; font-family: 'League Spartan', sans-serif;">CHAIRPERSON</span>
										<strong class="d-block text-dark" style="font-size: 0.85rem; font-family: 'League Spartan', sans-serif;"><?php echo htmlspecialchars($min['chair_name']); ?></strong>
									</div>
								</div>
								<?php if ($min['logo_path']): ?>
									<img src="<?php echo htmlspecialchars($min['logo_path']); ?>" alt="Ministry Logo" style="max-height: 28px; max-width: 80px; object-fit: contain;" onError="this.style.display='none'">
								<?php endif; ?>
							</div>
							<?php endif; ?>
						</div>
					</div>
				</div>
			</div>
			<?php endforeach; ?>
			<?php endif; ?>
		</div>
	</div>
</section>

<?php include 'footer.php'; ?>
