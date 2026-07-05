<?php
// header.php - shared header and navigation
if (!isset($currentPage)) {
    $currentPage = basename($_SERVER['PHP_SELF']);
}
?><!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
	<title>TUMSDA Church</title>
	<meta name="description" content="TUMSDA Church - The Church we love the most">
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=League+Spartan:wght@400;600;700;800&family=Source+Sans+Pro:wght@400;700&family=Noto+Sans:wght@400;70[...]" rel="stylesheet">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvWZOJyj7Yy7tcenmpD1ypASozpmT/E0iPtmFIB46ZmdtAc9[...]" crossorigin="anonymous" referrerpolicy="no-referrer" />
	<link rel="stylesheet" href="assets/bootstrap.min.css">
	<link rel="stylesheet" href="assets/all.min.css">
	<link rel="stylesheet" href="assets/style.css">
	<link rel="stylesheet" href="assets/style-utils.css">
	<link rel="icon" type="image/png" href="assets/img/favicon.png">
	<script src="assets/main.js" defer></script>
</head>
<body>

	<!-- Mobile Side Panel Menu -->
	<div class="mobile-side-panel" id="mobileSidePanel">
		<div class="side-panel-overlay" id="sidePanelOverlay"></div>
		<div class="side-panel-content">
			<div class="side-panel-search">
				<div class="search-container">
					<span class="search-placeholder">Search...</span>
					<div class="search-line"></div>
				</div>
			</div>
			<nav class="side-panel-nav">
				<ul class="side-panel-menu">
					<li class="side-panel-item">
						<a class="side-panel-link <?php echo ($currentPage=='index.php') ? 'active' : ''; ?>" href="index.php">
							<span>Home</span>
						</a>
					</li>
					<li class="side-panel-item">
						<a class="side-panel-link <?php echo ($currentPage=='about.php') ? 'active' : ''; ?>" href="about.php">
							<span>About Us</span>
						</a>
					</li>
					<li class="side-panel-item">
						<a class="side-panel-link <?php echo ($currentPage=='departments.php') ? 'active' : ''; ?>" href="departments.php">
							<span>Departments</span>
						</a>
					</li>
					<li class="side-panel-item">
						<a class="side-panel-link <?php echo ($currentPage=='ministries.php') ? 'active' : ''; ?>" href="ministries.php">
							<span>Ministries</span>
						</a>
					</li>
					<li class="side-panel-item">
						<a class="side-panel-link <?php echo ($currentPage=='evangelism.php') ? 'active' : ''; ?>" href="evangelism.php">
							<span>Evangelism</span>
						</a>
					</li>
					<li class="side-panel-item">
						<a class="side-panel-link <?php echo ($currentPage=='sermons.php') ? 'active' : ''; ?>" href="sermons.php">
							<span>Sermons</span>
						</a>
					</li>
					<li class="side-panel-item">
						<a class="side-panel-link <?php echo ($currentPage=='leadership.php') ? 'active' : ''; ?>" href="leadership.php">
							<span>Leadership</span>
						</a>
					</li>
				</ul>
			</nav>
		</div>
		<button class="side-panel-close" id="sidePanelClose">×</button>
	</div>

	<div class="content-wrapper">
		<header>
			<nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom sticky-top">
				<div class="container">
					<a class="navbar-brand d-flex align-items-center gap-2" href="index.php">
						<img src="assets/img/logo.jpg" alt="TUMSDA Logo" width="36" height="36" onerror="this.style.display='none'">
					</a>
					<button class="navbar-toggler d-lg-none" type="button" id="mobileMenuToggle" aria-label="Toggle mobile menu">
						<span class="navbar-toggler-icon"></span>
					</button>
					<div class="collapse navbar-collapse d-none d-lg-block" id="mainNav">
						<ul class="navbar-nav ms-auto mb-2 mb-lg-0">
							<li class="nav-item">
								<a class="nav-link <?php echo ($currentPage=='index.php') ? 'active' : ''; ?>" href="index.php">Home</a>
							</li>
							<li class="nav-item">
								<a class="nav-link <?php echo ($currentPage=='about.php') ? 'active' : ''; ?>" href="about.php">About Us</a>
							</li>
							<li class="nav-item">
								<a class="nav-link <?php echo ($currentPage=='departments.php') ? 'active' : ''; ?>" href="departments.php">Departments</a>
							</li>
							<li class="nav-item">
								<a class="nav-link <?php echo ($currentPage=='ministries.php') ? 'active' : ''; ?>" href="ministries.php">Ministries</a>
							</li>
							<li class="nav-item">
								<a class="nav-link <?php echo ($currentPage=='evangelism.php') ? 'active' : ''; ?>" href="evangelism.php">Evangelism</a>
							</li>
							<li class="nav-item">
								<a class="nav-link <?php echo ($currentPage=='sermons.php') ? 'active' : ''; ?>" href="sermons.php">Sermons</a>
							</li>
							<li class="nav-item">
								<a class="nav-link <?php echo ($currentPage=='leadership.php') ? 'active' : ''; ?>" href="leadership.php">Leadership</a>
							</li>
						</ul>
					</div>
				</div>
			</nav>
		</header>
		<main>
