<?php
session_start();

// Security: Ensure logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: admin_login.php");
    exit;
}

require_once 'db.php';

// Generate CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$msg = '';
$msg_type = 'success';

// ─────────────────────────────────────────────────────────────
// HELPER: Secure file upload handler
// ─────────────────────────────────────────────────────────────
function handle_upload($file_input, $upload_dir) {
    if (!isset($file_input) || $file_input['error'] !== UPLOAD_ERR_OK) return null;

    $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $max_size = 5 * 1024 * 1024; // 5MB

    // Validate MIME type properly
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($file_input['tmp_name']);
    if (!in_array($mime, $allowed_types)) return null;
    if ($file_input['size'] > $max_size) return null;

    $ext = pathinfo($file_input['name'], PATHINFO_EXTENSION);
    $safe_name = bin2hex(random_bytes(8)) . '.' . strtolower($ext);
    $dest = $upload_dir . $safe_name;

    if (move_uploaded_file($file_input['tmp_name'], $dest)) {
        return 'uploads/' . basename($upload_dir) . '/' . $safe_name;
    }
    return null;
}

// ─────────────────────────────────────────────────────────────
// CSRF Verification for all POST requests
// ─────────────────────────────────────────────────────────────
function verify_csrf() {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("Security check failed. Please go back and try again.");
    }
}

// ─────────────────────────────────────────────────────────────
// Handle: Profile Update
// ─────────────────────────────────────────────────────────────
if (isset($_POST['update_profile'])) {
    verify_csrf();

    $full_name   = trim($_POST['full_name']);
    $short_name  = trim($_POST['short_name']);
    $roles       = trim($_POST['roles']);
    $hero_desc   = trim($_POST['hero_desc']);
    $about_text  = trim($_POST['about_text']);
    $email       = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);

    // Get current image first
    $current = $conn->query("SELECT profile_image FROM profile LIMIT 1")->fetch_assoc();
    $profile_image = $current['profile_image'];

    // If a new photo is uploaded, use it
    if (!empty($_FILES['profile_photo']['name'])) {
        $uploaded = handle_upload($_FILES['profile_photo'], __DIR__ . '/uploads/profile/');
        if ($uploaded) {
            // Delete old uploaded file if it's a local upload
            if (strpos($profile_image, 'uploads/') === 0 && file_exists(__DIR__ . '/' . $profile_image)) {
                unlink(__DIR__ . '/' . $profile_image);
            }
            $profile_image = $uploaded;
        } else {
            $msg = "Photo upload failed. Use JPG, PNG, WEBP or GIF under 5MB.";
            $msg_type = 'error';
        }
    }

    if (!$msg) {
        $stmt = $conn->prepare("UPDATE profile SET full_name=?, short_name=?, roles=?, hero_desc=?, about_text=?, email=?, profile_image=? WHERE id=1");
        $stmt->bind_param("sssssss", $full_name, $short_name, $roles, $hero_desc, $about_text, $email, $profile_image);
        if ($stmt->execute()) {
            $msg = "Profile updated successfully on the live site!";
        } else {
            $msg = "Database error. Please try again.";
            $msg_type = 'error';
        }
    }
}

// ─────────────────────────────────────────────────────────────
// Handle: Add Project
// ─────────────────────────────────────────────────────────────
if (isset($_POST['add_project'])) {
    verify_csrf();

    $title       = trim($_POST['title']);
    $description = trim($_POST['description']);
    $tags        = trim($_POST['tags']);
    $github_link = filter_var(trim($_POST['github_link']), FILTER_SANITIZE_URL);
    $live_link   = filter_var(trim($_POST['live_link']), FILTER_SANITIZE_URL);
    $image       = '';

    // Upload image if provided
    if (!empty($_FILES['project_photo']['name'])) {
        $uploaded = handle_upload($_FILES['project_photo'], __DIR__ . '/uploads/projects/');
        if ($uploaded) {
            $image = $uploaded;
        } else {
            $msg = "Project image upload failed. Use JPG, PNG, WEBP or GIF under 5MB.";
            $msg_type = 'error';
        }
    } elseif (!empty($_POST['image_url'])) {
        $image = filter_var(trim($_POST['image_url']), FILTER_SANITIZE_URL);
    }

    if (!$msg) {
        $stmt = $conn->prepare("INSERT INTO projects (title, description, image, tags, github_link, live_link) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssss", $title, $description, $image, $tags, $github_link, $live_link);
        if ($stmt->execute()) {
            $msg = "Project published to your portfolio!";
        } else {
            $msg = "Database error. Please try again.";
            $msg_type = 'error';
        }
    }
}

// ─────────────────────────────────────────────────────────────
// Handle: Edit Project
// ─────────────────────────────────────────────────────────────
if (isset($_POST['edit_project'])) {
    verify_csrf();
    $id          = (int)$_POST['project_id'];
    $title       = trim($_POST['title']);
    $description = trim($_POST['description']);
    $tags        = trim($_POST['tags']);
    $github_link = filter_var(trim($_POST['github_link']), FILTER_SANITIZE_URL);
    $live_link   = filter_var(trim($_POST['live_link']), FILTER_SANITIZE_URL);

    $current_proj = $conn->query("SELECT image FROM projects WHERE id=$id")->fetch_assoc();
    $image = $current_proj['image'];

    if (!empty($_FILES['project_photo']['name'])) {
        $uploaded = handle_upload($_FILES['project_photo'], __DIR__ . '/uploads/projects/');
        if ($uploaded) {
            if (strpos($image, 'uploads/') === 0 && file_exists(__DIR__ . '/' . $image)) unlink(__DIR__ . '/' . $image);
            $image = $uploaded;
        }
    } elseif (!empty($_POST['image_url'])) {
        $image = filter_var(trim($_POST['image_url']), FILTER_SANITIZE_URL);
    }

    $stmt = $conn->prepare("UPDATE projects SET title=?, description=?, image=?, tags=?, github_link=?, live_link=? WHERE id=?");
    $stmt->bind_param("ssssssi", $title, $description, $image, $tags, $github_link, $live_link, $id);
    if ($stmt->execute()) {
        $msg = "Project updated!";
    }
}

// ─────────────────────────────────────────────────────────────
// Handle: Delete Project (POST only for CSRF safety)
// ─────────────────────────────────────────────────────────────
if (isset($_POST['delete_project'])) {
    verify_csrf();
    $id = (int)$_POST['project_id'];
    $row = $conn->query("SELECT image FROM projects WHERE id=$id")->fetch_assoc();
    if ($row && strpos($row['image'], 'uploads/') === 0 && file_exists(__DIR__ . '/' . $row['image'])) {
        unlink(__DIR__ . '/' . $row['image']);
    }
    $conn->query("DELETE FROM projects WHERE id=$id");
    header("Location: admin_dashboard.php?deleted=1");
    exit;
}

// ─────────────────────────────────────────────────────────────
// Handle: Change Password
// ─────────────────────────────────────────────────────────────
if (isset($_POST['change_password'])) {
    verify_csrf();
    $current  = $_POST['current_password'];
    $new_pass = $_POST['new_password'];
    $confirm  = $_POST['confirm_password'];

    $row = $conn->query("SELECT password_hash FROM admin_users WHERE id=" . (int)$_SESSION['admin_id'])->fetch_assoc();

    if (!password_verify($current, $row['password_hash'])) {
        $msg = "Current password is incorrect."; $msg_type = 'error';
    } elseif (strlen($new_pass) < 8) {
        $msg = "New password must be at least 8 characters."; $msg_type = 'error';
    } elseif ($new_pass !== $confirm) {
        $msg = "New passwords do not match."; $msg_type = 'error';
    } else {
        $hash = password_hash($new_pass, PASSWORD_DEFAULT);
        $conn->query("UPDATE admin_users SET password_hash='$hash' WHERE id=" . (int)$_SESSION['admin_id']);
        $msg = "Password changed successfully!";
    }
}

if (isset($_GET['deleted'])) $msg = "Project deleted.";

// ─────────────────────────────────────────────────────────────
// Fetch data for display
// ─────────────────────────────────────────────────────────────
$profile  = $conn->query("SELECT * FROM profile LIMIT 1")->fetch_assoc();
$projects = $conn->query("SELECT * FROM projects ORDER BY id DESC");

// If ?edit=ID, load that project for editing
$edit_project = null;
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $edit_id = (int)$_GET['edit'];
    $edit_project = $conn->query("SELECT * FROM projects WHERE id=$edit_id")->fetch_assoc();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard · Portfolio CMS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background: #07070f; }
        .sidebar { background: rgba(255,255,255,0.02); border-right: 1px solid rgba(255,255,255,0.06); }
        .card { background: rgba(255,255,255,0.025); border: 1px solid rgba(255,255,255,0.07); }
        .input-field { background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.08); color: #fff; }
        .input-field:focus { outline: none; border-color: rgba(255,255,255,0.25); }
        .nav-item { color: #71717a; transition: all 0.2s; border-radius: 8px; }
        .nav-item:hover { background: rgba(255,255,255,0.05); color: #fff; }
        .nav-item.active { background: rgba(255,255,255,0.07); color: #fff; }
        .upload-zone { border: 2px dashed rgba(255,255,255,0.1); transition: all 0.3s; }
        .upload-zone:hover, .upload-zone.dragover { border-color: rgba(255,255,255,0.4); background: rgba(255,255,255,0.03); }
        .tag-badge { background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); }
        input:-webkit-autofill { -webkit-box-shadow: 0 0 0 30px #0f0f1a inset !important; -webkit-text-fill-color: #fff !important; }
        ::-webkit-scrollbar { width: 4px; } ::-webkit-scrollbar-track { background: transparent; } ::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.1); border-radius: 2px; }
    </style>
</head>
<body class="text-zinc-300 min-h-screen flex">

    <!-- Sidebar -->
    <aside class="sidebar w-60 fixed h-full z-20 flex flex-col p-5">
        <div class="mb-8 px-2">
            <a href="index.php" target="_blank" class="text-lg font-semibold tracking-[0.15em] uppercase text-white hover:text-zinc-300 transition-colors">Jonathan.</a>
            <p class="text-zinc-600 text-xs mt-1">CMS Dashboard</p>
        </div>

        <nav class="space-y-1 flex-grow">
            <a href="#profile" class="nav-item active flex items-center gap-3 px-3 py-2.5 text-sm font-medium">
                <i class="fas fa-user w-4 text-center"></i> Profile
            </a>
            <a href="#projects" class="nav-item flex items-center gap-3 px-3 py-2.5 text-sm font-medium">
                <i class="fas fa-briefcase w-4 text-center"></i> Projects
            </a>
            <a href="#security" class="nav-item flex items-center gap-3 px-3 py-2.5 text-sm font-medium">
                <i class="fas fa-shield-alt w-4 text-center"></i> Security
            </a>
            <div class="pt-4 border-t border-white/5 mt-4">
                <a href="index.php" target="_blank" class="nav-item flex items-center gap-3 px-3 py-2.5 text-sm">
                    <i class="fas fa-external-link-alt w-4 text-center"></i> View Live Site
                </a>
            </div>
        </nav>

        <div class="pt-4 border-t border-white/5">
            <a href="logout.php" class="nav-item flex items-center gap-3 px-3 py-2.5 text-sm text-red-400 hover:text-red-300 hover:bg-red-500/10">
                <i class="fas fa-sign-out-alt w-4 text-center"></i> Logout
            </a>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="flex-grow ml-60 p-8 min-h-screen">
        <div class="max-w-4xl mx-auto">

            <!-- Header -->
            <div class="mb-10">
                <h1 class="text-2xl font-semibold text-white">Welcome back, <?php echo htmlspecialchars($profile['short_name']); ?></h1>
                <p class="text-zinc-500 text-sm mt-1">Manage your portfolio content below.</p>
            </div>

            <!-- Notification -->
            <?php if($msg): ?>
            <div class="<?php echo $msg_type === 'error' ? 'border-red-500/30 bg-red-500/10 text-red-400' : 'border-green-500/30 bg-green-500/10 text-green-400'; ?> border rounded-xl p-4 mb-8 text-sm flex items-center gap-3">
                <i class="fas <?php echo $msg_type === 'error' ? 'fa-exclamation-circle' : 'fa-check-circle'; ?>"></i>
                <?php echo htmlspecialchars($msg); ?>
            </div>
            <?php endif; ?>


            <!-- ═══════════════════════════════════ PROFILE SECTION -->
            <section id="profile" class="card rounded-2xl p-8 mb-8">
                <h2 class="text-base font-semibold text-white mb-6 flex items-center gap-3">
                    <span class="w-7 h-7 rounded-lg bg-blue-500/20 text-blue-400 flex items-center justify-center text-xs"><i class="fas fa-user"></i></span>
                    Profile Settings
                </h2>

                <form method="POST" enctype="multipart/form-data" class="space-y-6">
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

                    <!-- Profile Photo Upload -->
                    <div>
                        <label class="block text-xs text-zinc-400 mb-3 tracking-widest uppercase">Profile Photo</label>
                        <div class="flex items-center gap-6">
                            <img src="<?php echo htmlspecialchars($profile['profile_image']); ?>" alt="Profile" class="w-20 h-20 rounded-xl object-cover border border-white/10" id="profile-preview">
                            <div class="upload-zone rounded-xl p-5 flex-grow text-center cursor-pointer relative" onclick="document.getElementById('profile_photo').click()">
                                <i class="fas fa-cloud-upload-alt text-zinc-500 text-xl mb-2 block"></i>
                                <p class="text-zinc-400 text-sm">Click to upload new photo</p>
                                <p class="text-zinc-600 text-xs mt-1">JPG, PNG, WEBP or GIF · Max 5MB</p>
                                <input type="file" id="profile_photo" name="profile_photo" accept="image/*" class="absolute inset-0 opacity-0 cursor-pointer w-full h-full">
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div>
                            <label class="block text-xs text-zinc-400 mb-2 tracking-widest uppercase">Full Name</label>
                            <input type="text" name="full_name" value="<?php echo htmlspecialchars($profile['full_name']); ?>" class="input-field w-full rounded-lg px-4 py-3 text-sm">
                        </div>
                        <div>
                            <label class="block text-xs text-zinc-400 mb-2 tracking-widest uppercase">Short Name (Navigation)</label>
                            <input type="text" name="short_name" value="<?php echo htmlspecialchars($profile['short_name']); ?>" class="input-field w-full rounded-lg px-4 py-3 text-sm">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs text-zinc-400 mb-2 tracking-widest uppercase">Roles (comma separated for typing effect)</label>
                        <input type="text" name="roles" value="<?php echo htmlspecialchars($profile['roles']); ?>" class="input-field w-full rounded-lg px-4 py-3 text-sm" placeholder="Software Engineer, Developer, Creator">
                    </div>

                    <div>
                        <label class="block text-xs text-zinc-400 mb-2 tracking-widest uppercase">Contact Email</label>
                        <input type="email" name="email" value="<?php echo htmlspecialchars($profile['email']); ?>" class="input-field w-full rounded-lg px-4 py-3 text-sm">
                    </div>

                    <div>
                        <label class="block text-xs text-zinc-400 mb-2 tracking-widest uppercase">Hero Description (shown on homepage)</label>
                        <textarea name="hero_desc" rows="3" class="input-field w-full rounded-lg px-4 py-3 text-sm resize-none"><?php echo htmlspecialchars($profile['hero_desc']); ?></textarea>
                    </div>

                    <div>
                        <label class="block text-xs text-zinc-400 mb-2 tracking-widest uppercase">About Me Text (use | to separate paragraphs)</label>
                        <textarea name="about_text" rows="5" class="input-field w-full rounded-lg px-4 py-3 text-sm resize-none font-mono text-xs"><?php echo htmlspecialchars($profile['about_text']); ?></textarea>
                        <p class="text-zinc-600 text-xs mt-1">Tip: Separate paragraphs with <code class="text-zinc-400">|</code></p>
                    </div>

                    <button type="submit" name="update_profile" class="bg-white text-black font-semibold px-8 py-3 rounded-lg text-sm hover:bg-zinc-200 transition-colors">
                        Save Profile Changes
                    </button>
                </form>
            </section>


            <!-- ═══════════════════════════════════ PROJECTS SECTION -->
            <section id="projects" class="card rounded-2xl p-8 mb-8">
                <h2 class="text-base font-semibold text-white mb-6 flex items-center gap-3">
                    <span class="w-7 h-7 rounded-lg bg-green-500/20 text-green-400 flex items-center justify-center text-xs"><i class="fas fa-briefcase"></i></span>
                    <?php echo $edit_project ? 'Edit Project' : 'Add New Project'; ?>
                </h2>

                <form method="POST" enctype="multipart/form-data" class="space-y-5">
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                    <?php if($edit_project): ?>
                        <input type="hidden" name="project_id" value="<?php echo $edit_project['id']; ?>">
                    <?php endif; ?>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div>
                            <label class="block text-xs text-zinc-400 mb-2 tracking-widest uppercase">Project Title</label>
                            <input type="text" name="title" required value="<?php echo htmlspecialchars($edit_project['title'] ?? ''); ?>" class="input-field w-full rounded-lg px-4 py-3 text-sm">
                        </div>
                        <div>
                            <label class="block text-xs text-zinc-400 mb-2 tracking-widest uppercase">Tags (comma separated)</label>
                            <input type="text" name="tags" value="<?php echo htmlspecialchars($edit_project['tags'] ?? ''); ?>" placeholder="Python, Game, HTML" class="input-field w-full rounded-lg px-4 py-3 text-sm">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs text-zinc-400 mb-2 tracking-widest uppercase">Description</label>
                        <textarea name="description" rows="3" class="input-field w-full rounded-lg px-4 py-3 text-sm resize-none"><?php echo htmlspecialchars($edit_project['description'] ?? ''); ?></textarea>
                    </div>

                    <!-- Project Image: Upload OR URL -->
                    <div>
                        <label class="block text-xs text-zinc-400 mb-3 tracking-widest uppercase">Project Image</label>
                        <?php if(!empty($edit_project['image'])): ?>
                        <div class="mb-3">
                            <img src="<?php echo htmlspecialchars($edit_project['image']); ?>" class="h-24 rounded-lg object-cover border border-white/10" id="project-preview">
                        </div>
                        <?php else: ?>
                        <img src="" class="h-24 rounded-lg object-cover border border-white/10 hidden mb-3" id="project-preview">
                        <?php endif; ?>

                        <div class="upload-zone rounded-xl p-5 text-center cursor-pointer relative mb-3" onclick="document.getElementById('project_photo').click()">
                            <i class="fas fa-image text-zinc-500 text-xl mb-2 block"></i>
                            <p class="text-zinc-400 text-sm">Upload project screenshot</p>
                            <p class="text-zinc-600 text-xs mt-1">JPG, PNG, WEBP · Max 5MB</p>
                            <input type="file" id="project_photo" name="project_photo" accept="image/*" class="absolute inset-0 opacity-0 cursor-pointer w-full h-full">
                        </div>
                        <p class="text-zinc-600 text-xs text-center mb-2">— or paste an image URL —</p>
                        <input type="text" name="image_url" value="<?php echo htmlspecialchars($edit_project['image'] ?? ''); ?>" placeholder="https://images.unsplash.com/..." class="input-field w-full rounded-lg px-4 py-3 text-sm">
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div>
                            <label class="block text-xs text-zinc-400 mb-2 tracking-widest uppercase">GitHub Link</label>
                            <input type="text" name="github_link" value="<?php echo htmlspecialchars($edit_project['github_link'] ?? ''); ?>" placeholder="https://github.com/..." class="input-field w-full rounded-lg px-4 py-3 text-sm">
                        </div>
                        <div>
                            <label class="block text-xs text-zinc-400 mb-2 tracking-widest uppercase">Live Demo Link (Optional)</label>
                            <input type="text" name="live_link" value="<?php echo htmlspecialchars($edit_project['live_link'] ?? ''); ?>" placeholder="https://..." class="input-field w-full rounded-lg px-4 py-3 text-sm">
                        </div>
                    </div>

                    <div class="flex gap-3">
                        <button type="submit" name="<?php echo $edit_project ? 'edit_project' : 'add_project'; ?>"
                            class="bg-white text-black font-semibold px-8 py-3 rounded-lg text-sm hover:bg-zinc-200 transition-colors">
                            <?php echo $edit_project ? 'Save Changes' : 'Publish Project'; ?>
                        </button>
                        <?php if($edit_project): ?>
                        <a href="admin_dashboard.php#projects" class="border border-white/10 text-zinc-400 font-medium px-8 py-3 rounded-lg text-sm hover:text-white hover:border-white/20 transition-colors">Cancel</a>
                        <?php endif; ?>
                    </div>
                </form>
            </section>


            <!-- ═══════════════════════════════════ PROJECT LIST -->
            <section class="card rounded-2xl p-8 mb-8">
                <h2 class="text-base font-semibold text-white mb-6 flex items-center gap-3">
                    <span class="w-7 h-7 rounded-lg bg-purple-500/20 text-purple-400 flex items-center justify-center text-xs"><i class="fas fa-layer-group"></i></span>
                    All Projects
                </h2>
                <div class="space-y-3">
                <?php if($projects->num_rows > 0): ?>
                    <?php while($proj = $projects->fetch_assoc()): ?>
                    <div class="flex items-center gap-4 bg-white/3 border border-white/5 p-4 rounded-xl hover:border-white/10 transition-colors group">
                        <?php if(!empty($proj['image'])): ?>
                        <img src="<?php echo htmlspecialchars($proj['image']); ?>" class="w-14 h-14 rounded-lg object-cover flex-shrink-0 border border-white/10" onerror="this.style.display='none'">
                        <?php endif; ?>
                        <div class="flex-grow min-w-0">
                            <h3 class="font-medium text-white text-sm truncate"><?php echo htmlspecialchars($proj['title']); ?></h3>
                            <p class="text-zinc-500 text-xs mt-1"><?php echo htmlspecialchars($proj['tags']); ?></p>
                        </div>
                        <div class="flex items-center gap-2 flex-shrink-0">
                            <a href="admin_dashboard.php?edit=<?php echo $proj['id']; ?>#projects" class="text-zinc-400 hover:text-white text-xs px-3 py-1.5 border border-white/10 rounded-lg transition-colors hover:border-white/20">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form method="POST" onsubmit="return confirm('Delete this project permanently?')" class="inline">
                                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                <input type="hidden" name="project_id" value="<?php echo $proj['id']; ?>">
                                <button type="submit" name="delete_project" class="text-red-400 hover:text-white hover:bg-red-500 text-xs px-3 py-1.5 border border-red-500/20 rounded-lg transition-colors">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="text-center py-12 text-zinc-600">
                        <i class="fas fa-folder-open text-3xl mb-3 block"></i>
                        No projects yet. Add your first project above!
                    </div>
                <?php endif; ?>
                </div>
            </section>


            <!-- ═══════════════════════════════════ SECURITY SECTION -->
            <section id="security" class="card rounded-2xl p-8 mb-8">
                <h2 class="text-base font-semibold text-white mb-6 flex items-center gap-3">
                    <span class="w-7 h-7 rounded-lg bg-red-500/20 text-red-400 flex items-center justify-center text-xs"><i class="fas fa-shield-alt"></i></span>
                    Change Password
                </h2>
                <form method="POST" class="space-y-5 max-w-md">
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                    <div>
                        <label class="block text-xs text-zinc-400 mb-2 tracking-widest uppercase">Current Password</label>
                        <input type="password" name="current_password" required class="input-field w-full rounded-lg px-4 py-3 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs text-zinc-400 mb-2 tracking-widest uppercase">New Password (min. 8 characters)</label>
                        <input type="password" name="new_password" required minlength="8" class="input-field w-full rounded-lg px-4 py-3 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs text-zinc-400 mb-2 tracking-widest uppercase">Confirm New Password</label>
                        <input type="password" name="confirm_password" required minlength="8" class="input-field w-full rounded-lg px-4 py-3 text-sm">
                    </div>
                    <button type="submit" name="change_password" class="bg-red-500/20 text-red-400 border border-red-500/30 font-medium px-8 py-3 rounded-lg text-sm hover:bg-red-500 hover:text-white transition-all">
                        Update Password
                    </button>
                </form>
            </section>

        </div>
    </main>

    <script>
        // Live preview for profile photo
        document.getElementById('profile_photo').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = ev => document.getElementById('profile-preview').src = ev.target.result;
                reader.readAsDataURL(file);
            }
        });

        // Live preview for project photo
        document.getElementById('project_photo').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const preview = document.getElementById('project-preview');
                const reader = new FileReader();
                reader.onload = ev => { preview.src = ev.target.result; preview.classList.remove('hidden'); };
                reader.readAsDataURL(file);
            }
        });

        // Drag and drop styling
        document.querySelectorAll('.upload-zone').forEach(zone => {
            zone.addEventListener('dragover', e => { e.preventDefault(); zone.classList.add('dragover'); });
            zone.addEventListener('dragleave', () => zone.classList.remove('dragover'));
            zone.addEventListener('drop', e => {
                e.preventDefault(); zone.classList.remove('dragover');
                const input = zone.querySelector('input[type="file"]');
                if (input && e.dataTransfer.files.length) {
                    input.files = e.dataTransfer.files;
                    input.dispatchEvent(new Event('change'));
                }
            });
        });

        // Highlight active sidebar item on scroll
        const sections = document.querySelectorAll('section[id]');
        const navItems = document.querySelectorAll('.nav-item');
        window.addEventListener('scroll', () => {
            let current = '';
            sections.forEach(s => { if (window.scrollY >= s.offsetTop - 150) current = s.id; });
            navItems.forEach(a => {
                a.classList.remove('active');
                if (a.getAttribute('href') === '#' + current) a.classList.add('active');
            });
        });
    </script>
</body>
</html>
