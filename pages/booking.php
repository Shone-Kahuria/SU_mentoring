<?php
/**
 * Booking Page for Mentoring Website
 * Developer: Ezra
 * 
 * Features:
 * - JavaScript calendar (using FullCalendar)
 * - Allow mentees to request sessions from mentors
 * - Mentors can approve or decline requests
 * - Store bookings in DB with status (pending/confirmed)
 * - Session management and scheduling
 */

require_once '../includes/functions.php';
startSession();

// Require user to be logged in
requireLogin();

$user = getCurrentUser();
$userRole = getCurrentUserRole();
$userId = getCurrentUserId();

$pageTitle = 'Session Booking - MentorConnect';
$errors = [];
$successMessage = '';

// Get mentorship ID from URL parameter
$mentorshipId = isset($_GET['mentorship']) ? (int)$_GET['mentorship'] : null;
$selectedMentorship = null;

// Get user's mentorships
$mentorships = [];
if ($userRole === 'mentor') {
    $sql = "SELECT m.*, u.full_name as mentee_name, u.email as mentee_email
            FROM mentorships m 
            JOIN users u ON m.mentee_id = u.id 
            WHERE m.mentor_id = :user_id AND m.status = 'active'
            ORDER BY m.created_at DESC";
} else {
    $sql = "SELECT m.*, u.full_name as mentor_name, u.email as mentor_email
            FROM mentorships m 
            JOIN users u ON m.mentor_id = u.id 
            WHERE m.mentee_id = :user_id AND m.status = 'active'
            ORDER BY m.created_at DESC";
}

$mentorships = selectRecords($sql, ['user_id' => $userId]) ?: [];

// Validate mentorship selection
if ($mentorshipId) {
    foreach ($mentorships as $mentorship) {
        if ($mentorship['id'] == $mentorshipId) {
            $selectedMentorship = $mentorship;
            break;
        }
    }
    
    if (!$selectedMentorship) {
        $errors[] = 'Invalid mentorship selected.';
        $mentorshipId = null;
    }
}

// Handle session booking request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['book_session'])) {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid security token. Please try again.';
    } else {
        $mentorshipId = (int)($_POST['mentorship_id'] ?? 0);
        $title = sanitizeInput($_POST['title'] ?? '');
        $description = sanitizeInput($_POST['description'] ?? '');
        $scheduledDate = $_POST['scheduled_date'] ?? '';
        $duration = (int)($_POST['duration'] ?? 60);
        $meetingLink = sanitizeInput($_POST['meeting_link'] ?? '');
        
        // Validation
        if (!$mentorshipId) {
            $errors[] = 'Please select a valid mentorship.';
        }
        
        if (empty($title)) {
            $errors[] = 'Session title is required.';
        }
        
        if (empty($scheduledDate)) {
            $errors[] = 'Please select a date and time for the session.';
        } else {
            $scheduledDateTime = DateTime::createFromFormat('Y-m-d\TH:i', $scheduledDate);
            if (!$scheduledDateTime || $scheduledDateTime <= new DateTime()) {
                $errors[] = 'Please select a future date and time.';
            }
        }
        
        if ($duration < 15 || $duration > 240) {
            $errors[] = 'Session duration must be between 15 and 240 minutes.';
        }
        
        // Check if user is part of the selected mentorship
        $validMentorship = false;
        foreach ($mentorships as $mentorship) {
            if ($mentorship['id'] == $mentorshipId) {
                $validMentorship = true;
                $selectedMentorship = $mentorship;
                break;
            }
        }
        
        if (!$validMentorship) {
            $errors[] = 'You are not authorized to book sessions for this mentorship.';
        }
        
        // Check for scheduling conflicts
        if (empty($errors) && $scheduledDateTime) {
            $conflictSql = "SELECT id FROM sessions 
                           WHERE mentorship_id = :mentorship_id 
                           AND status = 'scheduled'
                           AND (
                               (scheduled_date <= :start_time AND DATE_ADD(scheduled_date, INTERVAL duration_minutes MINUTE) > :start_time)
                               OR (scheduled_date < :end_time AND DATE_ADD(scheduled_date, INTERVAL duration_minutes MINUTE) >= :end_time)
                               OR (scheduled_date >= :start_time AND scheduled_date < :end_time)
                           )";
            
            $endDateTime = clone $scheduledDateTime;
            $endDateTime->add(new DateInterval('PT' . $duration . 'M'));
            
            $conflict = selectRecord($conflictSql, [
                'mentorship_id' => $mentorshipId,
                'start_time' => $scheduledDateTime->format('Y-m-d H:i:s'),
                'end_time' => $endDateTime->format('Y-m-d H:i:s')
            ]);
            
            if ($conflict) {
                $errors[] = 'This time slot conflicts with an existing session. Please choose a different time.';
            }
        }
        
        // Create session if no errors
        if (empty($errors)) {
            try {
                $sessionData = [
                    'mentorship_id' => $mentorshipId,
                    'title' => $title,
                    'description' => $description,
                    'scheduled_date' => $scheduledDateTime->format('Y-m-d H:i:s'),
                    'duration_minutes' => $duration,
                    'meeting_link' => $meetingLink,
                    'status' => $userRole === 'mentor' ? 'scheduled' : 'pending',
                    'created_by' => $userId,
                    'created_at' => date('Y-m-d H:i:s')
                ];
                
                $sessionId = insertRecord('sessions', $sessionData);
                
                if ($sessionId) {
                    // Log activity
                    logActivity('session_created', [
                        'session_id' => $sessionId,
                        'mentorship_id' => $mentorshipId,
                        'status' => $sessionData['status']
                    ]);
                    
                    if ($userRole === 'mentor') {
                        $successMessage = 'Session scheduled successfully!';
                    } else {
                        $successMessage = 'Session request sent to your mentor for approval!';
                    }
                    
                    // Clear form data
                    $_POST = [];
                } else {
                    $errors[] = 'Failed to create session. Please try again.';
                }
            } catch (Exception $e) {
                error_log('Session booking error: ' . $e->getMessage());
                $errors[] = 'An error occurred while booking the session.';
            }
        }
    }
}

// Handle session status updates (approve/decline)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_session'])) {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid security token. Please try again.';
    } else {
        $sessionId = (int)($_POST['session_id'] ?? 0);
        $action = $_POST['action'] ?? '';
        
        if (!in_array($action, ['approve', 'decline'])) {
            $errors[] = 'Invalid action.';
        } else {
            // Verify user can update this session
            $sessionSql = "SELECT s.*, m.mentor_id, m.mentee_id 
                          FROM sessions s 
                          JOIN mentorships m ON s.mentorship_id = m.id 
                          WHERE s.id = :session_id AND m.mentor_id = :user_id";
            
            $session = selectRecord($sessionSql, [
                'session_id' => $sessionId,
                'user_id' => $userId
            ]);
            
            if (!$session) {
                $errors[] = 'Session not found or you are not authorized to update it.';
            } else {
                $newStatus = $action === 'approve' ? 'scheduled' : 'cancelled';
                $updateData = [
                    'status' => $newStatus,
                    'updated_at' => date('Y-m-d H:i:s')
                ];
                
                if ($action === 'decline') {
                    $updateData['cancelled_by'] = $userId;
                    $updateData['cancellation_reason'] = 'Declined by mentor';
                }
                
                $success = updateRecord('sessions', $updateData, 'id = :id', ['id' => $sessionId]);
                
                if ($success) {
                    logActivity('session_updated', [
                        'session_id' => $sessionId,
                        'action' => $action,
                        'new_status' => $newStatus
                    ]);
                    
                    $successMessage = 'Session ' . ($action === 'approve' ? 'approved' : 'declined') . ' successfully!';
                } else {
                    $errors[] = 'Failed to update session status.';
                }
            }
        }
    }
}

// Get sessions for calendar display
$calendarSql = "SELECT s.*, m.mentor_id, m.mentee_id,
                       mentor.full_name as mentor_name,
                       mentee.full_name as mentee_name
                FROM sessions s
                JOIN mentorships m ON s.mentorship_id = m.id
                JOIN users mentor ON m.mentor_id = mentor.id
                JOIN users mentee ON m.mentee_id = mentee.id
                WHERE (m.mentor_id = :user_id OR m.mentee_id = :user_id)
                AND s.scheduled_date >= DATE_SUB(NOW(), INTERVAL 1 MONTH)
                ORDER BY s.scheduled_date ASC";

$calendarSessions = selectRecords($calendarSql, ['user_id' => $userId]) ?: [];

// Get pending sessions for mentors
$pendingSessions = [];
if ($userRole === 'mentor') {
    $pendingSql = "SELECT s.*, m.mentee_id, u.full_name as mentee_name
                   FROM sessions s
                   JOIN mentorships m ON s.mentorship_id = m.id
                   JOIN users u ON m.mentee_id = u.id
                   WHERE m.mentor_id = :user_id AND s.status = 'pending'
                   ORDER BY s.created_at DESC";
    
    $pendingSessions = selectRecords($pendingSql, ['user_id' => $userId]) ?: [];
}

// Generate CSRF token
$csrfToken = generateCSRFToken();

// Prepare calendar events for JavaScript
$calendarEvents = [];
foreach ($calendarSessions as $session) {
    $calendarEvents[] = [
        'id' => $session['id'],
        'title' => $session['title'],
        'start' => $session['scheduled_date'],
        'end' => date('Y-m-d H:i:s', strtotime($session['scheduled_date'] . ' +' . $session['duration_minutes'] . ' minutes')),
        'color' => $session['status'] === 'scheduled' ? '#0057B7' : 
                  ($session['status'] === 'pending' ? '#FFC107' : '#D62828'),
        'extendedProps' => [
            'status' => $session['status'],
            'description' => $session['description'],
            'mentor_name' => $session['mentor_name'],
            'mentee_name' => $session['mentee_name'],
            'duration' => $session['duration_minutes'],
            'meeting_link' => $session['meeting_link']
        ]
    ];
}
?>

<?php include '../includes/header.php'; ?>

<link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.css" rel="stylesheet">

<div class="container">
    <!-- Header -->
    <div class="dashboard-header">
        <div class="dashboard-title">Session Booking</div>
        <div class="dashboard-subtitle">
            Schedule and manage your mentoring sessions
        </div>
    </div>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-error">
            <ul style="margin: 0; padding-left: 20px;">
                <?php foreach ($errors as $error): ?>
                    <li><?php echo htmlspecialchars($error); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <?php if ($successMessage): ?>
        <div class="alert alert-success">
            <?php echo htmlspecialchars($successMessage); ?>
        </div>
    <?php endif; ?>

    <!-- Pending Sessions (for mentors) -->
    <?php if (!empty($pendingSessions)): ?>
    <div class="dashboard-card mb-2">
        <div class="card-title">Pending Session Requests</div>
        <div class="card-content">
            <?php foreach ($pendingSessions as $session): ?>
                <div style="border: 1px solid var(--border-color); padding: 1rem; margin-bottom: 1rem; border-radius: 5px; background: #fff3cd;">
                    <h4><?php echo htmlspecialchars($session['title']); ?></h4>
                    <p><strong>Requested by:</strong> <?php echo htmlspecialchars($session['mentee_name']); ?></p>
                    <p><strong>Date & Time:</strong> <?php echo formatDate($session['scheduled_date']); ?></p>
                    <p><strong>Duration:</strong> <?php echo $session['duration_minutes']; ?> minutes</p>
                    <?php if (!empty($session['description'])): ?>
                        <p><strong>Description:</strong> <?php echo htmlspecialchars($session['description']); ?></p>
                    <?php endif; ?>
                    
                    <form method="POST" style="display: inline-block; margin-top: 1rem;">
                        <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                        <input type="hidden" name="update_session" value="1">
                        <input type="hidden" name="session_id" value="<?php echo $session['id']; ?>">
                        <button type="submit" name="action" value="approve" class="btn btn-primary">Approve</button>
                        <button type="submit" name="action" value="decline" class="btn btn-secondary">Decline</button>
                    </form>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <div class="dashboard-grid">
        <!-- Booking Form -->
        <div class="dashboard-card">
            <div class="card-title">
                <?php echo $userRole === 'mentor' ? 'Schedule New Session' : 'Request New Session'; ?>
            </div>
            <div class="card-content">
                <?php if (empty($mentorships)): ?>
                    <p>You don't have any active mentorships. You need an active mentorship to book sessions.</p>
                    <?php if ($userRole === 'mentee'): ?>
                        <a href="find-mentor.php" class="btn btn-primary">Find a Mentor</a>
                    <?php endif; ?>
                <?php else: ?>
                    <form method="POST" id="bookingForm">
                        <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                        <input type="hidden" name="book_session" value="1">
                        
                        <div class="form-group">
                            <label for="mentorship_id">Select Mentorship</label>
                            <select id="mentorship_id" name="mentorship_id" class="form-control" required>
                                <option value="">Choose a mentorship...</option>
                                <?php foreach ($mentorships as $mentorship): ?>
                                    <option value="<?php echo $mentorship['id']; ?>" 
                                            <?php echo ($mentorshipId == $mentorship['id']) ? 'selected' : ''; ?>>
                                        <?php 
                                        echo $userRole === 'mentor' 
                                            ? 'Mentee: ' . htmlspecialchars($mentorship['mentee_name'])
                                            : 'Mentor: ' . htmlspecialchars($mentorship['mentor_name']); 
                                        ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="title">Session Title</label>
                            <input 
                                type="text" 
                                id="title" 
                                name="title" 
                                class="form-control" 
                                required
                                maxlength="255"
                                placeholder="e.g., Career Planning Discussion"
                                value="<?php echo htmlspecialchars($_POST['title'] ?? ''); ?>"
                            >
                        </div>
                        
                        <div class="form-group">
                            <label for="description">Description (Optional)</label>
                            <textarea 
                                id="description" 
                                name="description" 
                                class="form-control" 
                                rows="3"
                                maxlength="500"
                                placeholder="Describe what you'd like to discuss in this session..."
                            ><?php echo htmlspecialchars($_POST['description'] ?? ''); ?></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label for="scheduled_date">Date & Time</label>
                            <input 
                                type="datetime-local" 
                                id="scheduled_date" 
                                name="scheduled_date" 
                                class="form-control" 
                                required
                                min="<?php echo date('Y-m-d\TH:i', strtotime('+1 hour')); ?>"
                                value="<?php echo $_POST['scheduled_date'] ?? ''; ?>"
                            >
                        </div>
                        
                        <div class="form-group">
                            <label for="duration">Duration (minutes)</label>
                            <select id="duration" name="duration" class="form-control" required>
                                <option value="30" <?php echo ($_POST['duration'] ?? 60) == 30 ? 'selected' : ''; ?>>30 minutes</option>
                                <option value="45" <?php echo ($_POST['duration'] ?? 60) == 45 ? 'selected' : ''; ?>>45 minutes</option>
                                <option value="60" <?php echo ($_POST['duration'] ?? 60) == 60 ? 'selected' : ''; ?>>1 hour</option>
                                <option value="90" <?php echo ($_POST['duration'] ?? 60) == 90 ? 'selected' : ''; ?>>1.5 hours</option>
                                <option value="120" <?php echo ($_POST['duration'] ?? 60) == 120 ? 'selected' : ''; ?>>2 hours</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="meeting_link">Meeting Link (Optional)</label>
                            <input 
                                type="url" 
                                id="meeting_link" 
                                name="meeting_link" 
                                class="form-control" 
                                placeholder="https://zoom.us/j/123456789 or https://meet.google.com/abc-def-ghi"
                                value="<?php echo htmlspecialchars($_POST['meeting_link'] ?? ''); ?>"
                            >
                            <small style="color: #666;">Zoom, Google Meet, or any other video conferencing link</small>
                        </div>
                        
                        <button type="submit" class="btn btn-primary btn-full">
                            <?php echo $userRole === 'mentor' ? 'Schedule Session' : 'Request Session'; ?>
                        </button>
                    </form>
                <?php endif; ?>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="dashboard-card">
            <div class="card-title">Quick Actions</div>
            <div class="card-content">
                <div style="display: flex; flex-direction: column; gap: 1rem;">
                    <a href="dashboard.php" class="btn btn-primary">Back to Dashboard</a>
                    <a href="sessions.php" class="btn btn-secondary">View All Sessions</a>
                    <a href="messages.php" class="btn btn-primary">Messages</a>
                    <?php if ($userRole === 'mentee'): ?>
                        <a href="find-mentor.php" class="btn btn-secondary">Find More Mentors</a>
                    <?php endif; ?>
                </div>
                
                <div style="margin-top: 2rem; padding-top: 1rem; border-top: 1px solid var(--border-color);">
                    <h4>Legend</h4>
                    <div style="display: flex; flex-direction: column; gap: 0.5rem;">
                        <div style="display: flex; align-items: center; gap: 0.5rem;">
                            <div style="width: 20px; height: 20px; background: #0057B7; border-radius: 3px;"></div>
                            <span>Scheduled Sessions</span>
                        </div>
                        <div style="display: flex; align-items: center; gap: 0.5rem;">
                            <div style="width: 20px; height: 20px; background: #FFC107; border-radius: 3px;"></div>
                            <span>Pending Approval</span>
                        </div>
                        <div style="display: flex; align-items: center; gap: 0.5rem;">
                            <div style="width: 20px; height: 20px; background: #D62828; border-radius: 3px;"></div>
                            <span>Cancelled/Declined</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Calendar -->
    <div class="dashboard-card">
        <div class="card-title">Session Calendar</div>
        <div class="card-content">
            <div id="calendar"></div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const calendarEl = document.getElementById('calendar');
    const calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay'
        },
        events: <?php echo json_encode($calendarEvents); ?>,
        eventClick: function(info) {
            const event = info.event;
            const props = event.extendedProps;
            
            let content = `
                <h3>${event.title}</h3>
                <p><strong>Date:</strong> ${new Date(event.start).toLocaleString()}</p>
                <p><strong>Duration:</strong> ${props.duration} minutes</p>
                <p><strong>Status:</strong> ${props.status}</p>
                <p><strong>Mentor:</strong> ${props.mentor_name}</p>
                <p><strong>Mentee:</strong> ${props.mentee_name}</p>
            `;
            
            if (props.description) {
                content += `<p><strong>Description:</strong> ${props.description}</p>`;
            }
            
            if (props.meeting_link) {
                content += `<p><strong>Meeting Link:</strong> <a href="${props.meeting_link}" target="_blank">Join Meeting</a></p>`;
            }
            
            // Show modal or alert with session details
            showSessionDetails(content);
        },
        eventDidMount: function(info) {
            // Add tooltip
            info.el.title = `${info.event.title} - ${info.event.extendedProps.status}`;
        }
    });
    
    calendar.render();
    
    // Form validation
    document.getElementById('bookingForm').addEventListener('submit', function(e) {
        const scheduledDate = new Date(document.getElementById('scheduled_date').value);
        const now = new Date();
        
        if (scheduledDate <= now) {
            e.preventDefault();
            showAlert('Please select a future date and time.', 'error');
            return;
        }
        
        // Check if it's during business hours (optional)
        const hour = scheduledDate.getHours();
        if (hour < 8 || hour > 20) {
            if (!confirm('This session is scheduled outside typical business hours (8 AM - 8 PM). Continue?')) {
                e.preventDefault();
                return;
            }
        }
    });
});

function showSessionDetails(content) {
    // Create a modal or use alert for session details
    const modal = document.createElement('div');
    modal.style.cssText = `
        position: fixed; top: 0; left: 0; width: 100%; height: 100%; 
        background: rgba(0,0,0,0.5); z-index: 1000; display: flex; 
        align-items: center; justify-content: center;
    `;
    
    const modalContent = document.createElement('div');
    modalContent.style.cssText = `
        background: white; padding: 2rem; border-radius: 10px; 
        max-width: 500px; width: 90%; max-height: 80vh; overflow-y: auto;
    `;
    
    modalContent.innerHTML = content + `
        <div style="margin-top: 2rem; text-align: center;">
            <button onclick="this.closest('.modal').remove()" class="btn btn-primary">Close</button>
        </div>
    `;
    
    modal.className = 'modal';
    modal.appendChild(modalContent);
    document.body.appendChild(modal);
    
    // Close on outside click
    modal.addEventListener('click', function(e) {
        if (e.target === modal) {
            modal.remove();
        }
    });
}

// Auto-populate datetime field with next available hour
window.addEventListener('load', function() {
    const dateInput = document.getElementById('scheduled_date');
    if (!dateInput.value) {
        const now = new Date();
        now.setHours(now.getHours() + 1);
        now.setMinutes(0);
        dateInput.value = now.toISOString().slice(0, 16);
    }
});
</script>

<?php include '../includes/footer.php'; ?>