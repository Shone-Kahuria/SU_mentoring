<?php
/**
 * Dashboard Page for Mentoring Website
 * Developer: Stile
 * 
 * Features:
 * - Role-based dashboard content
 * - For mentors: Display assigned mentees and upcoming sessions
 * - For mentees: Display assigned mentor and upcoming sessions
 * - Session management and quick actions
 */

require_once '../includes/functions.php';
startSession();

// Require user to be logged in
requireLogin();

$user = getCurrentUser();
$userRole = getCurrentUserRole();
$userId = getCurrentUserId();

$pageTitle = ucfirst($userRole) . ' Dashboard - MentorConnect';

// Get user statistics
$stats = [];
try {
    $stmt = executeQuery("CALL GetUserStats(?)", [$userId]);
    if ($stmt) {
        $stats = $stmt->fetch();
    }
} catch (Exception $e) {
    error_log("Dashboard stats error: " . $e->getMessage());
    $stats = [
        'active_mentorships' => 0,
        'completed_mentorships' => 0,
        'completed_sessions' => 0,
        'upcoming_sessions' => 0
    ];
}

// Get mentorships based on role
$mentorships = [];
if ($userRole === 'mentor') {
    $sql = "SELECT m.*, u.full_name as mentee_name, u.email as mentee_email, u.id as mentee_id
            FROM mentorships m 
            JOIN users u ON m.mentee_id = u.id 
            WHERE m.mentor_id = :user_id AND m.status = 'active'
            ORDER BY m.created_at DESC";
} else {
    $sql = "SELECT m.*, u.full_name as mentor_name, u.email as mentor_email, u.id as mentor_id
            FROM mentorships m 
            JOIN users u ON m.mentor_id = u.id 
            WHERE m.mentee_id = :user_id AND m.status = 'active'
            ORDER BY m.created_at DESC";
}

$mentorships = selectRecords($sql, ['user_id' => $userId]) ?: [];

// Get upcoming sessions
$sql = "SELECT s.*, m.mentor_id, m.mentee_id,
               mentor.full_name as mentor_name,
               mentee.full_name as mentee_name
        FROM sessions s
        JOIN mentorships m ON s.mentorship_id = m.id
        JOIN users mentor ON m.mentor_id = mentor.id
        JOIN users mentee ON m.mentee_id = mentee.id
        WHERE (m.mentor_id = :user_id OR m.mentee_id = :user_id)
        AND s.scheduled_date > NOW()
        AND s.status = 'scheduled'
        ORDER BY s.scheduled_date ASC
        LIMIT 5";

$upcomingSessions = selectRecords($sql, ['user_id' => $userId]) ?: [];

// Get recent messages
$sql = "SELECT msg.*, m.mentor_id, m.mentee_id,
               sender.full_name as sender_name,
               CASE 
                   WHEN msg.sender_id = :user_id THEN 'sent'
                   ELSE 'received'
               END as message_type
        FROM messages msg
        JOIN mentorships m ON msg.mentorship_id = m.id
        JOIN users sender ON msg.sender_id = sender.id
        WHERE (m.mentor_id = :user_id OR m.mentee_id = :user_id)
        ORDER BY msg.created_at DESC
        LIMIT 5";

$recentMessages = selectRecords($sql, ['user_id' => $userId]) ?: [];

// Get pending mentorship requests
$pendingRequests = [];
if ($userRole === 'mentor') {
    $sql = "SELECT m.*, u.full_name as mentee_name, u.email as mentee_email
            FROM mentorships m 
            JOIN users u ON m.mentee_id = u.id 
            WHERE m.mentor_id = :user_id AND m.status = 'pending'
            ORDER BY m.created_at DESC";
    $pendingRequests = selectRecords($sql, ['user_id' => $userId]) ?: [];
} else {
    $sql = "SELECT m.*, u.full_name as mentor_name, u.email as mentor_email
            FROM mentorships m 
            JOIN users u ON m.mentor_id = u.id 
            WHERE m.mentee_id = :user_id AND m.status = 'pending'
            ORDER BY m.created_at DESC";
    $pendingRequests = selectRecords($sql, ['user_id' => $userId]) ?: [];
}
?>

<?php include '../includes/header.php'; ?>

<div class="container">
    <!-- Dashboard Header -->
    <div class="dashboard-header">
        <div class="dashboard-title">
            Welcome, <?php echo htmlspecialchars($user['full_name']); ?>!
        </div>
        <div class="dashboard-subtitle">
            <?php if ($userRole === 'mentor'): ?>
                Your Mentor Dashboard - Guide and inspire your mentees
            <?php else: ?>
                Your Mentee Dashboard - Learn and grow with your mentor
            <?php endif; ?>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="dashboard-grid">
        <div class="dashboard-card">
            <div class="card-title">Active Mentorships</div>
            <div class="card-content">
                <div style="font-size: 2rem; font-weight: bold; color: var(--primary-blue);">
                    <?php echo $stats['active_mentorships']; ?>
                </div>
                <p>Currently active mentoring relationships</p>
            </div>
        </div>
        
        <div class="dashboard-card">
            <div class="card-title">Upcoming Sessions</div>
            <div class="card-content">
                <div style="font-size: 2rem; font-weight: bold; color: var(--primary-red);">
                    <?php echo $stats['upcoming_sessions']; ?>
                </div>
                <p>Scheduled mentoring sessions</p>
            </div>
        </div>
        
        <div class="dashboard-card">
            <div class="card-title">Completed Sessions</div>
            <div class="card-content">
                <div style="font-size: 2rem; font-weight: bold; color: var(--success-green);">
                    <?php echo $stats['completed_sessions']; ?>
                </div>
                <p>Total sessions completed</p>
            </div>
        </div>
    </div>

    <!-- Pending Requests -->
    <?php if (!empty($pendingRequests)): ?>
    <div class="dashboard-card mb-2">
        <div class="card-title">Pending Requests</div>
        <div class="card-content">
            <?php foreach ($pendingRequests as $request): ?>
                <div style="border: 1px solid var(--border-color); padding: 1rem; margin-bottom: 1rem; border-radius: 5px;">
                    <h4><?php echo $userRole === 'mentor' ? 'Mentorship Request from ' . htmlspecialchars($request['mentee_name']) : 'Request to ' . htmlspecialchars($request['mentor_name']); ?></h4>
                    <?php if (!empty($request['request_message'])): ?>
                        <p><strong>Message:</strong> <?php echo htmlspecialchars($request['request_message']); ?></p>
                    <?php endif; ?>
                    <p><small>Requested on <?php echo formatDate($request['created_at']); ?></small></p>
                    
                    <?php if ($userRole === 'mentor'): ?>
                        <div class="mt-1">
                            <button class="btn btn-primary" onclick="respondToRequest(<?php echo $request['id']; ?>, 'accept')">Accept</button>
                            <button class="btn btn-secondary" onclick="respondToRequest(<?php echo $request['id']; ?>, 'decline')">Decline</button>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <div class="dashboard-grid">
        <!-- Active Mentorships -->
        <div class="dashboard-card">
            <div class="card-title">
                <?php echo $userRole === 'mentor' ? 'Your Mentees' : 'Your Mentors'; ?>
            </div>
            <div class="card-content">
                <?php if (empty($mentorships)): ?>
                    <p>No active mentorships at the moment.</p>
                    <?php if ($userRole === 'mentee'): ?>
                        <a href="find-mentor.php" class="btn btn-primary">Find a Mentor</a>
                    <?php endif; ?>
                <?php else: ?>
                    <?php foreach ($mentorships as $mentorship): ?>
                        <div style="border-bottom: 1px solid var(--border-color); padding: 1rem 0;">
                            <h4>
                                <?php 
                                echo $userRole === 'mentor' 
                                    ? htmlspecialchars($mentorship['mentee_name'])
                                    : htmlspecialchars($mentorship['mentor_name']); 
                                ?>
                            </h4>
                            <p><small>Started: <?php echo formatDate($mentorship['start_date'] ?: $mentorship['created_at']); ?></small></p>
                            <?php if (!empty($mentorship['goals'])): ?>
                                <p><strong>Goals:</strong> <?php echo htmlspecialchars($mentorship['goals']); ?></p>
                            <?php endif; ?>
                            <div class="mt-1">
                                <a href="messages.php?mentorship=<?php echo $mentorship['id']; ?>" class="btn btn-primary" style="font-size: 0.875rem; padding: 8px 16px;">Message</a>
                                <a href="schedule-session.php?mentorship=<?php echo $mentorship['id']; ?>" class="btn btn-secondary" style="font-size: 0.875rem; padding: 8px 16px;">Schedule Session</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- Upcoming Sessions -->
        <div class="dashboard-card">
            <div class="card-title">Upcoming Sessions</div>
            <div class="card-content">
                <?php if (empty($upcomingSessions)): ?>
                    <p>No upcoming sessions scheduled.</p>
                <?php else: ?>
                    <?php foreach ($upcomingSessions as $session): ?>
                        <div style="border-bottom: 1px solid var(--border-color); padding: 1rem 0;">
                            <h4><?php echo htmlspecialchars($session['title']); ?></h4>
                            <p><strong>Date:</strong> <?php echo formatDate($session['scheduled_date']); ?></p>
                            <p><strong>Duration:</strong> <?php echo $session['duration_minutes']; ?> minutes</p>
                            <p><strong>With:</strong> 
                                <?php 
                                echo $session['mentor_id'] == $userId 
                                    ? htmlspecialchars($session['mentee_name'])
                                    : htmlspecialchars($session['mentor_name']); 
                                ?>
                            </p>
                            <?php if (!empty($session['meeting_link'])): ?>
                                <a href="<?php echo htmlspecialchars($session['meeting_link']); ?>" class="btn btn-primary" style="font-size: 0.875rem; padding: 8px 16px;" target="_blank">Join Meeting</a>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                    <div class="mt-1">
                        <a href="sessions.php" class="link">View all sessions →</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Recent Activity -->
    <div class="dashboard-card">
        <div class="card-title">Recent Messages</div>
        <div class="card-content">
            <?php if (empty($recentMessages)): ?>
                <p>No recent messages.</p>
            <?php else: ?>
                <?php foreach ($recentMessages as $message): ?>
                    <div style="border-bottom: 1px solid var(--border-color); padding: 1rem 0;">
                        <div style="display: flex; justify-content: space-between; align-items: start;">
                            <div>
                                <strong>
                                    <?php echo $message['message_type'] === 'sent' ? 'You' : htmlspecialchars($message['sender_name']); ?>
                                </strong>
                                <p style="margin: 0.5rem 0;"><?php echo htmlspecialchars(substr($message['message'], 0, 100)) . (strlen($message['message']) > 100 ? '...' : ''); ?></p>
                            </div>
                            <small><?php echo formatDate($message['created_at'], 'M j, g:i A'); ?></small>
                        </div>
                    </div>
                <?php endforeach; ?>
                <div class="mt-1">
                    <a href="messages.php" class="link">View all messages →</a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="dashboard-card">
        <div class="card-title">Quick Actions</div>
        <div class="card-content">
            <div style="display: flex; flex-wrap: wrap; gap: 1rem;">
                <?php if ($userRole === 'mentor'): ?>
                    <a href="profile.php" class="btn btn-primary">Update Profile</a>
                    <a href="availability.php" class="btn btn-secondary">Set Availability</a>
                    <a href="mentees.php" class="btn btn-primary">Browse Mentee Requests</a>
                <?php else: ?>
                    <a href="find-mentor.php" class="btn btn-primary">Find Mentors</a>
                    <a href="profile.php" class="btn btn-secondary">Update Profile</a>
                    <a href="goals.php" class="btn btn-primary">Set Learning Goals</a>
                <?php endif; ?>
                <a href="sessions.php" class="btn btn-secondary">View All Sessions</a>
                <a href="messages.php" class="btn btn-primary">All Messages</a>
            </div>
        </div>
    </div>
</div>

<script>
// Dashboard specific JavaScript
document.addEventListener('DOMContentLoaded', function() {
    // Auto-refresh upcoming sessions every 5 minutes
    setInterval(function() {
        // Only refresh if user is still on the page
        if (!document.hidden) {
            checkForNewSessions();
        }
    }, 300000); // 5 minutes
    
    // Check session status periodically
    checkSession();
});

function respondToRequest(requestId, action) {
    if (!confirm(`Are you sure you want to ${action} this mentorship request?`)) {
        return;
    }
    
    const formData = new FormData();
    formData.append('request_id', requestId);
    formData.append('action', action);
    formData.append('csrf_token', '<?php echo generateCSRFToken(); ?>');
    
    fetch('api/respond-mentorship.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert(data.message, 'success');
            setTimeout(() => location.reload(), 1500);
        } else {
            showAlert(data.message, 'error');
        }
    })
    .catch(error => {
        showAlert('An error occurred. Please try again.', 'error');
    });
}

function checkForNewSessions() {
    fetch('api/check-sessions.php')
    .then(response => response.json())
    .then(data => {
        if (data.newSessions && data.newSessions.length > 0) {
            showAlert(`You have ${data.newSessions.length} new session(s) scheduled!`, 'info');
        }
    })
    .catch(error => {
        console.log('Session check failed:', error);
    });
}

// Dashboard refresh button
function refreshDashboard() {
    location.reload();
}
</script>

<?php include '../includes/footer.php'; ?>