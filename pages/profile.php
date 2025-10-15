<?php
/**
 * Profile Page for Mentoring Website
 * Developer: Shone
 * 
 * Features:
 * - Display user information (name, email, role)
 * - For mentors: Show list of assigned mentees
 * - For mentees: Show assigned mentor
 * - Profile editing functionality
 * - PHP data fetching from database
 */

require_once '../includes/functions.php';
startSession();

// Require user to be logged in
requireLogin();

$user = getCurrentUser();
$userRole = getCurrentUserRole();
$userId = getCurrentUserId();

$pageTitle = 'Profile - MentorConnect';
$errors = [];
$successMessage = '';

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    // Verify CSRF token
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid security token. Please try again.';
    } else {
        // Sanitize input
        $fullName = sanitizeInput($_POST['full_name'] ?? '');
        $email = sanitizeInput($_POST['email'] ?? '');
        $bio = sanitizeInput($_POST['bio'] ?? '');
        $skills = sanitizeInput($_POST['skills'] ?? '');
        $experienceLevel = sanitizeInput($_POST['experience_level'] ?? '');
        $gender = strtolower(sanitizeInput($_POST['gender'] ?? ''));
        
        // Validation
        if (empty($fullName)) {
            $errors[] = 'Full name is required.';
        } elseif (strlen($fullName) < 2) {
            $errors[] = 'Full name must be at least 2 characters long.';
        }
        
        if (empty($email)) {
            $errors[] = 'Email is required.';
        } elseif (!validateEmail($email)) {
            $errors[] = 'Please enter a valid email address.';
        } elseif (emailExists($email, $userId)) {
            $errors[] = 'This email is already in use by another account.';
        }
        
        if (!empty($experienceLevel) && !in_array($experienceLevel, ['beginner', 'intermediate', 'advanced'])) {
            $errors[] = 'Invalid experience level selected.';
        }

        if (!isValidGender($gender)) {
            $errors[] = 'Please select a valid gender option.';
        }
        
        // Update profile if no errors
        if (empty($errors)) {
            try {
                $updateData = [
                    'full_name' => $fullName,
                    'email' => $email,
                    'gender' => $gender,
                    'bio' => $bio,
                    'skills' => $skills,
                    'experience_level' => $experienceLevel ?: null,
                    'updated_at' => date('Y-m-d H:i:s')
                ];
                
                $success = updateRecord('users', $updateData, 'id = :id', ['id' => $userId]);
                
                if ($success) {
                    // Update session data
                    $_SESSION['user_name'] = $fullName;
                    $_SESSION['user_email'] = $email;
                    $_SESSION['user_gender'] = $gender;
                    
                    // Log activity
                    logActivity('profile_updated', [
                        'user_id' => $userId,
                        'fields_updated' => array_keys($updateData)
                    ]);
                    
                    $successMessage = 'Profile updated successfully!';
                    
                    // Refresh user data
                    $user = getCurrentUser();
                } else {
                    $errors[] = 'Failed to update profile. Please try again.';
                }
            } catch (Exception $e) {
                error_log('Profile update error: ' . $e->getMessage());
                $errors[] = 'An error occurred while updating your profile.';
            }
        }
    }
}

// Get mentor/mentee relationships
$relationships = [];
if ($userRole === 'mentor') {
    // Get mentees for this mentor
    $sql = "SELECT m.*, u.full_name, u.email, u.bio, u.experience_level, u.created_at as user_created
            FROM mentorships m 
            JOIN users u ON m.mentee_id = u.id 
            WHERE m.mentor_id = :user_id AND m.status IN ('active', 'pending')
            ORDER BY m.status ASC, m.created_at DESC";
} else {
    // Get mentors for this mentee
    $sql = "SELECT m.*, u.full_name, u.email, u.bio, u.skills, u.experience_level, u.created_at as user_created
            FROM mentorships m 
            JOIN users u ON m.mentor_id = u.id 
            WHERE m.mentee_id = :user_id AND m.status IN ('active', 'pending')
            ORDER BY m.status ASC, m.created_at DESC";
}

$relationships = selectRecords($sql, ['user_id' => $userId]) ?: [];

// Get user statistics using shared helper
$stats = getUserStatistics($userId, $userRole);

// Generate CSRF token
$csrfToken = generateCSRFToken();
?>

<?php include '../includes/header.php'; ?>

<div class="container">
    <!-- Profile Header -->
    <div class="dashboard-header">
        <div class="dashboard-title">
            <?php echo htmlspecialchars($user['full_name']); ?>'s Profile
        </div>
        <div class="dashboard-subtitle">
            <?php echo ucfirst($userRole); ?> since <?php echo formatDate($user['created_at'], 'F Y'); ?>
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

    <div class="dashboard-grid">
        <!-- Profile Information -->
        <div class="dashboard-card">
            <div class="card-title">Profile Information</div>
            <div class="card-content">
                <form method="POST" id="profileForm">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                    <input type="hidden" name="update_profile" value="1">
                    
                    <div class="form-group">
                        <label for="full_name">Full Name</label>
                        <input 
                            type="text" 
                            id="full_name" 
                            name="full_name" 
                            class="form-control" 
                            value="<?php echo htmlspecialchars($user['full_name']); ?>"
                            required
                            maxlength="255"
                        >
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input 
                            type="email" 
                            id="email" 
                            name="email" 
                            class="form-control" 
                            value="<?php echo htmlspecialchars($user['email']); ?>"
                            required
                            maxlength="255"
                        >
                    </div>

                    <div class="form-group">
                        <label for="gender">Gender</label>
                        <select id="gender" name="gender" class="form-control" required>
                            <option value="">Select your gender</option>
                            <?php foreach (getAllowedGenders() as $genderOption): ?>
                                <option value="<?php echo $genderOption; ?>" <?php echo ($user['gender'] ?? '') === $genderOption ? 'selected' : ''; ?>>
                                    <?php echo ucfirst($genderOption); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="bio">Bio</label>
                        <textarea 
                            id="bio" 
                            name="bio" 
                            class="form-control" 
                            rows="4"
                            placeholder="Tell others about yourself, your expertise, and your goals..."
                            maxlength="1000"
                        ><?php echo htmlspecialchars($user['bio'] ?? ''); ?></textarea>
                        <small style="color: #666;">
                            <span id="bioCount">0</span>/1000 characters
                        </small>
                    </div>
                    
                    <div class="form-group">
                        <label for="skills">
                            <?php echo $userRole === 'mentor' ? 'Skills & Expertise' : 'Interests & Learning Goals'; ?>
                        </label>
                        <textarea 
                            id="skills" 
                            name="skills" 
                            class="form-control" 
                            rows="3"
                            placeholder="<?php echo $userRole === 'mentor' ? 'List your professional skills and areas of expertise...' : 'Describe what you want to learn and your interests...'; ?>"
                            maxlength="500"
                        ><?php echo htmlspecialchars($user['skills'] ?? ''); ?></textarea>
                        <small style="color: #666;">
                            <span id="skillsCount">0</span>/500 characters
                        </small>
                    </div>
                    
                    <div class="form-group">
                        <label for="experience_level">Experience Level</label>
                        <select id="experience_level" name="experience_level" class="form-control">
                            <option value="">Select your level</option>
                            <option value="beginner" <?php echo ($user['experience_level'] ?? '') === 'beginner' ? 'selected' : ''; ?>>Beginner</option>
                            <option value="intermediate" <?php echo ($user['experience_level'] ?? '') === 'intermediate' ? 'selected' : ''; ?>>Intermediate</option>
                            <option value="advanced" <?php echo ($user['experience_level'] ?? '') === 'advanced' ? 'selected' : ''; ?>>Advanced</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Role</label>
                        <input 
                            type="text" 
                            class="form-control" 
                            value="<?php echo ucfirst($userRole); ?>"
                            readonly
                            style="background-color: #f8f9fa;"
                        >
                        <small style="color: #666;">Contact support to change your role</small>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Update Profile</button>
                    <button type="button" class="btn btn-secondary" onclick="resetForm()">Reset</button>
                </form>
            </div>
        </div>

        <!-- Profile Statistics -->
        <div class="dashboard-card">
            <div class="card-title">Profile Statistics</div>
            <div class="card-content">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div style="text-align: center; padding: 1rem; background: #f8f9fa; border-radius: 8px;">
                        <div style="font-size: 2rem; font-weight: bold; color: var(--primary-blue);">
                            <?php echo $stats['active_mentorships']; ?>
                        </div>
                        <div>Active Mentorships</div>
                    </div>
                    <div style="text-align: center; padding: 1rem; background: #f8f9fa; border-radius: 8px;">
                        <div style="font-size: 2rem; font-weight: bold; color: var(--primary-red);">
                            <?php echo $stats['completed_sessions']; ?>
                        </div>
                        <div>Sessions Completed</div>
                    </div>
                    <div style="text-align: center; padding: 1rem; background: #f8f9fa; border-radius: 8px;">
                        <div style="font-size: 2rem; font-weight: bold; color: var(--success-green);">
                            <?php echo $stats['completed_mentorships']; ?>
                        </div>
                        <div>Completed Mentorships</div>
                    </div>
                    <div style="text-align: center; padding: 1rem; background: #f8f9fa; border-radius: 8px;">
                        <div style="font-size: 2rem; font-weight: bold; color: var(--warning-yellow);">
                            <?php echo $stats['upcoming_sessions']; ?>
                        </div>
                        <div>Upcoming Sessions</div>
                    </div>
                </div>
                
                <div class="mt-2">
                    <h4>Account Details</h4>
                    <p><strong>Gender:</strong> <?php echo $user['gender'] ? ucfirst($user['gender']) : 'Not set'; ?></p>
                    <p><strong>Member since:</strong> <?php echo formatDate($user['created_at'], 'F j, Y'); ?></p>
                    <p><strong>Last login:</strong> <?php echo $user['last_login'] ? formatDate($user['last_login']) : 'Never'; ?></p>
                    <p><strong>Email verified:</strong> 
                        <span style="color: <?php echo $user['email_verified'] ? 'var(--success-green)' : 'var(--error-red)'; ?>">
                            <?php echo $user['email_verified'] ? 'Yes' : 'No'; ?>
                        </span>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Relationships Section -->
    <div class="dashboard-card">
        <div class="card-title">
            <?php echo $userRole === 'mentor' ? 'Your Mentees' : 'Your Mentors'; ?>
        </div>
        <div class="card-content">
            <?php if (empty($relationships)): ?>
                <p>
                    <?php echo $userRole === 'mentor' 
                        ? 'You don\'t have any mentees yet. Mentees can request mentorship through your profile.' 
                        : 'You don\'t have any mentors yet.'; ?>
                </p>
                <?php if ($userRole === 'mentee'): ?>
                    <a href="find-mentor.php" class="btn btn-primary">Find a Mentor</a>
                <?php endif; ?>
            <?php else: ?>
                <div style="display: grid; gap: 1.5rem;">
                    <?php foreach ($relationships as $relationship): ?>
                        <div style="border: 1px solid var(--border-color); padding: 1.5rem; border-radius: 8px; background: #f8f9fa;">
                            <div style="display: flex; justify-content: between; align-items: start;">
                                <div style="flex: 1;">
                                    <h4 style="margin-bottom: 0.5rem; color: var(--primary-blue);">
                                        <?php echo htmlspecialchars($relationship['full_name']); ?>
                                    </h4>
                                    <p style="margin: 0.25rem 0; color: var(--text-light);">
                                        <?php echo htmlspecialchars($relationship['email']); ?>
                                    </p>
                                    
                                    <?php if (!empty($relationship['bio'])): ?>
                                        <p style="margin: 0.5rem 0;">
                                            <strong>Bio:</strong> 
                                            <?php echo htmlspecialchars(substr($relationship['bio'], 0, 150)) . (strlen($relationship['bio']) > 150 ? '...' : ''); ?>
                                        </p>
                                    <?php endif; ?>
                                    
                                    <?php if (!empty($relationship['skills'])): ?>
                                        <p style="margin: 0.5rem 0;">
                                            <strong><?php echo $userRole === 'mentor' ? 'Interests:' : 'Skills:'; ?></strong> 
                                            <?php echo htmlspecialchars($relationship['skills']); ?>
                                        </p>
                                    <?php endif; ?>
                                    
                                    <?php if (!empty($relationship['experience_level'])): ?>
                                        <p style="margin: 0.5rem 0;">
                                            <strong>Experience Level:</strong> 
                                            <span style="text-transform: capitalize;"><?php echo htmlspecialchars($relationship['experience_level']); ?></span>
                                        </p>
                                    <?php endif; ?>
                                </div>
                                
                                <div style="margin-left: 1rem;">
                                    <div style="padding: 0.5rem 1rem; border-radius: 20px; font-size: 0.875rem; font-weight: bold; text-align: center; color: white; 
                                                background: <?php echo $relationship['status'] === 'active' ? 'var(--success-green)' : 'var(--warning-yellow)'; ?>">
                                        <?php echo ucfirst($relationship['status']); ?>
                                    </div>
                                </div>
                            </div>
                            
                            <div style="margin-top: 1rem; padding-top: 1rem; border-top: 1px solid var(--border-color);">
                                <small style="color: var(--text-light);">
                                    <?php echo $userRole === 'mentor' ? 'Mentee' : 'Mentor'; ?> since <?php echo formatDate($relationship['user_created'], 'F Y'); ?> • 
                                    Relationship started <?php echo formatDate($relationship['created_at'], 'F j, Y'); ?>
                                </small>
                                
                                <div style="margin-top: 0.5rem;">
                                    <?php if ($relationship['status'] === 'active'): ?>
                                        <a href="messages.php?mentorship=<?php echo $relationship['id']; ?>" class="btn btn-primary" style="font-size: 0.875rem; padding: 6px 12px;">Message</a>
                                        <a href="booking.php?mentorship=<?php echo $relationship['id']; ?>" class="btn btn-secondary" style="font-size: 0.875rem; padding: 6px 12px;">Schedule Session</a>
                                    <?php elseif ($relationship['status'] === 'pending' && $userRole === 'mentor'): ?>
                                        <button class="btn btn-primary" style="font-size: 0.875rem; padding: 6px 12px;" onclick="respondToRequest(<?php echo $relationship['id']; ?>, 'accept')">Accept</button>
                                        <button class="btn btn-secondary" style="font-size: 0.875rem; padding: 6px 12px;" onclick="respondToRequest(<?php echo $relationship['id']; ?>, 'decline')">Decline</button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Account Actions -->
    <div class="dashboard-card">
        <div class="card-title">Account Actions</div>
        <div class="card-content">
            <div style="display: flex; flex-wrap: wrap; gap: 1rem;">
                <a href="dashboard.php" class="btn btn-primary">Back to Dashboard</a>
                <a href="change-password.php" class="btn btn-secondary">Change Password</a>
                <?php if ($userRole === 'mentee'): ?>
                    <a href="find-mentor.php" class="btn btn-primary">Find More Mentors</a>
                <?php endif; ?>
                <a href="settings.php" class="btn btn-secondary">Account Settings</a>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Character counters
    const bioField = document.getElementById('bio');
    const skillsField = document.getElementById('skills');
    const bioCounter = document.getElementById('bioCount');
    const skillsCounter = document.getElementById('skillsCount');
    const genderField = document.getElementById('gender');
    
    function updateCounter(field, counter) {
        counter.textContent = field.value.length;
    }
    
    // Initialize counters
    updateCounter(bioField, bioCounter);
    updateCounter(skillsField, skillsCounter);
    
    // Update counters on input
    bioField.addEventListener('input', () => updateCounter(bioField, bioCounter));
    skillsField.addEventListener('input', () => updateCounter(skillsField, skillsCounter));
    
    // Form validation
    document.getElementById('profileForm').addEventListener('submit', function(e) {
        const fullName = document.getElementById('full_name').value.trim();
        const email = document.getElementById('email').value.trim();
        
        if (fullName.length < 2) {
            e.preventDefault();
            showAlert('Full name must be at least 2 characters long.', 'error');
            return;
        }
        
        if (!email || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
            e.preventDefault();
            showAlert('Please enter a valid email address.', 'error');
            return;
        }

        if (!genderField.value || !['male', 'female'].includes(genderField.value)) {
            e.preventDefault();
            showAlert('Please select a valid gender option.', 'error');
            return;
        }
    });
});

function resetForm() {
    if (confirm('Are you sure you want to reset all changes?')) {
        document.getElementById('profileForm').reset();
        location.reload();
    }
}

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
</script>

<?php include '../includes/footer.php'; ?>