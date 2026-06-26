<?php
session_start();
if (isset($_SESSION['user_id'])) {
    header("Location: map_dashboard.php");
    exit();
}
$error = isset($_SESSION['login_error']) ? $_SESSION['login_error'] : '';
unset($_SESSION['login_error']);
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เข้าสู่ระบบ - CRIME MAP</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <style>
        body { 
            font-family: 'Kanit', sans-serif;
            background-color: #0f172a; /* slate-900 */
            background-image: 
                radial-gradient(circle at 15% 50%, rgba(14, 165, 233, 0.08), transparent 25%),
                radial-gradient(circle at 85% 30%, rgba(2, 132, 199, 0.08), transparent 25%);
        }
        
        /* Hi-tech grid background overlay */
        .cyber-grid {
            position: absolute;
            inset: 0;
            background-size: 40px 40px;
            background-image: 
                linear-gradient(to right, rgba(255, 255, 255, 0.02) 1px, transparent 1px),
                linear-gradient(to bottom, rgba(255, 255, 255, 0.02) 1px, transparent 1px);
            z-index: 0;
            pointer-events: none;
        }

        .glass-panel {
            background: rgba(30, 41, 59, 0.7); /* slate-800 with opacity */
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(56, 189, 248, 0.2); /* sky-400 border */
            box-shadow: 0 0 40px rgba(2, 132, 199, 0.2), inset 0 0 20px rgba(255, 255, 255, 0.02);
        }

        .input-cyber {
            background: rgba(15, 23, 42, 0.8);
            border: 1px solid rgba(71, 85, 105, 0.5);
            color: #f8fafc;
            transition: all 0.3s ease;
        }
        
        .input-cyber:focus {
            border-color: #38bdf8;
            box-shadow: 0 0 15px rgba(56, 189, 248, 0.3);
            outline: none;
        }

        .btn-cyber {
            background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%);
            border: 1px solid #38bdf8;
            box-shadow: 0 0 15px rgba(2, 132, 199, 0.4);
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .btn-cyber:hover {
            box-shadow: 0 0 25px rgba(56, 189, 248, 0.6);
            transform: translateY(-1px);
        }

        .btn-cyber::after {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: linear-gradient(to right, transparent, rgba(255,255,255,0.1), transparent);
            transform: rotate(45deg);
            transition: 0.5s;
            opacity: 0;
        }

        .btn-cyber:hover::after {
            animation: shine 1.5s infinite;
            opacity: 1;
        }

        @keyframes shine {
            0% { left: -50%; }
            100% { left: 100%; }
        }

        .gold-accent {
            color: #fbbf24; /* amber-400 */
            text-shadow: 0 0 10px rgba(251, 191, 36, 0.3);
        }
        
        .cyan-accent {
            color: #38bdf8;
            text-shadow: 0 0 10px rgba(56, 189, 248, 0.4);
        }
    </style>

    <!-- PWA Meta Tags -->
    <link rel="manifest" href="manifest.json">
    <meta name="theme-color" content="#0f172a">
    <link rel="apple-touch-icon" href="icons/icon-192.png">
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('sw.js');
            });
        }
    </script>
</head>
<body class="flex items-center justify-center min-h-screen relative overflow-hidden text-slate-200">
    
    <!-- Cyber Grid Background -->
    <div class="cyber-grid"></div>

    <div class="glass-panel p-8 sm:p-10 rounded-2xl w-full max-w-lg z-10 relative mt-4 mb-4 mx-4 border-t-4 border-t-sky-500">
        
        <!-- Header / Logo -->
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-slate-900 border-2 border-sky-500 shadow-[0_0_20px_rgba(56,189,248,0.5)] mb-5">
                <i class="fas fa-shield-alt text-4xl gold-accent"></i>
            </div>
            <h1 class="text-xl sm:text-2xl font-bold text-white leading-snug">
                ศูนย์ข้อมูลเพื่อการบูรณาการ<br>
                <span class="cyan-accent">ป้องกันปราบปรามอาชญากรรม</span><br>
                <span class="text-lg text-slate-300 font-medium">จังหวัดพัทลุง</span>
            </h1>
            <div class="flex items-center justify-center gap-2 mt-4">
                <div class="h-px bg-slate-700 w-12"></div>
                <p class="text-xs font-bold text-red-500 tracking-widest uppercase border border-red-500/50 px-2 py-0.5 rounded bg-red-500/10 shadow-[0_0_10px_rgba(239,68,68,0.3)]">Demo Version</p>
                <div class="h-px bg-slate-700 w-12"></div>
            </div>
        </div>

        <!-- Login Form -->
        <form action="login_action.php" method="POST" class="space-y-6">
            <div>
                <label for="username" class="block text-xs font-medium text-sky-400 uppercase tracking-wider mb-2">
                    <i class="fas fa-user-shield mr-1"></i> รหัสประจำตัว (Username)
                </label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-user text-slate-500"></i>
                    </div>
                    <input type="text" name="username" id="username" required autocomplete="off"
                           class="input-cyber block w-full pl-10 pr-4 py-3 rounded-lg focus:ring-0 sm:text-sm">
                </div>
            </div>

            <div>
                <label for="password" class="block text-xs font-medium text-sky-400 uppercase tracking-wider mb-2">
                    <i class="fas fa-key mr-1"></i> รหัสผ่าน (Password)
                </label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-lock text-slate-500"></i>
                    </div>
                    <input type="password" name="password" id="password" required 
                           class="input-cyber block w-full pl-10 pr-4 py-3 rounded-lg focus:ring-0 sm:text-sm">
                </div>
            </div>

            <div class="pt-2">
                <button type="submit" class="btn-cyber w-full flex justify-center py-3.5 px-4 rounded-lg font-bold text-white sm:text-sm">
                    <i class="fas fa-sign-in-alt mr-2 mt-0.5"></i> เข้าสู่ระบบปฏิบัติการ
                </button>
            </div>
        </form>

        <!-- Demo Accounts Info -->
        <div class="mt-8 pt-5 border-t border-slate-700/50">
            <div class="bg-slate-900/50 rounded-lg p-4 border border-slate-700/50">
                <p class="mb-3 text-xs font-bold text-slate-400 uppercase tracking-wider flex items-center">
                    <i class="fas fa-vial mr-2 text-amber-500"></i> บัญชีทดสอบระบบ (Demo Accounts)
                </p>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                    <div class="flex flex-col bg-slate-800/80 p-2 rounded border border-slate-700">
                        <span class="text-sky-300 font-mono">admin / admin</span>
                        <span class="text-slate-400 mt-1">Super Admin</span>
                    </div>
                    <div class="flex flex-col bg-slate-800/80 p-2 rounded border border-slate-700">
                        <span class="text-sky-300 font-mono">governor / governor</span>
                        <span class="text-slate-400 mt-1">ผู้ว่าฯ (ดูภาพรวม)</span>
                    </div>
                    <div class="flex flex-col bg-slate-800/80 p-2 rounded border border-slate-700">
                        <span class="text-sky-300 font-mono">chief_mueang / chief_mueang</span>
                        <span class="text-slate-400 mt-1">นายอำเภอ (อัปเดตสถานะ)</span>
                    </div>
                    <div class="flex flex-col bg-slate-800/80 p-2 rounded border border-slate-700">
                        <span class="text-sky-300 font-mono">officer_mueang / officer_mueang</span>
                        <span class="text-slate-400 mt-1">เจ้าหน้าที่ (แจ้งจุดเสี่ยง)</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Back to Public -->
        <div class="mt-6">
            <a href="index.php" class="group w-full flex justify-center items-center py-3 px-4 rounded-lg text-sm font-medium text-slate-300 bg-slate-800 border border-slate-700 hover:bg-slate-700 hover:text-white transition-all">
                <i class="fas fa-globe mr-2 text-slate-400 group-hover:text-sky-400 transition-colors"></i> กลับสู่หน้าสถิติประชาชน
            </a>
        </div>

        <div class="mt-6 text-center text-xs text-slate-500">
            ระบบปฏิบัติการบูรณาการข้อมูล พัฒนาโดย <span class="font-bold text-sky-400">จังหวัดพัทลุง</span>
        </div>
    </div>

    <!-- Error Alert -->
    <?php if ($error): ?>
    <script>
        Swal.fire({
            icon: 'error',
            title: 'การเข้าถึงถูกปฏิเสธ',
            text: <?= json_encode($error) ?>,
            background: '#1e293b',
            color: '#f8fafc',
            confirmButtonColor: '#0ea5e9',
            confirmButtonText: 'รับทราบ',
            customClass: {
                popup: 'border border-slate-700 shadow-[0_0_20px_rgba(220,38,38,0.3)]'
            }
        });
    </script>
    <?php endif; ?>
</body>
</html>
