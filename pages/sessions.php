<?php
/**
 * sessions.php — Mentoring Sessions Page
 * Displays a list of mentoring sessions for mentors and mentees
 */

require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/db.php';

startSession();
requireLogin();

$userId = getCurrentUserId();
$userRole = getCurrentUserRole();

// Query sessions depending on role
if ($userRole === 'mentor') {
    $sql = "
        SELECT s.id, s.title, s.description, s.scheduled_date, s.duration_minutes,
               s.status, u.full_name AS mentee_name
        FROM sessions s
        JOIN mentorships m ON s.mentorship_id = m.id
        JOIN users u ON m.mentee_id = u.id
        WHERE m.mentor_id = :userId
        ORDER BY s.scheduled_date DESC
    ";
} elseif ($userRole === 'mentee') {
    $sql = "
        SELECT s.id, s.title, s.description, s.scheduled_date, s.duration_minutes,
               s.status, u.full_name AS mentor_name
        FROM sessions s
        JOIN mentorships m ON s.mentorship_id = m.id
        JOIN users u ON m.mentor_id = u.id
        WHERE m.mentee_id = :userId
        ORDER BY s.scheduled_date DESC
    ";
} else {
    // Admins see all sessions
    $sql = "
        SELECT s.id, s.title, s.description, s.scheduled_date, s.duration_minutes,
               s.status,
               mentor.full_name AS mentor_name,
               mentee.full_name AS mentee_name
        FROM sessions s
        JOIN mentorships m ON s.mentorship_id = m.id
        JOIN users mentor ON m.mentor_id = mentor.id
        JOIN users mentee ON m.mentee_id = mentee.id
        ORDER BY s.scheduled_date DESC
    ";
}

$params = ['userId' => $userId];
$sessions = selectRecords($sql, $params);

// Page setup
$pageTitle = 'Mentoring Sessions';
include __DIR__ . '/../includes/header.php';
?>

<section class="container">
    <h2 class="page-title">Mentoring Sessions</h2>
    <p>Welcome, <strong><?php echo htmlspecialchars(getCurrentUser()['full_name']); ?></strong>!</p>

    <?php if ($sessions && count($sessions) > 0): ?>
        <table class="styled-table" border="1" cellpadding="10" cellspacing="0">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Title</th>
                    <?php if ($userRole === 'mentor'): ?>
                        <th>Mentee</th>
                    <?php elseif ($userRole === 'mentee'): ?>
                        <th>Mentor</th>
                    <?php else: ?>
                        <th>Mentor</th>
                        <th>Mentee</th>
                    <?php endif; ?>
                    <th>Date</th>
                    <th>Duration</th>
                    <th>Status</th>
                    <th>Description</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($sessions as $s): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($s['id']); ?></td>
                        <td><?php echo htmlspecialchars($s['title']); ?></td>

                        <?php if ($userRole === 'mentor'): ?>
                            <td><?php echo htmlspecialchars($s['mentee_name']); ?></td>
                        <?php elseif ($userRole === 'mentee'): ?>
                            <td><?php echo htmlspecialchars($s['mentor_name']); ?></td>
                        <?php else: ?>
                            <td><?php echo htmlspecialchars($s['mentor_name']); ?></td>
                            <td><?php echo htmlspecialchars($s['mentee_name']); ?></td>
                        <?php endif; ?>

                        <td><?php echo formatDate($s['scheduled_date']); ?></td>
                        <td><?php echo htmlspecialchars($s['duration_minutes']); ?> min</td>
                        <td><?php echo ucfirst(htmlspecialchars($s['status'])); ?></td>
                        <td><?php echo htmlspecialchars($s['description']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <div class="no-sessions">
            <p>No sessions found yet.</p>
        </div>
    <?php endif; ?>
</section>

<?php include __DIR__ . '/../includes/footer.php'; ?>
