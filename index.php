<?php
require_once 'db.php';

// ambil data profile dari db
$q = $conn->query("SELECT * FROM profile LIMIT 1");
$me = $q->fetch_assoc();
$roles = explode(',', $me['roles']);
$bio_parts = explode('|', $me['about_text']);

// socials
$sq = $conn->query("SELECT * FROM social_links");
$links = [];
while($r = $sq->fetch_assoc()) {
    $links[$r['platform']] = $r['url'];
}

$skills_q = $conn->query("SELECT * FROM skills");
$proj_q   = $conn->query("SELECT * FROM projects");
?>
<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($me['full_name']); ?></title>

    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://unpkg.com/typed.js@2.1.0/dist/typed.umd.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/vanilla-tilt/1.8.0/vanilla-tilt.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@200;300;400;500;600;700&display=swap" rel="stylesheet">

    <style>
        * { box-sizing: border-box; }
        
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: #050505;
            color: #a1a1aa;
            overflow-x: hidden;
        }
        h1,h2,h3,h4 { color: #f4f4f5; }

        .bg-space {
            position: fixed;
            inset: 0;
            background: radial-gradient(circle at top center, #18181b 0%, #050505 80%);
            z-index: -2;
        }

        /* stars — nemu ini dari stack overflow lol */
        .stars {
            position: fixed;
            inset: 0;
            z-index: -1;
            background-image:
                radial-gradient(1px 1px at 20px 30px, #fff, transparent),
                radial-gradient(1px 1px at 40px 70px, rgba(255,255,255,.8), transparent),
                radial-gradient(1.5px 1.5px at 50px 160px, rgba(255,255,255,.6), transparent),
                radial-gradient(1px 1px at 90px 40px, #fff, transparent),
                radial-gradient(2px 2px at 130px 80px, rgba(255,255,255,.9), transparent),
                radial-gradient(1px 1px at 160px 120px, rgba(255,255,255,.4), transparent);
            background-repeat: repeat;
            background-size: 200px 200px;
            animation: drift 150s linear infinite;
            opacity: .15;
        }
        @keyframes drift {
            to { transform: translateY(-200px); }
        }

        .glass {
            background: rgba(255,255,255,.02);
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
            border: 1px solid rgba(255,255,255,.05);
            box-shadow: 0 25px 50px -12px rgba(0,0,0,.5);
        }

        nav.scrolled {
            background: rgba(5,5,5,.88);
            backdrop-filter: blur(20px);
            border-bottom: 1px solid rgba(255,255,255,.05);
        }

        /* tombol utama */
        .btn {
            display: inline-block;
            padding: 13px 34px;
            border: 1px solid rgba(255,255,255,.2);
            border-radius: 100px;
            color: #fff;
            font-size: .75rem;
            font-weight: 500;
            letter-spacing: 2px;
            text-transform: uppercase;
            background: rgba(255,255,255,.02);
            transition: all .4s ease;
        }
        .btn:hover {
            background: #fff;
            color: #050505;
            box-shadow: 0 0 30px rgba(255,255,255,.12);
            transform: translateY(-2px);
        }

        .btn-ghost {
            display: inline-block;
            padding: 13px 34px;
            border-radius: 100px;
            color: #71717a;
            font-size: .75rem;
            font-weight: 500;
            letter-spacing: 2px;
            text-transform: uppercase;
            transition: all .4s ease;
        }
        .btn-ghost:hover {
            background: rgba(255,255,255,.05);
            color: #fff;
        }

        /* project image effect */
        .proj-img {
            filter: grayscale(100%) brightness(.8);
            transition: all .7s ease;
        }
        .proj-card:hover .proj-img {
            filter: grayscale(20%) brightness(1);
            transform: scale(1.04);
        }

        /* teks gradient buat hero */
        .grad-text {
            background: linear-gradient(to right, #fff, #71717a);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
    </style>
</head>
<body class="selection:bg-white selection:text-black">

<div class="bg-space"></div>
<div class="stars"></div>

<!-- navbar -->
<nav id="nav" class="fixed w-full z-50 py-6 transition-all duration-300">
    <div class="max-w-6xl mx-auto px-6 flex justify-between items-center">
        <a href="admin_login.php" title="admin" class="text-xl font-medium tracking-[.2em] uppercase text-white hover:opacity-60 transition-opacity">
            <?php echo htmlspecialchars($me['short_name']); ?>.
        </a>
        <div class="hidden md:flex gap-9 text-xs font-medium tracking-[.15em] uppercase">
            <a href="#home"    class="text-white  hover:opacity-60 transition-opacity">Home</a>
            <a href="#about"   class="text-zinc-400 hover:text-white transition-colors">About</a>
            <a href="#projects" class="text-zinc-400 hover:text-white transition-colors">Work</a>
            <a href="#contact" class="text-zinc-400 hover:text-white transition-colors">Contact</a>
        </div>
        <!-- mobile menu — nanti dibikin kalau sempat -->
        <button class="md:hidden text-white"><i class="fas fa-bars"></i></button>
    </div>
</nav>


<!-- ────────────────────── HERO -->
<section id="home" class="min-h-screen flex items-center justify-center px-6 pt-20">
    <div class="text-center max-w-3xl mx-auto mt-10">

        <p class="text-zinc-400 text-xs tracking-[.3em] uppercase mb-5 font-medium">Portfolio</p>

        <h1 class="text-5xl md:text-7xl font-light grad-text mb-5 tracking-tight">
            <?php echo htmlspecialchars($me['full_name']); ?>
        </h1>

        <h2 class="text-xl md:text-3xl font-light text-zinc-300 mb-9 tracking-wide">
            I'm a <span id="typed" class="font-medium text-white"></span>
        </h2>

        <p class="text-sm md:text-base text-zinc-400 mb-12 max-w-xl mx-auto leading-relaxed">
            <?php echo htmlspecialchars($me['hero_desc']); ?>
        </p>

        <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
            <a href="#projects" class="btn">View My Work</a>
            <a href="<?php echo htmlspecialchars($links['github'] ?? '#'); ?>" target="_blank" class="btn-ghost">
                <i class="fab fa-github mr-2"></i>GitHub
            </a>
        </div>
    </div>
</section>


<!-- ────────────────────── ABOUT -->
<section id="about" class="py-32 px-6 relative z-10">
    <div class="max-w-6xl mx-auto">

        <div class="flex items-center gap-5 mb-14">
            <span class="text-xs text-zinc-500 tracking-[.2em] uppercase">01</span>
            <h2 class="text-2xl font-light tracking-wide text-white uppercase">About Me</h2>
            <div class="h-px bg-white/10 flex-grow max-w-xs"></div>
        </div>

        <div class="grid md:grid-cols-12 gap-14 items-center glass p-8 md:p-12 rounded-2xl">

            <div class="md:col-span-7 space-y-5 text-zinc-400 text-base leading-relaxed font-light">
                <?php foreach($bio_parts as $p): ?>
                    <p><?php echo htmlspecialchars(trim($p)); ?></p>
                <?php endforeach; ?>

                <div class="pt-5">
                    <p class="text-xs tracking-[.1em] text-white uppercase mb-4">Technologies</p>
                    <ul class="grid grid-cols-2 gap-y-3 text-sm">
                        <?php while($skill = $skills_q->fetch_assoc()): ?>
                        <li class="flex items-center gap-3">
                            <div class="w-1 h-1 bg-white rounded-full flex-shrink-0"></div>
                            <?php echo htmlspecialchars($skill['skill_name']); ?>
                        </li>
                        <?php endwhile; ?>
                    </ul>
                </div>
            </div>

            <div class="md:col-span-5" data-tilt data-tilt-max="3" data-tilt-glare data-tilt-max-glare="0.1">
                <div class="glass rounded-2xl p-2">
                    <img src="<?php echo htmlspecialchars($me['profile_image']); ?>"
                         alt="<?php echo htmlspecialchars($me['short_name']); ?>"
                         class="w-full rounded-xl object-cover aspect-[4/5]"
                         style="filter: grayscale(30%) contrast(1.1);">
                </div>
            </div>

        </div>
    </div>
</section>


<!-- ────────────────────── PROJECTS -->
<section id="projects" class="py-32 px-6 relative z-10">
    <div class="max-w-6xl mx-auto">

        <div class="flex items-center gap-5 mb-14">
            <span class="text-xs text-zinc-500 tracking-[.2em] uppercase">02</span>
            <h2 class="text-2xl font-light tracking-wide text-white uppercase">Selected Work</h2>
            <div class="h-px bg-white/10 flex-grow max-w-xs"></div>
        </div>

        <div class="grid md:grid-cols-2 gap-10">
        <?php while($p = $proj_q->fetch_assoc()): ?>

            <div class="proj-card glass rounded-2xl overflow-hidden flex flex-col"
                 data-tilt data-tilt-max="2" data-tilt-glare data-tilt-max-glare="0.05">

                <div class="h-56 overflow-hidden relative">
                    <div class="absolute inset-0 bg-black/20 z-10 group-hover:bg-transparent transition-all duration-500"></div>
                    <img src="<?php echo htmlspecialchars($p['image']); ?>"
                         alt="<?php echo htmlspecialchars($p['title']); ?>"
                         class="proj-img w-full h-full object-cover absolute inset-0">
                    <!-- overlay links -->
                    <div class="absolute inset-0 z-20 flex items-center justify-center gap-6
                                bg-black/80 opacity-0 hover:opacity-100 transition-opacity duration-400 backdrop-blur-sm">
                        <a href="<?php echo htmlspecialchars($p['github_link']); ?>" target="_blank"
                           class="text-white/70 hover:text-white text-2xl transition-all hover:-translate-y-1">
                            <i class="fab fa-github"></i>
                        </a>
                        <?php if(!empty($p['live_link']) && $p['live_link'] !== '#'): ?>
                        <a href="<?php echo htmlspecialchars($p['live_link']); ?>" target="_blank"
                           class="text-white/70 hover:text-white text-2xl transition-all hover:-translate-y-1">
                            <i class="fas fa-external-link-alt"></i>
                        </a>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="p-7 flex flex-col flex-grow">
                    <h3 class="text-lg font-medium text-white mb-2 tracking-wide">
                        <?php echo htmlspecialchars($p['title']); ?>
                    </h3>
                    <p class="text-zinc-400 text-sm flex-grow leading-relaxed mb-6">
                        <?php echo htmlspecialchars($p['description']); ?>
                    </p>
                    <div class="flex flex-wrap gap-3 text-xs text-zinc-500 uppercase tracking-widest">
                        <?php foreach(explode(',', $p['tags']) as $tag): ?>
                            <span><?php echo htmlspecialchars(trim($tag)); ?></span>
                        <?php endforeach; ?>
                    </div>
                </div>

            </div>

        <?php endwhile; ?>
        </div>

    </div>
</section>


<!-- ────────────────────── CONTACT -->
<section id="contact" class="py-32 px-6 text-center relative z-10">
    <div class="max-w-2xl mx-auto glass p-14 rounded-3xl">
        <p class="text-zinc-500 text-xs tracking-[.2em] uppercase mb-5">03. Contact</p>
        <h2 class="text-4xl md:text-5xl font-light text-white mb-7 tracking-wide">Let's Connect</h2>
        <p class="text-zinc-400 text-sm leading-relaxed mb-10 max-w-md mx-auto">
            Whether it's a collab, freelance gig, or just saying hi — hit me up, I'll reply!
        </p>
        <a href="mailto:<?php echo htmlspecialchars($me['email']); ?>" class="btn">Send an Email</a>
    </div>
</section>


<!-- footer -->
<footer class="py-10 border-t border-white/5 text-center text-zinc-600 relative z-10">
    <div class="flex justify-center gap-7 mb-6">
        <a href="<?php echo htmlspecialchars($links['github'] ?? '#'); ?>" target="_blank" class="hover:text-white transition-colors text-lg"><i class="fab fa-github"></i></a>
        <a href="<?php echo htmlspecialchars($links['linkedin'] ?? '#'); ?>" target="_blank" class="hover:text-white transition-colors text-lg"><i class="fab fa-linkedin-in"></i></a>
        <a href="<?php echo htmlspecialchars($links['instagram'] ?? '#'); ?>" target="_blank" class="hover:text-white transition-colors text-lg"><i class="fab fa-instagram"></i></a>
    </div>
    <p class="text-[10px] tracking-[.2em] uppercase">
        Built by <?php echo htmlspecialchars($me['full_name']); ?>
    </p>
</footer>


<script>
    // sticky nav
    const nav = document.getElementById('nav');
    window.addEventListener('scroll', () => {
        nav.classList.toggle('scrolled', window.scrollY > 50);
        nav.classList.toggle('py-4', window.scrollY > 50);
        nav.classList.toggle('py-6', window.scrollY <= 50);
    });

    // typed.js
    new Typed('#typed', {
        strings: <?php echo json_encode(array_map('trim', $roles)); ?>,
        typeSpeed: 65,
        backSpeed: 35,
        backDelay: 2200,
        loop: true,
    });
</script>

</body>
</html>
