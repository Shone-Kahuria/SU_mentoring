<?php
/**
 * Account Settings Page
 * 
 * Allows users to manage their account settings including:
 * - Email notifications
 * - Privacy preferences
 * - Account security
 * - Session management
 */

require_once '../includes/functions.php';
startSession();

// Require user to be logged in
requireLogin();

$pageTitle = 'Account Settings - SU Mentoring Platform';
$userId = getCurrentUserId();
$user = getCurrentUser();
$successMessage = '';
$errors = [];

// Handle settings update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid security token. Please try again.';
    } else {
        $setting_type = sanitizeInput($_POST['setting_type'] ?? '');
        
        switch ($setting_type) {
            case 'notifications':
                $emailNotifications = isset($_POST['email_notifications']) ? 1 : 0;
                $sessionNotifications = isset($_POST['session_notifications']) ? 1 : 0;
                
                $updateData = [
                    'email_notifications' => $emailNotifications,
                    'session_notifications' => $sessionNotifications,
                    'updated_at' => date('Y-m-d H:i:s')
                ];
                
                if (updateRecord('users', $updateData, 'id = :id', ['id' => $userId])) {
                    $successMessage = 'Notification preferences updated successfully.';
                    logActivity('settings_updated', ['setting_type' => 'notifications']);
                } else {
                    $errors[] = 'Failed to update notification preferences.';
                }
                break;
                
            case 'privacy':
                $profileVisibility = sanitizeInput($_POST['profile_visibility'] ?? 'private');
                
                if (!in_array($profileVisibility, ['public', 'private', 'mentors_only'])) {
                    $errors[] = 'Invalid privacy setting.';
                } else {
                    $updateData = [
                        'profile_visibility' => $profileVisibility,
                        'updated_at' => date('Y-m-d H:i:s')
                    ];
                    
                    if (updateRecord('users', $updateData, 'id = :id', ['id' => $userId])) {
                        $successMessage = 'Privacy preferences updated successfully.';
                        logActivity('settings_updated', ['setting_type' => 'privacy']);
                    } else {
                        $errors[] = 'Failed to update privacy preferences.';
                    }
                }
                break;
                
            case 'session':
                $action = sanitizeInput($_POST['session_action'] ?? '');
                
                if ($action === 'logout_all') {
                    // Sign out from all other sessions
                    $sql = "DELETE FROM user_sessions WHERE user_id = :user_id AND session_id != :session_id";
                    executeQuery($sql, [
                        'user_id' => $userId,
                        'session_id' => session_id()
                    ]);
                    
                    $successMessage = 'All other sessions have been signed out.';
                    logActivity('security_updated', ['action' => 'logout_all']);
                }
                break;
                
            default:
                $errors[] = 'Invalid setting type.';
        }
    }
}

// Fetch current user settings
$userSettings = selectRecord('SELECT * FROM users WHERE id = :id', ['id' => $userId]);

include '../includes/header.php';
?>

<div class="container">
    <div class="row mt-4 mb-5">
        <div class="col-md-3">
            <div class="list-group">
                <a href="#notifications" class="list-group-item list-group-item-action" data-bs-toggle="list">
                    <i class="fas fa-bell"></i> Notifications
                </a>
                <a href="#privacy" class="list-group-item list-group-item-action" data-bs-toggle="list">
                    <i class="fas fa-lock"></i> Privacy
                </a>
                <a href="#security" class="list-group-item list-group-item-action" data-bs-toggle="list">
                    <i class="fas fa-shield-alt"></i> Security
                </a>
                <a href="change-password.php" class="list-group-item list-group-item-action">
                    <i class="fas fa-key"></i> Change Password
                </a>
            </div>
        </div>

        <div class="col-md-9">
            <h2 class="mb-4">Account Settings</h2>

            <?php if (!empty($successMessage)): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($successMessage); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle"></i>
                    <ul class="mb-0">
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo htmlspecialchars($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <!-- Notifications Settings -->
            <div class="tab-pane fade show active" id="notifications">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-bell"></i> Notification Preferences</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(generateCSRFToken()); ?>">
                            <input type="hidden" name="setting_type" value="notifications">

                            <div class="mb-3 form-check">
                                <input type="checkbox" class="form-check-input" id="emailNotifications" 
                                       name="email_notifications" 
                                       <?php echo ($userSettings['email_notifications'] ?? 0) ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="emailNotifications">
                                    Receive email notifications for mentorship requests and messages
                                </label>
                            </div>

                            <div class="mb-3 form-check">
                                <input type="checkbox" class="form-check-input" id="sessionNotifications" 
                                       name="session_notifications" 
                                       <?php echo ($userSettings['session_notifications'] ?? 0) ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="sessionNotifications">
                                    Receive notifications for scheduled sessions
                                </label>
                            </div>

                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Save Preferences
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Privacy Settings -->
            <div class="tab-pane fade" id="privacy">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-lock"></i> Privacy Settings</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(generateCSRFToken()); ?>">
                            <input type="hidden" name="setting_type" value="privacy">

                            <div class="mb-3">
                                <label for="profileVisibility" class="form-label">Profile Visibility</label>
                                <select class="form-select" id="profileVisibility" name="profile_visibility">
                                    <option value="private" <?php echo ($userSettings['profile_visibility'] ?? 'private') === 'private' ? 'selected' : ''; ?>>
                                        Private (Only visible to mentor/mentee)
                                    </option>
                                    <option value="mentors_only" <?php echo ($userSettings['profile_visibility'] ?? 'private') === 'mentors_only' ? 'selected' : ''; ?>>
                                        Mentors Only (Visible to mentors and connections)
                                    </option>
                                    <option value="public" <?php echo ($userSettings['profile_visibility'] ?? 'private') === 'public' ? 'selected' : ''; ?>>
                                        Public (Visible to all platform users)
                                    </option>
                                </select>
                                <small class="text-muted d-block mt-2">
                                    Controls who can see your profile information
                                </small>
                            </div>

                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Save Privacy Settings
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Security Settings -->
            <div class="tab-pane fade" id="security">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-shield-alt"></i> Security Settings</h5>
                    </div>
                    <div class="card-body">
                        <h6 class="mb-3">Active Sessions</h6>
                        <p class="text-muted">Manage your login sessions across different devices</p>

                        <div class="row mb-3">
                            <div class="col-md-8">
                                <p class="mb-0">
                                    <strong>Current Session</strong><br>
                                    <small class="text-muted">This device and browser</small>
                                </p>
                            </div>
                            <div class="col-md-4 text-end">
                                <span class="badge bg-success">Active</span>
                            </div>
                        </div>

                        <hr>

                        <h6 class="mb-3">Sign Out All Other Sessions</h6>
                        <p class="text-muted">This will end your login on all other devices</p>

                        <form method="POST">
                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(generateCSRFToken()); ?>">
                            <input type="hidden" name="setting_type" value="session">
                            <input type="hidden" name="session_action" value="logout_all">

                            <button type="submit" class="btn btn-warning">
                                <i class="fas fa-sign-out-alt"></i> Sign Out All Other Sessions
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Links -->
            <div class="mt-4">
                <a href="profile.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Profile
                </a>
            </div>
        </div>
    </div>
</div>

<?php
include '../includes/footer.php';
?>
