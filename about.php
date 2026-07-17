<?php
// about.php — now uses shared header/footer
$currentPage = basename($_SERVER['PHP_SELF']);
$weeklyMeetings = [];
$meetingsByDay = [];
$events = [];
try {
    require_once 'db_connect.php';
    $pdo = getPublicDB();
    // Get weekly meetings
    $stmt = $pdo->query("SELECT * FROM weekly_meetings ORDER BY sort_order ASC");
    $weeklyMeetings = $stmt->fetchAll();
    // Group weekly meetings by day
    foreach ($weeklyMeetings as $meeting) {
        $meetingsByDay[$meeting['day_of_week']][] = $meeting;
    }
    // Get events
    $stmt = $pdo->query("SELECT * FROM events ORDER BY event_date ASC");
    $events = $stmt->fetchAll();
} catch (Exception $e) {
    // If database not set up, use empty arrays
    $weeklyMeetings = [];
    $meetingsByDay = [];
    $events = [];
}
include 'header.php';
?>

<section class="section py-0">
	<img src="assets/img/TUMSDA.png" class="img-fluid w-100 rounded-3" alt="TUMSDA">
</section>
<section class="section">
	<div class="container">
		<div class="row align-items-center">
			<div class="col-lg-6">
				<div class="about-content">
					<h2 class="about-title">About TUMSDA</h2>
					<div class="about-subtitle">
						<i class="fas fa-church me-2"></i>
						<span>Seventh-day Adventist Sabbath School</span>
					</div>
					<div class="about-location">
						<i class="fas fa-map-marker-alt me-2"></i>
						<span>Technical University of Mombasa (TUM), Tudor</span>
					</div>
					<div class="about-tagline">
						<p class="holiday-tagline">The Church We Love The Most!</p>
					</div>
				</div>
			</div>
			<div class="col-lg-6">
				<div class="about-image-container">
					<img src="assets/img/icon2.png" class="about-image" alt="TUMSDA Church">
				</div>
			</div>
		</div>
		
		<div class="row mt-5">
			<div class="col-12">
				<div class="about-description-card">
					<div class="about-description-content text-center">
						<p>TUMSDA Church is a Seventh-day Adventist Sabbath school in Ziwani District and it is a beacon of hope, a sanctuary of spiritual growth, where young hearts beat in unison, yearning for a [...]</p>
						
						<p>The Church is located within the Technical University of Mombasa (TUM) in Tudor, Mombasa.</p>
						
						<p>With fervent passion and unwavering devotion, we gather to nurture our faith, cultivating a profound relationship with the Creator, building a deep love of present truth and reformation [...]</p>
						
						<p>In this sacred space, we as young people are transformed by the power of Bible study, prayer and sacred music, becoming beacons of light in a world beset by darkness. United in our pursu[...]</p>
						
						<p>As a haven of spiritual nourishment, TUMSDA embodies the essence of a community blessed by the divine presence.</p>
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
				<div class="mission-vision-card mission-card">
					<h3 class="mission-vision-title title-with-underline blue-title">Our Mission</h3>
					<div class="mission-vision-content">
						<p>To make disciples of all people by communicating the everlasting gospel in the context of the three angels' messages of Revelation 14:6–12, leading them to accept Jesus as personal Sav[...]</p>
					</div>
				</div>
			</div>
			<div class="col-lg-6">
				<div class="mission-vision-card vision-card">
					<h3 class="mission-vision-title title-with-underline blue-title">Our Vision</h3>
					<div class="mission-vision-content">
						<p>To uphold the distinctive message of the Seventh-day Adventist Church; to aspire to excellence in all aspects of life—academic, social, and spiritual; to embrace radical discipleship i[...]</p>
					</div>
				</div>
			</div>
		</div>
	</div>
</section>
<section class="section">
	<div class="container">
		<div class="text-center">
			<h3 class="fw-semibold">History</h3>
			<p class="mx-auto" style="max-width: 800px;">In 1982, a small group of college students began a movement—born from a hunger for the Word of God, a deeper relationship with Christ, and an act[...]</p>
		</div>
	</div>
</section>
<section class="section bg-white">
	<div class="container">
		<div class="text-center">
			<h3 class="fw-semibold">Beliefs</h3>
			<p class="mb-2 mx-auto" style="max-width: 800px;">We cherish the fundamental beliefs of the Seventh-day Adventist Church.</p>
			<p class="mb-2 mx-auto" style="max-width: 800px;">Upholding the Protestant conviction of Sola Scriptura (“Bible only”), these 28 Fundamental Beliefs describe how Seventh-day Adventists int[...]</p>
			<p><a href="https://www.adventist.org/beliefs/" target="_blank" rel="noopener" class="btn btn-outline-primary">Fundamental Beliefs</a></p>
		</div>
	</div>
</section>
<section id="weekly-meetings" class="section">
	<div class="container">
		<div class="weekly-meetings-card">
			<h3 class="fw-semibold mb-3">Weekly Meetings</h3>
			<p class="mb-4">Find our Weekly Meetings schedules where we meet as a family to engage one another and grow in different aspects, whether social, spiritual or even physically from Sunday to Sa[...]</p>
			<div class="table-responsive">
				<table class="table weekly-meetings-table align-middle">
					<thead>
						<tr>
							<th>Day</th>
							<th>Time</th>
							<th>Program</th>
						</tr>
					</thead>
					<tbody>
						<?php 
						$dayOrder = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
						foreach ($dayOrder as $day): 
							if (!isset($meetingsByDay[$day])) continue;
							$meetings = $meetingsByDay[$day];
							$rowspan = count($meetings);
							$first = true;
						?>
							<?php foreach ($meetings as $meeting): ?>
								<tr>
									<?php if ($first): ?>
										<td rowspan="<?php echo $rowspan; ?>"><?php echo htmlspecialchars($day); ?></td>
									<?php $first = false; endif; ?>
									<td><?php echo htmlspecialchars($meeting['time_range']); ?></td>
									<td><?php echo htmlspecialchars($meeting['program_name']); ?></td>
								</tr>
							<?php endforeach; ?>
						<?php endforeach; ?>
					</tbody>
				</table>
			</div>
		</div>
	</div>
</section>
<section id="calendar" class="section bg-white">
	<div class="container">
		<h3 class="fw-semibold">Church Calendar</h3>
		<div class="table-responsive">
			<table class="table church-calendar-table">
				<thead>
					<tr>
						<th>Date</th>
						<th>Event</th>
						<th>Facilitator</th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ($events as $event): ?>
						<tr>
							<td><?php echo htmlspecialchars($event['event_date']); ?></td>
							<td><?php echo htmlspecialchars($event['title']); ?></td>
							<td><?php echo htmlspecialchars($event['facilitator'] ?? ''); ?></td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		</div>
</section>

<?php include 'footer.php'; ?>
