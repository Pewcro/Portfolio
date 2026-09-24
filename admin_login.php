<?php
session_start();
session_regenerate_id(true);

require_once 'db.php';

// udah login? langsung masuk aja
if (!empty($_SESSION['admin_logged_in'])) {
    header("Location: admin_dashboard.php");
    exit;
}

$err = '';

// rate limit sederhana — 5x salah dalam 10 menit
if (!isset($_SESSION['fails']))       $_SESSION['fails'] = 0;
if (!isset($_SESSION['fail_since']))  $_SESSION['fail_since'] = time();

if (time() - $_SESSION['fail_since'] > 600) {
    $_SESSION['fails']      = 0;
    $_SESSION['fail_since'] = time();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if ($_SESSION['fails'] >= 5) {
        $wait = ceil((600 - (time() - $_SESSION['fail_since'])) / 60);
        $err  = "Terlalu banyak percobaan. Tunggu $wait menit.";
    } elseif (empty($_POST['_token']) || $_POST['_token'] !== $_SESSION['_token']) {
        $err = "Request tidak valid.";
    } else {
        $user = trim($_POST['username'] ?? '');
        $pass = $_POST['password'] ?? '';

        $st = $conn->prepare("SELECT id, password_hash FROM admin_users WHERE username = ?");
        $st->bind_param("s", $user);
        $st->execute();
        $row = $st->get_result()->fetch_assoc();

        if ($row && password_verify($pass, $row['password_hash'])) {
            session_regenerate_id(true);
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_id']        = $row['id'];
            $_SESSION['fails']           = 0;
            header("Location: admin_dashboard.php");
            exit;
        }

        $_SESSION['fails']++;
        $left = 5 - $_SESSION['fails'];
        $err  = "Username atau password salah. ($left percobaan tersisa)";
    }
}

// csrf token
if (empty($_SESSION['_token'])) {
    $_SESSION['_token'] = bin2hex(random_bytes(32));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin — Portfolio</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background: #050505; }
        .glass { background: rgba(255,255,255,.03); backdrop-filter: blur(20px); border: 1px solid rgba(255,255,255,.07); }
        input:-webkit-autofill {
            -webkit-box-shadow: 0 0 0 30px #0d0d0d inset !important;
            -webkit-text-fill-color: #fff !important;
        }
    </style>
</head>
<body class="flex items-center justify-center min-h-screen px-4">

    <div class="w-full max-w-sm">

        <div class="text-center mb-10">
            <a href="index.php" class="text-2xl font-medium tracking-[.2em] uppercase text-white">Jonathan.</a>
            <p class="text-zinc-500 text-xs mt-2 tracking-wide">Admin</p>
        </div>

        <div class="glass rounded-2xl p-8">

            <?php if($err): ?>
            <div class="bg-red-500/10 border border-red-500/30 text-red-400 text-sm text-center p-3 rounded-lg mb-5">
                <?php echo htmlspecialchars($err); ?>
            </div>
            <?php endif; ?>

            <form method="POST" class="space-y-4" autocomplete="off">
                <input type="hidden" name="_token" value="<?php echo $_SESSION['_token']; ?>">

                <div>
                    <label class="block text-zinc-400 text-xs tracking-widest uppercase mb-2">Username</label>
                    <input type="text" name="username" required autocomplete="username"
                        class="w-full bg-white/5 border border-white/10 rounded-lg px-4 py-3 text-white text-sm
                               focus:outline-none focus:border-white/30 transition-colors">
                </div>

                <div>
                    <label class="block text-zinc-400 text-xs tracking-widest uppercase mb-2">Password</label>
                    <input type="password" name="password" required
                        class="w-full bg-white/5 border border-white/10 rounded-lg px-4 py-3 text-white text-sm
                               focus:outline-none focus:border-white/30 transition-colors">
                </div>

                <button type="submit"
                    class="w-full bg-white text-black font-semibold py-3 rounded-lg text-sm tracking-wide
                           hover:bg-zinc-200 transition-colors mt-1">
                    Masuk
                </button>
            </form>

        </div>

        <p class="text-center mt-6">
            <a href="index.php" class="text-zinc-600 text-xs hover:text-zinc-400 transition-colors">← Kembali ke Portfolio</a>
        </p>

    </div>

</body>
</html>
