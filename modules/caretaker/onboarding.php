<?php
require_once '../../includes/core/config.php';
require_once INC_PATH . '/core/auth_check.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'caretaker') {
    header("Location: ../user/login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$full_name = $_SESSION['full_name'] ?? 'Caretaker';
$phone = $_SESSION['phone'] ?? '';

// Check if already completed
$check = $conn->query("SELECT onboarding_completed FROM caretakers WHERE id = $user_id");
$is_completed = $check->fetch_assoc()['onboarding_completed'] ?? 0;

if ($is_completed) {
    header("Location: dashboard.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Expert Onboarding | Kurwa System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.5.0/fonts/remixicon.css" rel="stylesheet">
    <style>
        body { 
            font-family: 'Poppins', sans-serif; 
            background: radial-gradient(circle at top right, #f8fafc 0%, #eef2ff 100%);
            min-height: 100vh;
        }
        .glass-container {
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.6);
            box-shadow: 0 40px 100px -20px rgba(0, 0, 0, 0.1);
        }
        .step-inactive { opacity: 0; display: none; transform: translateY(10px); }
        .step-active { opacity: 1; display: block; animation: slideIn 0.5s ease forwards; }
        @keyframes slideIn { from { opacity: 0; transform: translateY(15px); } to { opacity: 1; transform: translateY(0); } }
        
        .progress-bar { transition: width 0.6s cubic-bezier(0.65, 0, 0.35, 1); }
        .upload-card {
            border: 2px dashed #e2e8f0;
            transition: all 0.3s ease;
        }
        .upload-card:hover { border-color: #2F3CFF; background: #f0f3ff; }
    </style>
</head>
<body class="flex items-center justify-center p-4 py-12 md:p-10">

    <div class="max-w-4xl w-full glass-container rounded-[2.5rem] overflow-hidden">
        <!-- Progress -->
        <div class="h-2 bg-gray-100 w-full relative">
            <div id="progressBar" class="progress-bar h-full bg-[#2F3CFF] w-1/3"></div>
        </div>

        <div class="p-8 md:p-16">
            <header class="mb-12 text-center">
                <div class="inline-flex items-center justify-center w-16 h-16 bg-blue-50 text-[#2F3CFF] rounded-2xl mb-6">
                    <i class="ri-user-star-line text-3xl"></i>
                </div>
                <h1 class="text-3xl md:text-4xl font-bold text-gray-800">Complete Your Expert Profile</h1>
                <p class="text-gray-500 mt-2 mb-10">Help us verify your expertise to start receiving bookings.</p>

                <!-- Step Indicators -->
                <div class="flex justify-center items-center gap-4 max-w-xs mx-auto mb-4">
                    <div id="ind1" class="w-10 h-10 rounded-xl bg-[#2F3CFF] text-white flex items-center justify-center font-bold shadow-lg shadow-blue-100 transition-all duration-500">1</div>
                    <div class="h-0.5 w-8 bg-gray-100 rounded-full overflow-hidden">
                        <div id="bar1" class="h-full bg-[#2F3CFF] w-0 transition-all duration-500"></div>
                    </div>
                    <div id="ind2" class="w-10 h-10 rounded-xl bg-gray-50 text-gray-400 flex items-center justify-center font-bold border border-gray-100 transition-all duration-500">2</div>
                    <div class="h-0.5 w-8 bg-gray-100 rounded-full overflow-hidden">
                        <div id="bar2" class="h-full bg-[#2F3CFF] w-0 transition-all duration-500"></div>
                    </div>
                    <div id="ind3" class="w-10 h-10 rounded-xl bg-gray-50 text-gray-400 flex items-center justify-center font-bold border border-gray-100 transition-all duration-500">3</div>
                </div>
            </header>

            <form id="onboardingForm" enctype="multipart/form-data">
                <!-- Step 1: Personal Profile -->
                <div id="step1" class="step-active">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8 items-center">
                        <div class="flex flex-col items-center">
                            <div class="relative group cursor-pointer" onclick="document.getElementById('photoInput').click()">
                                <div id="photoPreview" class="w-40 h-40 rounded-[2rem] bg-gray-50 border-2 border-gray-100 flex items-center justify-center overflow-hidden transition-all group-hover:border-[#2F3CFF]">
                                    <i class="ri-image-add-line text-4xl text-gray-300 group-hover:text-[#2F3CFF]"></i>
                                </div>
                                <div class="absolute -bottom-2 -right-2 bg-[#2F3CFF] text-white w-10 h-10 rounded-xl flex items-center justify-center shadow-lg">
                                    <i class="ri-camera-line"></i>
                                </div>
                                <input type="file" id="photoInput" name="photo" accept="image/*" class="hidden" onchange="previewFile(this, 'photoPreview')" required>
                            </div>
                            <span class="text-xs font-semibold text-gray-400 mt-4 uppercase tracking-widest">Profile Photo</span>
                        </div>
                        
                        <div class="space-y-5">
                            <div>
                                <label class="text-sm font-bold text-gray-700 block mb-2">Display Name</label>
                                <input type="text" name="full_name" value="<?= htmlspecialchars($full_name) ?>" class="w-full bg-gray-50 border border-gray-100 rounded-xl p-4 outline-none focus:ring-2 focus:ring-[#2F3CFF]/10 focus:bg-white transition-all" required>
                            </div>
                            <div>
                                <label class="text-sm font-bold text-gray-700 block mb-2">Contact Number</label>
                                <input type="text" name="phone" value="<?= htmlspecialchars($phone) ?>" class="w-full bg-gray-50 border border-gray-100 rounded-xl p-4 outline-none focus:ring-2 focus:ring-[#2F3CFF]/10 focus:bg-white transition-all" required>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Step 2: Skills & Expertise -->
                <div id="step2" class="step-inactive">
                    <div class="space-y-8">
                        <div>
                            <label class="text-sm font-bold text-gray-700 block mb-3 flex items-center gap-2">
                                <i class="ri-medal-line text-[#2F3CFF]"></i> Skills & Specializations
                            </label>
                            <input type="text" name="skills" placeholder="e.g. Wound Care, Elderly Massage, Post-Op Support" class="w-full bg-gray-50 border border-gray-100 rounded-2xl p-4.5 outline-none focus:ring-2 focus:ring-[#2F3CFF]/10 focus:bg-white transition-all shadow-sm" required>
                            <p class="text-[11px] text-gray-400 mt-2 ml-1 font-medium italic opacity-75">Separate distinct skills with commas.</p>
                        </div>
                        
                        <div>
                            <label class="text-sm font-bold text-gray-700 block mb-3 flex items-center gap-2">
                                <i class="ri-video-chat-line text-[#2F3CFF]"></i> Intro Video (YouTube Link)
                            </label>
                            <input type="url" name="video_url" placeholder="https://www.youtube.com/watch?v=..." class="w-full bg-gray-50 border border-gray-100 rounded-2xl p-4.5 outline-none focus:ring-2 focus:ring-[#2F3CFF]/10 focus:bg-white transition-all shadow-sm">
                            <p class="text-[11px] text-gray-400 mt-2 ml-1 font-medium italic opacity-75">A short video explaining your experience builds trust with patients.</p>
                        </div>

                        <div>
                            <label class="text-sm font-bold text-gray-700 block mb-3 flex items-center gap-2">
                                <i class="ri-article-line text-[#2F3CFF]"></i> Detailed Expertise & Bio
                            </label>
                            <textarea name="expertise" rows="5" placeholder="Share your professional journey and how you provide compassionate care..." class="w-full bg-gray-50 border border-gray-100 rounded-2xl p-4.5 outline-none focus:ring-2 focus:ring-[#2F3CFF]/10 focus:bg-white transition-all shadow-sm" required></textarea>
                        </div>
                    </div>
                </div>

                <!-- Step 3: Document Verification -->
                <div id="step3" class="step-inactive text-center">
                    <div class="mb-8">
                        <i class="ri-shield-check-fill text-6xl text-emerald-500 mb-4 inline-block"></i>
                        <h3 class="text-xl font-bold text-gray-800">Verification Document</h3>
                        <p class="text-gray-500">Please upload your original citizenship or professional ID.</p>
                    </div>

                    <div class="upload-card rounded-3xl p-10 cursor-pointer text-center relative" id="docDrop" onclick="document.getElementById('docInput').click()">
                        <div id="docPreview" class="hidden flex flex-col items-center">
                            <i class="ri-file-check-line text-5xl text-emerald-500 mb-2"></i>
                            <span id="docName" class="text-sm font-semibold text-gray-700">document.pdf</span>
                        </div>
                        <div id="docPlaceholder" class="flex flex-col items-center">
                            <i class="ri-upload-2-line text-4xl text-gray-300 mb-2"></i>
                            <p class="text-sm font-medium text-gray-500">Click to upload PDF or Image</p>
                        </div>
                        <input type="file" id="docInput" name="document" accept="image/*,application/pdf" class="hidden" onchange="previewDoc(this)" required>
                    </div>
                    <p class="text-[10px] text-gray-400 mt-4 uppercase tracking-tighter">Only Admin can view your original documents</p>
                </div>

                <!-- Navigation -->
                <div class="mt-16 flex justify-between items-center bg-gray-50/50 -m-8 md:-m-16 p-8 md:p-12 border-t border-gray-100">
                    <button type="button" id="prevBtn" onclick="moveStep(-1)" class="hidden text-gray-500 font-bold hover:text-black transition-all flex items-center gap-2">
                        <i class="ri-arrow-left-line"></i> Back
                    </button>
                    <div class="flex-1"></div>
                    <button type="button" id="nextBtn" onclick="moveStep(1)" class="bg-[#2F3CFF] text-white px-10 py-5 rounded-2xl font-bold shadow-xl shadow-blue-200 hover:scale-[1.02] active:scale-[0.98] transition-all flex items-center gap-3">
                        Continue <i class="ri-arrow-right-line"></i>
                    </button>
                    <button type="submit" id="submitBtn" class="hidden bg-gradient-to-r from-emerald-500 to-teal-600 text-white px-10 py-5 rounded-2xl font-bold shadow-xl shadow-emerald-100 hover:scale-[1.02] active:scale-[0.98] transition-all flex items-center gap-3">
                         Submit Profile <i class="ri-checkbox-circle-fill"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        let currentStep = 1;
        const totalSteps = 3;

        function moveStep(dir) {
            // Validation
            if(dir === 1 && !validateStep(currentStep)) return;

            document.getElementById(`step${currentStep}`).classList.replace('step-active', 'step-inactive');
            currentStep += dir;
            document.getElementById(`step${currentStep}`).classList.replace('step-inactive', 'step-active');

            updateUI();
        }

        function validateStep(step) {
            const container = document.getElementById(`step${step}`);
            const inputs = container.querySelectorAll('input, textarea');
            for(let input of inputs) {
                if(!input.checkValidity()) {
                    input.reportValidity();
                    return false;
                }
            }
            return true;
        }

        function updateUI() {
            // Bar
            document.getElementById('progressBar').style.width = (currentStep / totalSteps * 100) + '%';
            
            // Step Indicators
            for(let i=1; i<=totalSteps; i++) {
                const ind = document.getElementById(`ind${i}`);
                const bar = document.getElementById(`bar${i-1}`);
                
                if(i < currentStep) {
                    ind.className = "w-10 h-10 rounded-xl bg-blue-100 text-[#2F3CFF] flex items-center justify-center font-bold shadow-none transition-all duration-500";
                    ind.innerHTML = '<i class="ri-check-line"></i>';
                    if(bar) bar.style.width = '100%';
                } else if(i === currentStep) {
                    ind.className = "w-10 h-10 rounded-xl bg-[#2F3CFF] text-white flex items-center justify-center font-bold shadow-lg shadow-blue-100 transition-all duration-500 scale-110";
                    ind.innerHTML = i;
                    if(bar) bar.style.width = '0%';
                } else {
                    ind.className = "w-10 h-10 rounded-xl bg-gray-50 text-gray-400 flex items-center justify-center font-bold border border-gray-100 transition-all duration-500";
                    ind.innerHTML = i;
                    if(bar) bar.style.width = '0%';
                }
            }

            // Buttons
            document.getElementById('prevBtn').classList.toggle('hidden', currentStep === 1);
            document.getElementById('nextBtn').classList.toggle('hidden', currentStep === totalSteps);
            document.getElementById('submitBtn').classList.toggle('hidden', currentStep !== totalSteps);
        }

        function previewFile(input, targetId) {
            const preview = document.getElementById(targetId);
            const file = input.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = (e) => {
                    preview.innerHTML = `<img src="${e.target.result}" class="w-full h-full object-cover">`;
                };
                reader.readAsDataURL(file);
            }
        }

        function previewDoc(input) {
            const preview = document.getElementById('docPreview');
            const placeholder = document.getElementById('docPlaceholder');
            const nameEl = document.getElementById('docName');
            
            if(input.files[0]) {
                const name = input.files[0].name;
                nameEl.innerText = name;
                placeholder.classList.add('hidden');
                preview.classList.remove('hidden');
            }
        }

        document.getElementById('onboardingForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            
            // Show loading
            const btn = document.getElementById('submitBtn');
            const originalContent = btn.innerHTML;
            btn.innerHTML = `<i class="ri-loader-4-line animate-spin"></i> Processing...`;
            btn.disabled = true;

            try {
                const response = await fetch('api/complete_onboarding.php', {
                    method: 'POST',
                    body: formData
                });
                const result = await response.json();
                
                if(result.success) {
                    window.location.href = 'dashboard.php';
                } else {
                    alert('Error: ' + result.message);
                    btn.innerHTML = originalContent;
                    btn.disabled = false;
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Submission failed. Please check your files and try again.');
                btn.innerHTML = originalContent;
                btn.disabled = false;
            }
        });
    </script>
</body>
</html>
