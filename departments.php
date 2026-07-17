<?php
$currentPage = basename($_SERVER['PHP_SELF']);
$departments = [];
try {
    require_once 'db_connect.php';
    $pdo = getPublicDB();
    $stmt = $pdo->query("SELECT * FROM departments_ministries WHERE type = 'department' ORDER BY sort_order ASC");
    $departments = $stmt->fetchAll();
} catch (Exception $e) {
    $departments = [];
}
include 'header.php';
?>

<section class="section">
	<div class="container">
		<!-- Solid block with text -->
		<div class="page-hero-block">
			<div class="page-hero-content">
				<h1 class="page-hero-title">Departments</h1>
				<p class="page-hero-description">These ministries help our community function and flourish.</p>
			</div>
		</div>
		<div class="row g-4 mt-4">
			<?php if (empty($departments)): ?>
			<div class="col-12 text-center py-5">
				<p class="text-muted">No departments found.</p>
			</div>
			<?php else: ?>
			<?php foreach ($departments as $dept): ?>
			<div class="col-lg-6">
				<div class="card department-card h-100 shadow-sm">
					<div class="card-body d-flex flex-column justify-content-between">
						<div>
							<h5 class="card-title mb-3 d-flex align-items-center gap-2">
								<?php if ($dept['logo_path']): ?>
									<img src="<?php echo htmlspecialchars($dept['logo_path']); ?>" alt="Logo" class="rounded-circle border shadow-sm" style="width: 32px; height: 32px; object-fit: cover;" onError="this.style.display='none'">
								<?php endif; ?>
								<span><?php echo htmlspecialchars($dept['name']); ?></span>
							</h5>
							<p class="card-text mb-3"><?php echo htmlspecialchars($dept['description']); ?></p>
							<?php if ($dept['scripture_quote']): ?>
							<blockquote class="blockquote mb-3">
								<footer class="blockquote-footer">
									<cite title="Source Title">
										"<?php echo htmlspecialchars($dept['scripture_quote']); ?>" 
										<?php if ($dept['scripture_reference']): ?>
											<?php echo htmlspecialchars($dept['scripture_reference']); ?>
										<?php endif; ?>
									</cite>
								</footer>
							</blockquote>
							<?php endif; ?>
						</div>
						
						<div>
							<?php if ($dept['external_link']): ?>
							<p class="mb-3">
								<a href="<?php echo htmlspecialchars($dept['external_link']); ?>" target="_blank" rel="noopener" class="btn btn-outline-primary btn-sm">Visit Site</a>
							</p>
							<?php endif; ?>

							<?php if ($dept['chair_name']): ?>
							<div class="mt-3 pt-3 border-top d-flex align-items-center justify-content-between">
								<div class="d-flex align-items-center gap-2">
									<?php if ($dept['chair_photo_path']): ?>
										<img src="<?php echo htmlspecialchars($dept['chair_photo_path']); ?>" alt="<?php echo htmlspecialchars($dept['chair_name']); ?>" class="rounded-circle border shadow-sm" style="width: 36px; height: 36px; object-fit: cover;" onError="this.src='assets/img/avatar.png'">
									<?php else: ?>
										<div class="rounded-circle bg-light border d-flex align-items-center justify-content-center shadow-sm" style="width: 36px; height: 36px; color: #64748b;"><i class="fas fa-user-tie"></i></div>
									<?php endif; ?>
									<div>
										<span class="d-block text-muted small" style="font-size: 0.7rem; font-weight: 600; font-family: 'League Spartan', sans-serif;">CHAIRPERSON</span>
										<strong class="d-block text-dark" style="font-size: 0.85rem; font-family: 'League Spartan', sans-serif;"><?php echo htmlspecialchars($dept['chair_name']); ?></strong>
									</div>
								</div>
								<?php if ($dept['logo_path']): ?>
									<img src="<?php echo htmlspecialchars($dept['logo_path']); ?>" alt="Department Logo" style="max-height: 28px; max-width: 80px; object-fit: contain;" onError="this.style.display='none'">
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
