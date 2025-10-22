<?php
/**
 * Mentor Availability Management
 * Allows mentors to set their available time slots for mentoring sessions
 */

require_once '../includes/db.php';
require_once '../includes/functions.php';

// Start session and check authentication
startSession();
if (!isLoggedIn()) {
    header('Location: login.php');
    exit();
}

$userId = getCurrentUserId();
$userRole = getUserRole($userId);
$pageTitle = 'Set Availability - SU Mentoring';

// DEBUG: Log what we're getting
error_log("Availability page - User ID: " . var_export($userId, true));
error_log("Availability page - User Role: " . var_export($userRole, true));

// Only mentors can access this page
if ($userRole !== 'mentor') {
    // More helpful error message
    $message = "Only mentors can set availability. Your current role is: " . ($userRole ?? 'unknown');
    setFlashMessage($message, 'error');
    header('Location: dashboard.php');
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'add_availability') {
        $dayOfWeek = sanitizeInput($_POST['day_of_week'] ?? '');
        $startTime = sanitizeInput($_POST['start_time'] ?? '');
        $endTime = sanitizeInput($_POST['end_time'] ?? '');
        $isRecurring = isset($_POST['is_recurring']) ? 1 : 0;
        
        // Validate inputs
        $errors = [];
        $validDays = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
        
        if (!in_array($dayOfWeek, $validDays)) {
            $errors[] = 'Invalid day of week';
        }
        
        if (empty($startTime) || empty($endTime)) {
            $errors[] = 'Start time and end time are required';
        }
        
        if (strtotime($startTime) >= strtotime($endTime)) {
            $errors[] = 'End time must be after start time';
        }
        
        if (empty($errors)) {
            // Insert availability into mentor_availability table
            $sql = "INSERT INTO mentor_availability (mentor_id, day_of_week, start_time, end_time, is_available, is_recurring, created_at) 
                    VALUES (:mentor_id, :day_of_week, :start_time, :end_time, 1, :is_recurring, NOW())";
            
            $result = executeQuery($sql, [
                'mentor_id' => $userId,
                'day_of_week' => $dayOfWeek,
                'start_time' => $startTime,
                'end_time' => $endTime,
                'is_recurring' => $isRecurring
            ]);
            
            if ($result) {
                setFlashMessage('Availability added successfully!', 'success');
            } else {
                setFlashMessage('Failed to add availability. Please try again.', 'error');
            }
        } else {
            setFlashMessage(implode(', ', $errors), 'error');
        }
        
        header('Location: availability.php');
        exit();
    }
    
    if ($action === 'delete_availability') {
        $availabilityId = (int)($_POST['availability_id'] ?? 0);
        
        $sql = "DELETE FROM mentor_availability WHERE id = :id AND mentor_id = :mentor_id";
        $result = executeQuery($sql, [
            'id' => $availabilityId,
            'mentor_id' => $userId
        ]);
        
        if ($result) {
            setFlashMessage('Availability removed successfully!', 'success');
        } else {
            setFlashMessage('Failed to remove availability.', 'error');
        }
        
        header('Location: availability.php');
        exit();
    }
}

// Fetch mentor's current availability
$sql = "SELECT id, day_of_week, start_time, end_time, is_recurring 
        FROM mentor_availability 
        WHERE mentor_id = :mentor_id AND is_available = 1
        ORDER BY FIELD(day_of_week, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'), start_time";

$availabilities = selectRecords($sql, ['mentor_id' => $userId]);

// Include header
include '../includes/header.php';
?>

<div class="container">
    <h1>Set Your Availability</h1>
    <p>Manage your available time slots for mentoring sessions. Mentees can book sessions during these times.</p>
    
    <?php displayFlashMessage(); ?>
    
    <!-- Add Availability Form -->
    <div class="dashboard-card">
        <div class="card-title">Add New Availability</div>
        <div class="card-content">
            <form method="POST" action="" class="availability-form">
                <input type="hidden" name="action" value="add_availability">
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="day_of_week">Day of Week</label>
                        <select name="day_of_week" id="day_of_week" required>
                            <option value="">Select Day</option>
                            <option value="Monday">Monday</option>
                            <option value="Tuesday">Tuesday</option>
                            <option value="Wednesday">Wednesday</option>
                            <option value="Thursday">Thursday</option>
                            <option value="Friday">Friday</option>
                            <option value="Saturday">Saturday</option>
                            <option value="Sunday">Sunday</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="start_time">Start Time</label>
                        <input type="time" name="start_time" id="start_time" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="end_time">End Time</label>
                        <input type="time" name="end_time" id="end_time" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label>
                        <input type="checkbox" name="is_recurring" value="1" checked>
                        Recurring weekly (repeat every week)
                    </label>
                </div>
                
                <button type="submit" class="btn btn-primary">Add Availability</button>
            </form>
        </div>
    </div>
    
    <!-- Current Availability -->
    <div class="dashboard-card">
        <div class="card-title">Your Current Availability</div>
        <div class="card-content">
            <?php if (empty($availabilities)): ?>
                <p class="text-muted">You haven't set any availability yet. Add your available time slots above.</p>
            <?php else: ?>
                <div class="availability-list">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Day</th>
                                <th>Time</th>
                                <th>Type</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($availabilities as $slot): ?>
                                <tr>
                                    <td><strong><?= htmlspecialchars($slot['day_of_week']) ?></strong></td>
                                    <td>
                                        <?= date('g:i A', strtotime($slot['start_time'])) ?> - 
                                        <?= date('g:i A', strtotime($slot['end_time'])) ?>
                                    </td>
                                    <td>
                                        <?php if ($slot['is_recurring']): ?>
                                            <span class="badge badge-success">Recurring</span>
                                        <?php else: ?>
                                            <span class="badge badge-info">One-time</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <form method="POST" action="" style="display: inline;" onsubmit="return confirm('Are you sure you want to remove this availability?');">
                                            <input type="hidden" name="action" value="delete_availability">
                                            <input type="hidden" name="availability_id" value="<?= $slot['id'] ?>">
                                            <button type="submit" class="btn btn-danger btn-sm">Remove</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <div class="action-buttons">
        <a href="dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
    </div>
</div>

<style>
.availability-form .form-row {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
    margin-bottom: 1rem;
}

.availability-list {
    overflow-x: auto;
}

.table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 1rem;
}

.table th,
.table td {
    padding: 12px;
    text-align: left;
    border-bottom: 1px solid var(--border-color);
}

.table th {
    background-color: var(--light-bg);
    font-weight: 600;
}

.table tbody tr:hover {
    background-color: var(--light-bg);
}

.badge {
    display: inline-block;
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 0.875rem;
    font-weight: 500;
}

.badge-success {
    background-color: #d4edda;
    color: #155724;
}

.badge-info {
    background-color: #d1ecf1;
    color: #0c5460;
}

.text-muted {
    color: #6c757d;
    font-style: italic;
}

.action-buttons {
    margin-top: 2rem;
    display: flex;
    gap: 1rem;
}

.btn-sm {
    padding: 0.375rem 0.75rem;
    font-size: 0.875rem;
}
</style>

<?php include '../includes/footer.php'; ?>
