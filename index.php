<?php
session_start();

// Check if user is already logged in. 
// If so, the main button will redirect to the Dashboard
$is_logged_in = isset($_SESSION['user_id']);
$dashboard_link = "pages/dashboard.php";
$login_link = "pages/loginpage.html"; 

$target_link = $is_logged_in ? $dashboard_link : $login_link;
$button_text = $is_logged_in ? "Go to Dashboard" : "Start Learning Now";
?>

<!DOCTYPE html>
<html lang="en" class="dark scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ReadQuest - Improve Your Reading Score</title>
    <link rel="icon" type="image/png" href="/assets/favicon.png">
    
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&family=Hanken+Grotesk:wght@400;500;600&family=JetBrains+Mono:wght@700&family=Source+Sans+3:wght@400;600&display=swap" rel="stylesheet"/>
    
    <script id="tailwind-config">
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    "colors": {
                        "background": "#0b1326",
                        "primary-fixed-dim": "#adc6ff",
                        "inverse-surface": "#dae2fd",
                        "on-primary-fixed-variant": "#004395",
                        "surface-container": "#171f33",
                        "surface-container-highest": "#2d3449",
                        "on-secondary-container": "#5b3800",
                        "tertiary": "#98da27",
                        "on-primary-fixed": "#001a42",
                        "on-tertiary-fixed-variant": "#334f00",
                        "outline-variant": "#424754",
                        "inverse-on-surface": "#283044",
                        "on-tertiary-fixed": "#121f00",
                        "on-tertiary": "#213600",
                        "on-secondary-fixed": "#2a1700",
                        "tertiary-fixed": "#b2f746",
                        "tertiary-container": "#6ba000",
                        "surface-container-lowest": "#060e20",
                        "on-error": "#690005",
                        "surface": "#0b1326",
                        "surface-tint": "#adc6ff",
                        "on-primary": "#002e6a",
                        "secondary-container": "#ee9800",
                        "on-background": "#dae2fd",
                        "primary": "#adc6ff",
                        "outline": "#8c909f",
                        "primary-fixed": "#d8e2ff",
                        "inverse-primary": "#005ac2",
                        "primary-container": "#4d8eff",
                        "on-secondary": "#472a00",
                        "on-surface": "#dae2fd",
                        "tertiary-fixed-dim": "#98da27",
                        "on-primary-container": "#00285d",
                        "secondary": "#ffb95f",
                        "surface-container-low": "#131b2e",
                        "on-tertiary-container": "#1c2f00",
                        "surface-variant": "#2d3449",
                        "error": "#ffb4ab",
                        "on-secondary-fixed-variant": "#653e00",
                        "surface-container-high": "#222a3d",
                        "on-error-container": "#ffdad6",
                        "error-container": "#93000a",
                        "secondary-fixed-dim": "#ffb95f",
                        "surface-dim": "#0b1326",
                        "on-surface-variant": "#c2c6d6",
                        "secondary-fixed": "#ffddb8",
                        "surface-bright": "#31394d"
                    },
                    "borderRadius": {
                        "DEFAULT": "0.25rem",
                        "lg": "0.5rem",
                        "xl": "0.75rem",
                        "full": "9999px"
                    },
                    "spacing": {
                        "base": "8px",
                        "stack-sm": "12px",
                        "stack-md": "24px",
                        "gutter": "24px",
                        "container-max": "1280px",
                        "margin-desktop": "40px",
                        "stack-lg": "48px",
                        "margin-mobile": "16px"
                    },
                    "fontFamily": {
                        "display-lg": ["Montserrat"],
                        "body-md": ["Hanken Grotesk"],
                        "stats-number": ["Montserrat"],
                        "headline-md": ["Montserrat"],
                        "label-caps": ["JetBrains Mono"],
                        "body-lg": ["Hanken Grotesk"],
                        "headline-sm": ["Montserrat"],
                        "display-lg-mobile": ["Montserrat"]
                    },
                    "fontSize": {
                        "display-lg": ["48px", { "lineHeight": "56px", "letterSpacing": "-0.02em", "fontWeight": "800" }],
                        "body-md": ["16px", { "lineHeight": "24px", "fontWeight": "400" }],
                        "stats-number": ["32px", { "lineHeight": "32px", "fontWeight": "700" }],
                        "headline-md": ["24px", { "lineHeight": "32px", "fontWeight": "700" }],
                        "label-caps": ["12px", { "lineHeight": "16px", "letterSpacing": "0.1em", "fontWeight": "700" }],
                        "body-lg": ["18px", { "lineHeight": "28px", "fontWeight": "400" }],
                        "headline-sm": ["20px", { "lineHeight": "28px", "fontWeight": "600" }],
                        "display-lg-mobile": ["32px", { "lineHeight": "40px", "letterSpacing": "-0.02em", "fontWeight": "800" }]
                    },
                    "animation": {
                        "pulse-glow": "pulse-glow 3s ease-in-out infinite",
                        "float": "float 6s ease-in-out infinite"
                    },
                    "keyframes": {
                        "pulse-glow": {
                            "0%, 100%": { opacity: "1", boxShadow: "0 0 15px rgba(173, 198, 255, 0.4)" },
                            "50%": { opacity: ".9", boxShadow: "0 0 30px rgba(173, 198, 255, 0.8)" }
                        },
                        "float": {
                            "0%, 100%": { transform: "translateY(0)" },
                            "50%": { transform: "translateY(-15px)" }
                        }
                    }
                }
            }
        }
    </script>
    
    <link rel="stylesheet" href="desain/index.css?v=2.0">
</head>
<body class="font-body-md text-body-md antialiased relative">
    <!-- Ambient Background Glows -->
    <div class="fixed top-[-20%] left-[-10%] w-[60%] h-[60%] rounded-full bg-primary/10 blur-[120px] pointer-events-none z-0"></div>
    <div class="fixed bottom-[-20%] right-[-10%] w-[50%] h-[50%] rounded-full bg-[#8b5cf6]/10 blur-[120px] pointer-events-none z-0"></div>

    <!-- TopNavBar -->
    <header class="navbar">
        <div class="nav-container">
            <div class="logo">ReadQuest</div>
            <?php if(!$is_logged_in): ?>
                <div class="nav-buttons">
                    <a href="pages/loginpage.html" class="nav-login-btn">Login</a>
                    <a href="pages/signup.html" class="nav-signup-btn glow-blue">Sign Up</a>
                </div>
            <?php else: ?>
                <div class="nav-buttons">
                    <a href="<?= $dashboard_link ?>" class="nav-signup-btn glow-blue">Dashboard</a>
                </div>
            <?php endif; ?>
        </div>
    </header>

    <!-- Main Content -->
    <main class="relative z-10 pt-[120px] pb-stack-lg">
        <!-- Hero Section -->
        <section class="hero-section fade-in-up visible">
            <div class="hero-content">
                <div class="typewriter-container max-w-full">
                    <h1 class="pr-2"><span id="typewriter-text" class="typewriter-text"></span></h1>
                </div>
                <p>Don't just read—conquer. Experience a fully gamified reading journey that prepares you for the rigor of the actual exam.</p>
                <div class="hero-actions">
                    <a href="<?= $target_link ?>" class="btn-primary glow-blue"><?= $button_text ?></a>
                    <a href="#features" class="btn-secondary">Explore Features</a>
                </div>
            </div>
            <div class="hero-image-wrapper animate-float">
                <div class="glow-backdrop"></div>
                <img src="assets/hero-image.png" alt="Dashboard Graphic" class="hero-image">
            </div>
        </section>

        <!-- Container for New Content Below Hero -->
        <div class="max-w-container-max mx-auto px-margin-mobile md:px-margin-desktop">
            <!-- Features Bento Grid -->
            <section id="features" class="py-stack-lg border-t border-white/5 relative scroll-mt-[100px]">
                <!-- Background Glow -->
                <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[80vw] h-[60vh] bg-blue-600/10 rounded-full blur-[120px] pointer-events-none z-[-1]"></div>
                <div class="absolute top-0 left-1/2 -translate-x-1/2 w-[80%] h-1 bg-gradient-to-r from-transparent via-primary/20 to-transparent"></div>
                <div class="text-center mb-stack-lg fade-in-up">
                    <h2 class="font-headline-md text-headline-md text-on-surface mb-2">Systems Engineered for Success</h2>
                    <p class="font-body-md text-body-md text-on-surface-variant max-w-xl mx-auto">Master the mechanics of reading comprehension through structured, gamified intelligence.</p>
                </div>
                <div id="horizontal-scroll-section" class="relative w-full h-[250vh]">
                    <div class="sticky top-[100px] overflow-hidden flex items-center h-[70vh] min-h-[450px]">
                        <div id="horizontal-scroll-track" class="flex gap-stack-md w-max px-4 md:px-8 will-change-transform" style="transition: transform 0.1s ease-out;">
                            <!-- Feature 1 -->
                            <div class="glass-panel p-stack-md rounded-xl hover-card fade-in-up flex flex-col gap-stack-sm relative overflow-hidden group w-[85vw] md:w-[400px] shrink-0 border-t-2 border-t-primary/30">
                                <div class="absolute -right-10 -top-10 w-32 h-32 bg-primary/10 rounded-full blur-2xl group-hover:bg-primary/20 transition-colors"></div>
                                <div class="w-12 h-12 bg-surface-container rounded-lg flex items-center justify-center border border-white/10 mb-2">
                                    <span class="material-symbols-outlined text-primary text-2xl">route</span>
                                </div>
                                <h3 class="font-headline-sm text-headline-sm text-on-surface">The Gamified Practice Path</h3>
                                <p class="font-body-md text-body-md text-on-surface-variant flex-grow">Progress through meticulously designed levels. Every reading passage conquered unlocks new, harder challenges.</p>
                                <div class="mt-4">
                                    <div class="flex justify-between font-label-caps text-label-caps text-on-surface-variant mb-1">
                                        <span class="">Level A1 Progress</span>
                                        <span class="text-primary">68%</span>
                                    </div>
                                    <div class="h-2 w-full bg-surface-container rounded-full overflow-hidden">
                                        <div class="h-full bg-gradient-to-r from-primary to-primary-fixed rounded-full w-[68%] relative shadow-[0_0_10px_#adc6ff]"></div>
                                    </div>
                                </div>
                            </div>
                            <!-- Feature 2 -->
                            <div class="glass-panel p-stack-md rounded-xl hover-card fade-in-up flex flex-col gap-stack-sm relative overflow-hidden group w-[85vw] md:w-[400px] shrink-0 border-t-2 border-t-secondary/30" style="--delay: 0.1s;">
                                <div class="absolute -right-10 -top-10 w-32 h-32 bg-secondary/10 rounded-full blur-2xl group-hover:bg-secondary/20 transition-colors"></div>
                                <div class="w-12 h-12 bg-surface-container rounded-lg flex items-center justify-center border border-white/10 mb-2">
                                    <span class="material-symbols-outlined text-secondary text-2xl">timer</span>
                                </div>
                                <h3 class="font-headline-sm text-headline-sm text-on-surface">Full-Length Simulations</h3>
                                <p class="font-body-md text-body-md text-on-surface-variant flex-grow">Experience the pressure of the real exam through structured mock sessions, organized into convenient sets like Package A, Package B, and more.</p>
                                <div class="mt-4 flex gap-2">
                                    <div class="flex-1 bg-surface-container-highest py-2 rounded flex flex-col items-center justify-center border border-white/5 border-b-2 border-b-secondary/50">
                                        <span class="font-headline-sm text-[13px] text-secondary drop-shadow-[0_0_5px_rgba(255,185,95,0.4)]">Package A</span>
                                    </div>
                                    <div class="flex-1 bg-surface-container-highest py-2 rounded flex flex-col items-center justify-center border border-white/5 border-b-2 border-b-secondary/50">
                                        <span class="font-headline-sm text-[13px] text-secondary drop-shadow-[0_0_5px_rgba(255,185,95,0.4)]">Package B</span>
                                    </div>
                                    <div class="flex-1 bg-surface-container-highest py-2 rounded flex flex-col items-center justify-center border border-white/5 border-b-2 border-b-secondary/20">
                                        <span class="font-headline-sm text-[13px] text-on-surface-variant">+ More</span>
                                    </div>
                                </div>
                            </div>
                            <!-- Feature 3 -->
                            <div class="glass-panel p-stack-md rounded-xl hover-card fade-in-up flex flex-col gap-stack-sm relative overflow-hidden group w-[85vw] md:w-[400px] shrink-0 border-t-2 border-t-tertiary/30" style="--delay: 0.2s;">
                                <div class="absolute -right-10 -top-10 w-32 h-32 bg-tertiary/10 rounded-full blur-2xl group-hover:bg-tertiary/20 transition-colors"></div>
                                <div class="w-12 h-12 bg-surface-container rounded-lg flex items-center justify-center border border-white/10 mb-2">
                                    <span class="material-symbols-outlined text-tertiary text-2xl">psychology</span>
                                </div>
                                <h3 class="font-headline-sm text-headline-sm text-on-surface">Reciprocal Reading Strategy</h3>
                                <p class="font-body-md text-body-md text-on-surface-variant flex-grow">Master English texts using the 4 proven phases of reciprocal reading to deeply boost your comprehension.</p>
                                <div class="mt-4 grid grid-cols-2 gap-2">
                                    <div class="bg-surface-container p-2 rounded border border-white/5 flex flex-col items-center text-center">
                                        <span class="font-label-caps text-label-caps text-on-surface-variant text-[10px]">Phase 1</span>
                                        <span class="font-headline-sm text-[14px] font-bold text-tertiary mt-1">Predicting</span>
                                    </div>
                                    <div class="bg-surface-container p-2 rounded border border-white/5 flex flex-col items-center text-center">
                                        <span class="font-label-caps text-label-caps text-on-surface-variant text-[10px]">Phase 2</span>
                                        <span class="font-headline-sm text-[14px] font-bold text-tertiary mt-1">Questioning</span>
                                    </div>
                                    <div class="bg-surface-container p-2 rounded border border-white/5 flex flex-col items-center text-center">
                                        <span class="font-label-caps text-label-caps text-on-surface-variant text-[10px]">Phase 3</span>
                                        <span class="font-headline-sm text-[14px] font-bold text-tertiary mt-1">Clarifying</span>
                                    </div>
                                    <div class="bg-surface-container p-2 rounded border border-white/5 flex flex-col items-center text-center">
                                        <span class="font-label-caps text-label-caps text-on-surface-variant text-[10px]">Phase 4</span>
                                        <span class="font-headline-sm text-[14px] font-bold text-tertiary mt-1">Summarizing</span>
                                    </div>
                                </div>
                            </div>
                            <!-- Feature 4 -->
                            <div class="glass-panel p-stack-md rounded-xl hover-card fade-in-up flex flex-col gap-stack-sm relative overflow-hidden group w-[85vw] md:w-[400px] shrink-0 border-t-2 border-t-primary/30" style="--delay: 0.3s;">
                                <div class="absolute -right-10 -top-10 w-32 h-32 bg-primary/10 rounded-full blur-2xl group-hover:bg-primary/20 transition-colors"></div>
                                <div class="w-12 h-12 bg-surface-container rounded-lg flex items-center justify-center border border-white/10 mb-2">
                                    <span class="material-symbols-outlined text-primary text-2xl">ssid_chart</span>
                                </div>
                                <h3 class="font-headline-sm text-headline-sm text-on-surface">Track Your Performance</h3>
                                <p class="font-body-md text-body-md text-on-surface-variant flex-grow">Monitor your learning journey through detailed line chart graphs that visualize your progress over time.</p>
                                <div class="mt-4 bg-surface-container-highest p-3 rounded flex items-end justify-between px-4 border border-white/5 relative h-[72px] overflow-hidden">
                                    <svg class="absolute inset-0 w-full h-full" preserveAspectRatio="none" viewBox="0 0 150 80">
                                        <path d="M0,80 L20,60 L40,70 L60,40 L80,50 L100,20 L120,40 L150,10" fill="none" stroke="#adc6ff" stroke-width="3" class="opacity-80"></path>
                                        <path d="M0,80 L20,60 L40,70 L60,40 L80,50 L100,20 L120,40 L150,10 L150,80 L0,80 Z" fill="url(#chart-gradient)" class="opacity-30"></path>
                                        <defs>
                                            <linearGradient id="chart-gradient" x1="0" y1="0" x2="0" y2="1">
                                                <stop offset="0%" stop-color="#adc6ff"></stop>
                                                <stop offset="100%" stop-color="transparent"></stop>
                                            </linearGradient>
                                        </defs>
                                    </svg>
                                    <span class="relative font-label-caps text-label-caps text-on-surface-variant z-10 pb-1">Score Trend</span>
                                    <span class="relative material-symbols-outlined text-primary z-10 drop-shadow-[0_0_5px_rgba(173,198,255,0.8)] pb-1">trending_up</span>
                                </div>
                            </div>
                            <!-- Feature 5 -->
                            <div class="glass-panel p-stack-md rounded-xl hover-card fade-in-up flex flex-col gap-stack-sm relative overflow-hidden group w-[85vw] md:w-[400px] shrink-0 border-t-2 border-t-secondary/30" style="--delay: 0.4s;">
                                <div class="absolute -right-10 -top-10 w-32 h-32 bg-secondary/10 rounded-full blur-2xl group-hover:bg-secondary/20 transition-colors"></div>
                                <div class="w-12 h-12 bg-surface-container rounded-lg flex items-center justify-center border border-white/10 mb-2">
                                    <span class="material-symbols-outlined text-secondary text-2xl">menu_book</span>
                                </div>
                                <h3 class="font-headline-sm text-headline-sm text-on-surface">Authentic CEFR Texts</h3>
                                <p class="font-body-md text-body-md text-on-surface-variant flex-grow">Sharpen your skills with reading materials adapted from real Cambridge Exams, accurately graded from A1 (Beginner) to C2 (Mastery).</p>
                                <div class="mt-4 flex flex-wrap gap-2 justify-between">
                                    <div class="flex items-center justify-center bg-surface-container flex-1 py-2 rounded border border-white/5">
                                        <span class="font-label-caps text-[12px] text-on-surface-variant">A1-A2</span>
                                    </div>
                                    <div class="flex items-center justify-center bg-surface-container flex-1 py-2 rounded border border-white/5">
                                        <span class="font-label-caps text-[12px] text-on-surface-variant">B1-B2</span>
                                    </div>
                                    <div class="flex items-center justify-center bg-surface-container flex-1 py-2 rounded border border-secondary/30">
                                        <span class="font-label-caps text-[12px] text-secondary drop-shadow-[0_0_5px_rgba(255,185,95,0.4)]">C1-C2</span>
                                    </div>
                                </div>
                            </div>
                            <!-- Feature 6 -->
                            <div class="glass-panel p-stack-md rounded-xl hover-card fade-in-up flex flex-col gap-stack-sm relative overflow-hidden group w-[85vw] md:w-[400px] shrink-0 border-t-2 border-t-tertiary/30" style="--delay: 0.5s;">
                                <div class="absolute -right-10 -top-10 w-32 h-32 bg-tertiary/10 rounded-full blur-2xl group-hover:bg-tertiary/20 transition-colors"></div>
                                <div class="w-12 h-12 bg-surface-container rounded-lg flex items-center justify-center border border-white/10 mb-2">
                                    <span class="material-symbols-outlined text-tertiary text-2xl">emoji_events</span>
                                </div>
                                <h3 class="font-headline-sm text-headline-sm text-on-surface">Competitive Leaderboard</h3>
                                <p class="font-body-md text-body-md text-on-surface-variant flex-grow">Fuel your motivation by competing with peers. Climb the global leaderboard by earning XP exclusively in the practice section.</p>
                                <div class="mt-4 flex flex-col gap-2">
                                    <div class="flex items-center justify-between bg-surface-container px-3 py-2 rounded border border-tertiary/30 relative overflow-hidden">
                                        <div class="absolute left-0 top-0 bottom-0 w-1 bg-tertiary"></div>
                                        <div class="flex items-center gap-3 ml-1">
                                            <span class="font-label-caps text-tertiary font-bold">#1</span>
                                            <span class="font-body-md text-[13px] text-on-surface font-semibold">Alex M.</span>
                                        </div>
                                        <span class="font-label-caps text-[11px] text-tertiary drop-shadow-[0_0_5px_rgba(152,218,39,0.4)]">2450 XP</span>
                                    </div>
                                    <div class="flex items-center justify-between bg-surface-container px-3 py-2 rounded border border-white/5 opacity-80">
                                        <div class="flex items-center gap-3 ml-1">
                                            <span class="font-label-caps text-on-surface-variant">#2</span>
                                            <span class="font-body-md text-[13px] text-on-surface">Sarah K.</span>
                                        </div>
                                        <span class="font-label-caps text-[11px] text-on-surface-variant">2310 XP</span>
                                    </div>
                                    <div class="flex items-center justify-between bg-surface-container px-3 py-2 rounded border border-white/5 opacity-60">
                                        <div class="flex items-center gap-3 ml-1">
                                            <span class="font-label-caps text-on-surface-variant">#3</span>
                                            <span class="font-body-md text-[13px] text-on-surface">You</span>
                                        </div>
                                        <span class="font-label-caps text-[11px] text-on-surface-variant">2150 XP</span>
                                    </div>
                                </div>
                            </div>
                            <!-- Feature 7: CEFR Badges -->
                            <div class="glass-panel p-stack-md rounded-xl hover-card fade-in-up flex flex-col gap-stack-sm relative overflow-hidden group w-[85vw] md:w-[400px] shrink-0 border-t-2 border-t-[#a855f7]/30" style="--delay: 0.6s;">
                                <div class="absolute -right-10 -top-10 w-32 h-32 bg-[#a855f7]/10 rounded-full blur-2xl group-hover:bg-[#a855f7]/20 transition-colors"></div>
                                <div class="w-12 h-12 bg-surface-container rounded-lg flex items-center justify-center border border-white/10 mb-2">
                                    <span class="material-symbols-outlined text-[#a855f7] text-2xl">military_tech</span>
                                </div>
                                <h3 class="font-headline-sm text-headline-sm text-on-surface">CEFR Mastery Badges</h3>
                                <p class="font-body-md text-body-md text-on-surface-variant flex-grow">Earn prestigious titles from The Conqueror (A2) to The Maestro (C1) by conquering tests. Flex your highest rank on your profile.</p>
                                <div class="mt-4 flex gap-2">
                                    <div class="flex-1 bg-surface-container-highest py-2 rounded flex flex-col items-center justify-center border border-white/5 border-b-2 border-b-[#22c55e]/50 shadow-[0_0_8px_rgba(34,197,94,0.1)]">
                                        <span class="font-headline-sm text-[12px] text-[#22c55e] px-1 text-center">A2: The Conqueror</span>
                                    </div>
                                    <div class="flex-1 bg-surface-container-highest py-2 rounded flex flex-col items-center justify-center border border-white/5 border-b-2 border-b-[#a855f7]/50 shadow-[0_0_8px_rgba(168,85,247,0.1)]">
                                        <span class="font-headline-sm text-[12px] text-[#a855f7] px-1 text-center">C1: The Maestro</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- How It Works Timeline -->
            <section class="py-stack-lg relative fade-in-up">
                <div class="text-center mb-stack-lg">
                    <h2 class="font-headline-md text-headline-md text-on-surface mb-2">The Operational Flow</h2>
                    <p class="font-body-md text-body-md text-on-surface-variant max-w-xl mx-auto">Follow the protocol. Elevate your score.</p>
                </div>
                <div class="max-w-3xl mx-auto relative pl-8 md:pl-0" id="timeline-container">
                    <!-- Vertical Line -->
                    <div class="absolute left-8 md:left-1/2 top-0 bottom-0 w-1 bg-surface-container -translate-x-1/2 rounded-full overflow-hidden">
                        <div class="w-full bg-gradient-to-b from-primary via-secondary to-tertiary h-0 timeline-line shadow-[0_0_10px_#adc6ff]" id="timeline-progress"></div>
                    </div>
                    <!-- Step 1 -->
                    <div class="timeline-step relative flex flex-col md:flex-row items-start md:items-center justify-between mb-stack-lg fade-in-up pl-12 md:pl-0">
                        <div class="md:w-[45%] text-left md:text-right pr-0 md:pr-stack-md order-2 md:order-1 mt-4 md:mt-0">
                            <h3 class="font-headline-sm text-headline-sm text-on-surface mb-1">Phase 1: Read &amp; Analyze</h3>
                            <p class="font-body-md text-body-md text-on-surface-variant">Tackle curated, high-academic-level texts designed to stress-test your comprehension under simulated conditions.</p>
                        </div>
                        <div class="step-dot absolute left-0 md:left-1/2 -translate-x-1/2 w-8 h-8 rounded-full bg-surface border-2 border-primary flex items-center justify-center z-10 shadow-[0_0_15px_rgba(173,198,255,0.4)] order-1 md:order-2 transition-colors duration-500">
                            <div class="w-3 h-3 bg-primary rounded-full"></div>
                        </div>
                        <div class="md:w-[45%] order-3 hidden md:block"></div>
                    </div>
                    <!-- Step 2 -->
                    <div class="timeline-step relative flex flex-col md:flex-row items-start md:items-center justify-between mb-stack-lg fade-in-up pl-12 md:pl-0">
                        <div class="md:w-[45%] order-1 hidden md:block"></div>
                        <div class="step-dot absolute left-0 md:left-1/2 -translate-x-1/2 w-8 h-8 rounded-full bg-surface border-2 border-surface-container flex items-center justify-center z-10 order-1 md:order-2 transition-colors duration-500">
                            <div class="w-3 h-3 bg-surface-container rounded-full transition-colors duration-500"></div>
                        </div>
                        <div class="md:w-[45%] text-left pl-0 md:pl-stack-md order-2 md:order-3 mt-4 md:mt-0">
                            <h3 class="font-headline-sm text-headline-sm text-on-surface mb-1">Phase 2: Conquer the Quizzes</h3>
                            <p class="font-body-md text-body-md text-on-surface-variant">Engage with question types algorithmically weighted to match the exact distribution of the official exam.</p>
                        </div>
                    </div>
                    <!-- Step 3 -->
                    <div class="timeline-step relative flex flex-col md:flex-row items-start md:items-center justify-between fade-in-up pl-12 md:pl-0">
                        <div class="md:w-[45%] text-left md:text-right pr-0 md:pr-stack-md order-2 md:order-1 mt-4 md:mt-0">
                            <h3 class="font-headline-sm text-headline-sm text-on-surface mb-1 text-tertiary">Phase 3: Break the Locks</h3>
                            <p class="font-body-md text-body-md text-on-surface-variant">Achieve target accuracy to unlock the next level. Gain XP, track streaks, and face the test simulations.</p>
                        </div>
                        <div class="step-dot absolute left-0 md:left-1/2 -translate-x-1/2 w-8 h-8 rounded-full bg-surface border-2 border-surface-container flex items-center justify-center z-10 order-1 md:order-2 transition-colors duration-500">
                            <span class="material-symbols-outlined text-[16px] text-surface-container transition-colors duration-500">lock_open</span>
                        </div>
                        <div class="md:w-[45%] order-3 hidden md:block"></div>
                    </div>
                </div>
            </section>

            <!-- Bottom CTA -->
            <section class="py-stack-lg fade-in-up relative">
                <!-- CTA Background Glow -->
                <div class="absolute inset-0 bg-[#8b5cf6]/10 blur-[100px] pointer-events-none z-[-1] rounded-2xl"></div>
                <div class="w-full glass-panel rounded-2xl p-stack-lg text-center relative overflow-hidden border-t border-primary/40">
                    <!-- Inner glow -->
                    <div class="absolute inset-0 bg-gradient-to-b from-primary/10 to-transparent z-0 pointer-events-none"></div>
                    <div class="relative z-10 flex flex-col items-center">
                        <span class="material-symbols-outlined text-display-lg text-primary mb-4 drop-shadow-[0_0_15px_rgba(173,198,255,0.6)]">local_fire_department</span>
                        <h2 class="font-display-lg text-headline-md md:text-display-lg text-on-surface mb-stack-sm">Your first reading challenge is waiting.</h2>
                        <p class="font-body-lg text-body-lg text-on-surface-variant max-w-lg mb-stack-md">Join thousands of international students upgrading their academic preparation protocols.</p>
                        <a href="<?= $target_link ?>" class="inline-block px-8 py-4 font-headline-sm text-headline-sm bg-gradient-to-br from-[#a3e635] to-[#3b82f6] text-white border-none rounded-lg hover:drop-shadow-[0_0_20px_rgba(173,198,255,0.6)] hover:scale-105 transition-all animate-pulse-glow mt-4">
                            <?= $button_text ?>
                        </a>
                    </div>
                </div>
            </section>
        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-surface-container-lowest w-full bg-gradient-to-t from-primary/10 to-transparent border-t border-outline-variant relative z-10">
        <div class="max-w-container-max mx-auto py-stack-lg px-margin-mobile md:px-margin-desktop text-center">
            <div class="logo mb-2" style="font-size: 20px;">ReadQuest</div>
            <p class="font-body-md text-body-md text-on-surface-variant text-sm">© <?= date("Y"); ?> ReadQuest. All rights reserved.</p>
        </div>
    </footer>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // Scroll Reveal Logic using IntersectionObserver
            const observerOptions = {
                root: null,
                rootMargin: '0px 0px -50px 0px',
                threshold: 0.15
            };

            const revealObserver = new IntersectionObserver((entries, observer) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('visible');
                        observer.unobserve(entry.target); // Only animate once
                    }
                });
            }, observerOptions);

            document.querySelectorAll('.fade-in-up').forEach((elem) => {
                revealObserver.observe(elem);
            });

            // Advanced Timeline Scroll Logic
            const timelineContainer = document.getElementById('timeline-container');
            const timelineProgress = document.getElementById('timeline-progress');
            const timelineSteps = document.querySelectorAll('.timeline-step');
            const dots = document.querySelectorAll('.step-dot');

            if(timelineContainer && timelineProgress) {
                window.addEventListener('scroll', () => {
                    const containerRect = timelineContainer.getBoundingClientRect();
                    const viewportHeight = window.innerHeight;
                    
                    // Calculate how much of the timeline container has been scrolled past the middle of the screen
                    const triggerPoint = viewportHeight * 0.6; 
                    const containerTop = containerRect.top;
                    const containerHeight = containerRect.height;
                    
                    let progress = 0;
                    if (containerTop < triggerPoint) {
                        progress = ((triggerPoint - containerTop) / containerHeight) * 100;
                    }
                    
                    progress = Math.min(Math.max(progress, 0), 100);
                    timelineProgress.style.height = progress + "%";

                    // Animate dots based on progress
                    timelineSteps.forEach((step, index) => {
                        const stepRect = step.getBoundingClientRect();
                        const dot = dots[index];
                        const dotInner = dot.querySelector('div, span');
                        
                        if (stepRect.top < triggerPoint) {
                            if (index === 0) {
                                // Already styled active in HTML, just ensure it stays
                            } else if (index === 1) {
                                dot.classList.remove('border-surface-container');
                                dot.classList.add('border-secondary', 'shadow-[0_0_15px_rgba(255,185,95,0.4)]');
                                if(dotInner) {
                                    dotInner.classList.remove('bg-surface-container');
                                    dotInner.classList.add('bg-secondary');
                                }
                            } else if (index === 2) {
                                dot.classList.remove('border-surface-container');
                                dot.classList.add('border-tertiary', 'shadow-[0_0_15px_rgba(152,218,39,0.4)]');
                                if(dotInner) {
                                    dotInner.classList.remove('text-surface-container');
                                    dotInner.classList.add('text-tertiary');
                                }
                            }
                        }
                    });
                }, { passive: true });
            }

            // Typewriter Effect Logic
            const text = "Stop guessing your reading performance.<br>Start mastering it.";
            const typedTextSpan = document.getElementById("typewriter-text");
            let i = 0;
            
            function typeWriter() {
                if (i < text.length) {
                    if (text.substring(i, i+4) === "<br>") {
                        typedTextSpan.innerHTML += "<br>";
                        i += 4;
                    } else {
                        typedTextSpan.innerHTML += text.charAt(i);
                        i++;
                    }
                    setTimeout(typeWriter, 60); // Typing speed
                }
            }
            
            // Start animation
            setTimeout(typeWriter, 500); // Delay before starting

            // Horizontal Scroll on Vertical Scroll Logic
            const scrollSection = document.getElementById('horizontal-scroll-section');
            const scrollTrack = document.getElementById('horizontal-scroll-track');
            
            if (scrollSection && scrollTrack) {
                window.addEventListener('scroll', () => {
                    const rect = scrollSection.getBoundingClientRect();
                    const stickyTop = 100; // Match top-[100px] offset
                    
                    // The distance we can scroll vertically before the container ends
                    const scrollDistance = rect.height - window.innerHeight;
                    
                    // How far we have scrolled past the sticky point
                    let scrollProgress = -rect.top + stickyTop;
                    
                    // Clamp progress between 0 and scrollDistance
                    scrollProgress = Math.max(0, Math.min(scrollProgress, scrollDistance));
                    
                    // The maximum distance the track can slide to the left
                    // We use the parent container's width, which is the actual visible area
                    const visibleWidth = scrollTrack.parentElement.clientWidth;
                    const maxTranslate = Math.max(0, scrollTrack.scrollWidth - visibleWidth); 
                    
                    // Calculate translation percentage and value
                    const percentage = scrollProgress / scrollDistance;
                    const translateX = maxTranslate * percentage;
                    
                    scrollTrack.style.transform = `translateX(-${translateX}px)`;
                }, { passive: true });
            }
        });
    </script>
</body>
</html>