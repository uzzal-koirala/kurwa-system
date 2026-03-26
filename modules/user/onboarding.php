<?php
include '../../includes/core/config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$full_name = $_SESSION['full_name'];

// Handle onboarding submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['complete_onboarding'])) {
    $hospital_id = intval($_POST['hospital_id']);
    $location_id = intval($_POST['location_id']);

    if ($hospital_id > 0 && $location_id > 0) {
        $stmt = $conn->prepare("UPDATE users SET hospital_id = ?, location_id = ? WHERE id = ?");
        $stmt->bind_param("iii", $hospital_id, $location_id, $user_id);

        if ($stmt->execute()) {
            $_SESSION['hospital_id'] = $hospital_id;
            $_SESSION['location_id'] = $location_id;
            $_SESSION['onboarding_complete'] = true;
            header("Location: user_dashboard.php");
            exit;
        }
    }
}

// Fetch locations
$locations = $conn->query("SELECT * FROM locations ORDER BY name ASC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to Kurwa - Onboarding</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.5.0/fonts/remixicon.css" rel="stylesheet">
    <style>
        body { 
            font-family: 'Poppins', sans-serif; 
            background: radial-gradient(circle at top left, #eef2ff 0%, #f8fafc 50%, #f1f5f9 100%);
            min-height: 100vh;
        }
        .glass-card {
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.5);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.08);
        }
        .step-container {
            animation: slideUp 0.6s cubic-bezier(0.16, 1, 0.3, 1);
        }
        @keyframes slideUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .selection-tile {
            transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
            border: 2px solid transparent; /* Default transparent border */
            background: rgba(255, 255, 255, 0.5); /* Default background */
            backdrop-filter: blur(5px); /* Default backdrop blur */
        }
        .selection-tile:hover {
            transform: translateY(-3px);
            background: #fff;
            border-color: rgba(79, 70, 229, 0.3);
            box-shadow: 0 10px 20px -5px rgba(0, 0, 0, 0.05);
        }
        .peer:checked + .selection-tile {
            border: 2px solid var(--primary) !important;
            background: #fff;
            transform: translateY(-2px);
            box-shadow: 0 15px 25px -5px rgba(79, 70, 229, 0.15);
        }
        .progress-glow {
            box-shadow: 0 0 15px rgba(47, 60, 255, 0.4);
        }
        .custom-scrollbar::-webkit-scrollbar { width: 5px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }
        .checkmark {
            opacity: 0;
            transform: scale(0.5);
            transition: all 0.3s ease;
        }
        .peer:checked + .selection-tile .checkmark {
            opacity: 1;
            transform: scale(1);
        }
    </style>
</head>
<body class="min-h-screen flex flex-col items-center justify-center p-6">

    <div class="max-w-4xl w-full glass-card rounded-2xl md:rounded-[2.5rem] overflow-hidden m-4">
        <!-- Header -->
        <div class="bg-gradient-to-br from-[#2F3CFF] to-[#1E29B1] p-6 md:p-10 text-white text-center relative overflow-hidden">
            <div class="absolute top-0 left-0 w-full h-full opacity-10">
                <i class="ri-hospital-line text-7xl md:text-9xl absolute -bottom-10 -right-10 transform -rotate-12"></i>
            </div>
            <h1 class="text-2xl md:text-4xl font-bold mb-3 relative">Welcome, <?php echo explode(' ', $full_name)[0]; ?>!</h1>
            <p class="text-blue-100 text-sm md:text-lg relative font-light">Let's personalize your healthcare experience.</p>
        </div>

        <div class="p-4 md:p-8">
            <!-- Progress Bar -->
            <div class="flex items-center justify-center gap-2 md:gap-4 mb-8 md:mb-12">
                <div class="flex flex-col items-center gap-1 md:gap-2">
                    <div id="prog-1" class="w-8 h-8 md:w-10 md:h-10 rounded-xl md:rounded-2xl bg-[#2F3CFF] text-white flex items-center justify-center font-bold text-xs md:text-sm shadow-lg shadow-blue-200">1</div>
                    <span class="text-[8px] md:text-[10px] font-semibold text-blue-600 uppercase tracking-wider">Location</span>
                </div>
                <div class="h-1 w-8 md:w-12 bg-gray-100 rounded-full overflow-hidden relative">
                    <div id="prog-bar-1" class="h-full bg-[#2F3CFF] w-0 transition-all duration-700 ease-out"></div>
                </div>
                <div class="flex flex-col items-center gap-1 md:gap-2">
                    <div id="prog-2" class="w-8 h-8 md:w-10 md:h-10 rounded-xl md:rounded-2xl bg-gray-100 text-gray-400 flex items-center justify-center font-bold text-xs md:text-sm">2</div>
                    <span class="text-[8px] md:text-[10px] font-semibold text-gray-400 uppercase tracking-wider">Hospital</span>
                </div>
                <div class="h-1 w-8 md:w-12 bg-gray-100 rounded-full overflow-hidden relative">
                    <div id="prog-bar-2" class="h-full bg-[#2F3CFF] w-0 transition-all duration-700 ease-out"></div>
                </div>
                <div class="flex flex-col items-center gap-1 md:gap-2">
                    <div id="prog-3" class="w-8 h-8 md:w-10 md:h-10 rounded-xl md:rounded-2xl bg-gray-100 text-gray-400 flex items-center justify-center font-bold text-xs md:text-sm">3</div>
                    <span class="text-[8px] md:text-[10px] font-semibold text-gray-400 uppercase tracking-wider">Services</span>
                </div>
            </div>

            <form id="onboardingForm" method="POST">
                <!-- Step 1: Location -->
                <div id="step-1" class="step-container">
                    <h2 class="text-xl md:text-2xl font-bold mb-6 md:mb-8 text-gray-800 text-center">Where are you located?</h2>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 md:gap-6 p-2">
                        <?php while ($loc = $locations->fetch_assoc()): ?>
                            <label class="relative block cursor-pointer group">
                                <input type="radio" name="location_id" value="<?php echo $loc['id']; ?>" class="peer hidden" onchange="enableNext(1)">
                                <div class="selection-tile p-5 md:p-8 rounded-2xl flex items-center gap-4">
                                    <div class="w-12 h-12 md:w-14 md:h-14 rounded-xl bg-indigo-50 text-indigo-600 group-hover:bg-indigo-600 group-hover:text-white transition-all duration-500 flex items-center justify-center">
                                        <i class="ri-compass-3-line text-2xl md:text-3xl"></i>
                                    </div>
                                    <div class="flex-1">
                                        <span class="block font-semibold text-gray-800 text-lg"><?php echo $loc['name']; ?></span>
                                        <span class="text-sm text-gray-500">Service Area</span>
                                    </div>
                                    <div class="checkmark w-6 h-6 rounded-full bg-[#2F3CFF] text-white flex items-center justify-center">
                                        <i class="ri-check-line font-bold"></i>
                                    </div>
                                </div>
                            </label>
                        <?php
endwhile; ?>
                    </div>
                </div>

                <!-- Step 2: Hospital -->
                <div id="step-2" class="step-container hidden">
                    <h2 class="text-2xl font-bold mb-8 text-gray-800 text-center">Which hospital are you at?</h2>
                    <div id="hospitalList" class="grid grid-cols-1 gap-4 max-h-[25rem] overflow-y-auto pr-3 custom-scrollbar" onchange="enableNext(2)">
                        <!-- Loaded via AJAX -->
                    </div>
                </div>

                <!-- Step 3: Service Preview -->
                <div id="step-3" class="step-container hidden">
                    <h2 class="text-2xl font-bold mb-8 text-gray-800 text-center">Services Available for You</h2>
                    <div id="servicePreview" class="grid grid-cols-1 gap-4">
                        <!-- Loaded via AJAX -->
                        <div class="animate-pulse flex flex-col gap-4">
                            <div class="h-24 bg-gray-100 rounded-3xl"></div>
                            <div class="h-24 bg-gray-100 rounded-3xl"></div>
                            <div class="h-24 bg-gray-100 rounded-3xl"></div>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="mt-8 md:mt-12 flex flex-col sm:flex-row justify-between items-center bg-gray-50/50 -mx-4 -mb-4 md:-mx-8 md:-mb-8 p-4 md:p-8 border-t border-gray-100 gap-4">
                    <button type="button" id="prevBtn" onclick="prevStep()" class="hidden px-6 md:px-8 py-3 md:py-4 text-gray-500 font-bold hover:text-gray-900 transition-colors flex items-center gap-2">
                        <i class="ri-arrow-left-s-line text-xl"></i> Back
                    </button>
                    <div class="flex-1 hidden sm:block"></div>
                    <button type="button" id="nextBtn" disabled onclick="nextStep()" class="w-full sm:w-auto px-10 md:px-12 py-3 md:py-4 bg-[#2F3CFF] text-white rounded-xl md:rounded-2xl font-bold shadow-xl shadow-blue-200 disabled:opacity-40 disabled:shadow-none disabled:cursor-not-allowed transform transition hover:scale-[1.02] active:scale-[0.98] flex items-center justify-center gap-3">
                        Continue <i class="ri-arrow-right-s-line text-xl"></i>
                    </button>
                    <button type="submit" id="submitBtn" name="complete_onboarding" class="hidden w-full sm:w-auto px-10 md:px-12 py-3 md:py-4 bg-gradient-to-r from-[#2F3CFF] to-[#1E29B1] text-white rounded-xl md:rounded-2xl font-bold shadow-xl shadow-blue-200 transform transition hover:scale-[1.02] active:scale-[0.98] flex items-center justify-center gap-3">
                        Complete Setup <i class="ri-checkbox-circle-line text-xl"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        let currentStep = 1;

        function enableNext(step) {
            document.getElementById('nextBtn').disabled = false;
        }

        async function nextStep() {
            if (currentStep === 1) {
                const locationId = document.querySelector('input[name="location_id"]:checked').value;
                await loadHospitals(locationId);
                
                document.getElementById('step-1').classList.add('hidden');
                document.getElementById('step-2').classList.remove('hidden');
                document.getElementById('prevBtn').classList.remove('hidden');
                document.getElementById('nextBtn').disabled = true;
                
                document.getElementById('prog-bar-1').style.width = '100%';
                setActive(2);
                currentStep = 2;
            } else if (currentStep === 2) {
                const hospitalId = document.querySelector('input[name="hospital_id"]:checked').value;
                await loadServicePreview(hospitalId);

                document.getElementById('step-2').classList.add('hidden');
                document.getElementById('step-3').classList.remove('hidden');
                document.getElementById('nextBtn').classList.add('hidden');
                document.getElementById('submitBtn').classList.remove('hidden');

                document.getElementById('prog-bar-2').style.width = '100%';
                setActive(3);
                currentStep = 3;
            }
        }

        function prevStep() {
            if (currentStep === 2) {
                document.getElementById('step-2').classList.add('hidden');
                document.getElementById('step-1').classList.remove('hidden');
                document.getElementById('prevBtn').classList.add('hidden');
                document.getElementById('nextBtn').disabled = false;
                
                document.getElementById('prog-bar-1').style.width = '0%';
                setInactive(2);
                currentStep = 1;
            } else if (currentStep === 3) {
                document.getElementById('step-3').classList.add('hidden');
                document.getElementById('step-2').classList.remove('hidden');
                document.getElementById('submitBtn').classList.add('hidden');
                document.getElementById('nextBtn').classList.remove('hidden');
                document.getElementById('nextBtn').disabled = false;

                document.getElementById('prog-bar-2').style.width = '0%';
                setInactive(3);
                currentStep = 2;
            }
        }

        function setActive(num) {
            const el = document.getElementById(`prog-${num}`);
            el.classList.remove('bg-gray-100', 'text-gray-400');
            el.classList.add('bg-[#2F3CFF]', 'text-white', 'shadow-lg', 'shadow-blue-200');
            const spanEl = el.nextElementSibling.tagName === 'SPAN' ? el.nextElementSibling : el.parentNode.querySelector('span');
            if (spanEl) {
                spanEl.classList.remove('text-gray-400');
                spanEl.classList.add('text-blue-600');
            }
        }

        function setInactive(num) {
            const el = document.getElementById(`prog-${num}`);
            el.classList.add('bg-gray-100', 'text-gray-400');
            el.classList.remove('bg-[#2F3CFF]', 'text-white', 'shadow-lg', 'shadow-blue-200');
            const spanEl = el.nextElementSibling.tagName === 'SPAN' ? el.nextElementSibling : el.parentNode.querySelector('span');
            if (spanEl) {
                spanEl.classList.add('text-gray-400');
                spanEl.classList.remove('text-blue-600');
            }
        }

        async function loadHospitals(locationId) {
            const list = document.getElementById('hospitalList');
            list.innerHTML = `<div class="text-center py-10 text-gray-400"><i class="ri-loader-4-line text-3xl animate-spin block mb-2"></i>Loading...</div>`;
            
            try {
                const response = await fetch(`handlers/get_hospitals.php?location_id=${locationId}`);
                const hospitals = await response.json();
                list.innerHTML = hospitals.map(h => `
                    <label class="relative block cursor-pointer group">
                        <input type="radio" name="hospital_id" value="${h.id}" class="peer hidden" required onchange="enableNext(2)">
                        <div class="selection-tile p-5 md:p-8 rounded-2xl flex items-center gap-4">
                            <div class="w-12 h-12 md:w-14 md:h-14 rounded-xl bg-indigo-50 text-indigo-600 group-hover:bg-indigo-600 group-hover:text-white transition-all duration-500 flex items-center justify-center">
                                <i class="ri-hospital-line text-2xl md:text-3xl"></i>
                            </div>
                            <div class="flex-1">
                                <span class="block font-bold text-slate-800 text-lg md:text-xl">${h.name}</span>
                                <span class="text-sm text-slate-400 font-medium tracking-wide">AVAILABLE FACILITY</span>
                            </div>
                            <div class="checkmark w-8 h-8 rounded-full bg-indigo-600 text-white flex items-center justify-center shadow-lg shadow-indigo-100">
                                <i class="ri-check-line font-bold text-lg"></i>
                            </div>
                        </div>
                    </label>
                `).join('');
            } catch (error) {
                list.innerHTML = '<div class="text-center py-10 text-red-400">Error loading.</div>';
            }
        }

        async function loadServicePreview(hospitalId) {
            const preview = document.getElementById('servicePreview');
            preview.innerHTML = `<div class="text-center py-10 text-gray-400"><i class="ri-loader-4-line text-3xl animate-spin block mb-2"></i>Loading Services...</div>`;
            
            try {
                const response = await fetch(`handlers/get_hospital_services.php?hospital_id=${hospitalId}`);
                const data = await response.json();
                
                if (data.success) {
                    const counts = data.counts;
                    preview.innerHTML = `
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 w-full">
                            <!-- Caretakers -->
                            <div class="p-6 bg-rose-50/40 border border-rose-100 rounded-2xl flex items-center gap-6 shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-500 group relative">
                                <div class="absolute top-0 left-0 w-1 bg-rose-500 h-full rounded-l-2xl"></div>
                                <div class="w-16 h-16 shrink-0 rounded-2xl bg-white text-rose-500 flex items-center justify-center group-hover:bg-rose-500 group-hover:text-white transition-colors duration-500 shadow-sm">
                                    <i class="ri-heart-pulse-line text-3xl"></i>
                                </div>
                                <div class="text-left">
                                    <span class="block font-black text-slate-800 text-4xl leading-none mb-1">${counts.caretakers}</span>
                                    <span class="text-sm text-slate-400 font-bold uppercase tracking-wider">Caretakers</span>
                                </div>
                            </div>

                            <!-- Pharmacies -->
                            <div class="p-6 bg-indigo-50/40 border border-indigo-100 rounded-2xl flex items-center gap-6 shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-500 group relative">
                                <div class="absolute top-0 left-0 w-1 bg-indigo-500 h-full rounded-l-2xl"></div>
                                <div class="w-16 h-16 shrink-0 rounded-2xl bg-white text-indigo-500 flex items-center justify-center group-hover:bg-indigo-500 group-hover:text-white transition-colors duration-500 shadow-sm">
                                    <i class="ri-capsule-line text-3xl"></i>
                                </div>
                                <div class="text-left">
                                    <span class="block font-black text-slate-800 text-4xl leading-none mb-1">${counts.pharmacies}</span>
                                    <span class="text-sm text-slate-400 font-bold uppercase tracking-wider">Pharmacies</span>
                                </div>
                            </div>

                            <!-- Canteens -->
                            <div class="p-6 bg-amber-50/40 border border-amber-100 rounded-2xl flex items-center gap-6 shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-500 group relative">
                                <div class="absolute top-0 left-0 w-1 bg-amber-500 h-full rounded-l-2xl"></div>
                                <div class="w-16 h-16 shrink-0 rounded-2xl bg-white text-amber-500 flex items-center justify-center group-hover:bg-amber-500 group-hover:text-white transition-colors duration-500 shadow-sm">
                                    <i class="ri-restaurant-line text-3xl"></i>
                                </div>
                                <div class="text-left">
                                    <span class="block font-black text-slate-800 text-4xl leading-none mb-1">${counts.food}</span>
                                    <span class="text-sm text-slate-400 font-bold uppercase tracking-wider">Canteens</span>
                                </div>
                            </div>

                            <!-- Emergency -->
                            <div class="p-6 bg-emerald-50/40 border border-emerald-100 rounded-2xl flex items-center gap-6 shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-500 group relative">
                                <div class="absolute top-0 left-0 w-1 bg-emerald-500 h-full rounded-l-2xl"></div>
                                <div class="w-16 h-16 shrink-0 rounded-2xl bg-white text-emerald-500 flex items-center justify-center group-hover:bg-emerald-500 group-hover:text-white transition-colors duration-500 shadow-sm">
                                    <i class="ri-shield-cross-line text-3xl"></i>
                                </div>
                                <div class="text-left">
                                    <span class="block font-black text-slate-800 text-4xl leading-none mb-1">${counts.emergency}</span>
                                    <span class="text-sm text-slate-400 font-bold uppercase tracking-wider">Live Support</span>
                                </div>
                            </div>
                        </div>
                        <p class="text-center text-sm text-slate-400 mt-10 px-8 leading-relaxed font-medium">Everything is ready to support your stay at this facility.</p>
                    `;
                }
            } catch (error) {
                preview.innerHTML = '<div class="text-center py-10 text-red-400">Error loading services.</div>';
            }
        }
    </script>
</body>
</html>
