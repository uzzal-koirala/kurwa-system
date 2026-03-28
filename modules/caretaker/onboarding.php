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
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.4);
            box-shadow: 0 50px 100px -30px rgba(0, 0, 0, 0.1), 0 0 0 1px rgba(255, 255, 255, 0.2) inset;
        }
        .step-inactive { opacity: 0; display: none; transform: translateY(15px); }
        .step-active { opacity: 1; display: block; animation: slideIn 0.8s cubic-bezier(0.16, 1, 0.3, 1) forwards; }
        @keyframes slideIn { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
        
        .progress-bar { transition: width 0.8s cubic-bezier(0.65, 0, 1, 1); }
        .upload-card {
            border: 2px dashed #e2e8f0;
            background: #ffffff;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .upload-card:hover { border-color: #2F3CFF; background: #fdfdff; transform: translateY(-4px); box-shadow: 0 15px 30px rgba(47, 60, 255, 0.08); }
        
        input::placeholder, textarea::placeholder { color: #aaaaaa; font-weight: 400; font-size: 0.95rem; }
        .input-focus { border-color: #2F3CFF !important; box-shadow: 0 0 0 4px rgba(47, 60, 255, 0.08) !important; background: #ffffff !important; }
    </style>
</head>
<body class="flex items-center justify-center p-4 py-12 md:p-10">

    <div class="max-w-lg w-full glass-container rounded-[2rem] overflow-hidden">
        <!-- Progress -->
        <div class="h-2 bg-gray-100 w-full relative">
            <div id="progressBar" class="progress-bar h-full bg-[#2F3CFF] w-1/3"></div>
        </div>

        <div class="p-6 md:px-8 md:py-4">
            <header class="mb-4 text-center">
                <div class="inline-flex items-center justify-center w-10 h-10 bg-blue-50 text-[#2F3CFF] rounded-lg mb-2 shadow-inner ring-1 ring-blue-100">
                    <i class="ri-user-star-line text-lg"></i>
                </div>
                <h1 class="text-xl md:text-2xl font-bold text-gray-900 tracking-tight leading-tight">Complete Your Expert Profile</h1>
                <p class="text-[13px] text-gray-400 mt-1 font-medium italic">Verify your expertise to start receiving bookings.</p>

                <!-- Step Indicators -->
                <div class="flex justify-center items-center gap-2 md:gap-4 max-w-sm mx-auto mb-4 mt-6">
                    <div id="ind1" class="w-10 h-10 rounded-xl bg-[#2F3CFF] text-white flex items-center justify-center font-bold shadow-lg shadow-blue-100 transition-all duration-500">1</div>
                    <div class="h-1 w-6 md:w-8 bg-gray-100 rounded-full overflow-hidden">
                        <div id="bar1" class="h-full bg-[#2F3CFF] w-0 transition-all duration-500"></div>
                    </div>
                    <div id="ind2" class="w-10 h-10 rounded-xl bg-gray-50 text-gray-400 flex items-center justify-center font-bold border border-gray-100 transition-all duration-500">2</div>
                    <div class="h-1 w-6 md:w-8 bg-gray-100 rounded-full overflow-hidden">
                        <div id="bar2" class="h-full bg-[#2F3CFF] w-0 transition-all duration-500"></div>
                    </div>
                    <div id="ind3" class="w-10 h-10 rounded-xl bg-gray-50 text-gray-400 flex items-center justify-center font-bold border border-gray-100 transition-all duration-500">3</div>
                </div>
            </header>

            <form id="onboardingForm" enctype="multipart/form-data">
                <!-- Step 1: Personal Profile -->
                <div id="step1" class="step-active">
                    <div class="space-y-4">
                        <!-- Profile Photo Full Width -->
                        <div class="upload-card rounded-xl p-4 md:p-6 cursor-pointer text-center relative group" id="photoDrop" onclick="document.getElementById('photoInput').click()">
                            <div id="photoPreview" class="hidden flex flex-col items-center">
                                <div class="w-20 h-20 rounded-full overflow-hidden border-2 border-white shadow-lg mb-2">
                                    <img id="photoImg" src="" class="w-full h-full object-cover">
                                </div>
                                <span class="text-[10px] font-bold text-[#2F3CFF] bg-blue-50 px-2 py-1 rounded-full uppercase tracking-wider">Photo Selected</span>
                            </div>
                            <div id="photoPlaceholder" class="flex flex-col items-center py-1">
                                <div class="w-12 h-12 bg-blue-50 text-[#2F3CFF] rounded-xl flex items-center justify-center mb-2 group-hover:scale-105 transition-transform duration-500">
                                    <i class="ri-user-add-line text-2xl"></i>
                                </div>
                                <h3 class="text-sm font-bold text-gray-800 mb-0.5">Upload Profile Photo</h3>
                                <p class="text-[11px] font-medium text-gray-400">Drag and drop or click to browse</p>
                            </div>
                            <input type="file" id="photoInput" name="photo" accept="image/*" class="hidden" onchange="previewProfilePhoto(this)" required>
                        </div>

                        <!-- Info Grid -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 md:gap-4">
                            <div class="relative group">
                                <i class="ri-user-line absolute left-4 top-1/2 -translate-y-1/2 text-gray-500 text-lg group-focus-within:text-[#2F3CFF] transition-colors"></i>
                                <input type="text" name="full_name" value="<?= htmlspecialchars($full_name) ?>" placeholder="Your Full Name" class="w-full bg-white border border-gray-300 rounded-lg py-3 pl-12 pr-4 outline-none focus:border-[#2F3CFF] focus:ring-2 focus:ring-blue-50 transition-all font-medium text-gray-700 shadow-sm text-sm" required>
                            </div>
                            <div class="relative group">
                                <i class="ri-phone-line absolute left-4 top-1/2 -translate-y-1/2 text-gray-500 text-lg group-focus-within:text-[#2F3CFF] transition-colors"></i>
                                <input type="text" name="phone" value="<?= htmlspecialchars($phone) ?>" placeholder="Contact Number" class="w-full bg-white border border-gray-300 rounded-lg py-3 pl-12 pr-4 outline-none focus:border-[#2F3CFF] focus:ring-2 focus:ring-blue-50 transition-all font-medium text-gray-700 shadow-sm text-sm" required>
                            </div>
                        </div>

                        <div class="relative group">
                            <i class="ri-video-chat-line absolute left-4 top-1/2 -translate-y-1/2 text-gray-500 text-lg group-focus-within:text-[#2F3CFF] transition-colors"></i>
                            <input type="url" name="video_url" placeholder="Intro Video Link (YouTube)" class="w-full bg-white border border-gray-300 rounded-lg py-3 pl-12 pr-4 outline-none focus:border-[#2F3CFF] focus:ring-2 focus:ring-blue-50 transition-all font-medium text-gray-700 shadow-sm text-sm">
                        </div>
                    </div>
                </div>

                <!-- Step 2: Skills & Expertise -->
                <div id="step2" class="step-inactive">
                    <div class="space-y-4">
                        <div class="relative group">
                            <i class="ri-medal-line absolute left-4 top-4 text-gray-500 text-lg group-focus-within:text-[#2F3CFF] transition-colors"></i>
                            <input type="text" name="skills" placeholder="Skills (e.g. Wound Care, Massage)" class="w-full bg-white border border-gray-300 rounded-lg py-3.5 pl-12 pr-4 outline-none focus:border-[#2F3CFF] focus:ring-2 focus:ring-blue-50 transition-all font-medium text-gray-700 shadow-sm text-sm" required>
                        </div>

                        <div class="relative group">
                            <i class="ri-article-line absolute left-4 top-4 text-gray-500 text-lg group-focus-within:text-[#2F3CFF] transition-colors"></i>
                            <textarea name="expertise" rows="3" placeholder="Detailed Bio..." class="w-full bg-white border border-gray-300 rounded-lg py-3.5 pl-12 pr-4 outline-none focus:border-[#2F3CFF] focus:ring-2 focus:ring-blue-50 transition-all font-medium text-gray-700 shadow-sm text-sm" required></textarea>
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
                <div class="mt-4 border-t border-gray-100 pt-4 flex flex-col md:flex-row justify-between items-center gap-4">
                    <button type="button" id="prevBtn" onclick="moveStep(-1)" class="hidden text-gray-500 font-extrabold hover:text-black transition-all flex items-center gap-2 px-4 py-2 order-2 md:order-1 text-xs">
                        <i class="ri-arrow-left-line font-bold"></i> Back
                    </button>
                    <div class="hidden md:block flex-1"></div>
                    <button type="button" id="nextBtn" onclick="moveStep(1)" class="w-full md:w-auto bg-[#2F3CFF] text-white px-8 py-3 rounded-lg font-bold shadow-lg hover:scale-[1.02] active:scale-[0.98] transition-all flex items-center justify-center gap-2 order-1 md:order-2 text-xs uppercase tracking-wider">
                        Continue <i class="ri-arrow-right-line"></i>
                    </button>
                    <button type="submit" id="submitBtn" class="hidden w-full md:w-auto bg-gradient-to-r from-emerald-500 to-teal-600 text-white px-8 py-3 rounded-lg font-bold shadow-lg hover:scale-[1.02] active:scale-[0.98] transition-all flex items-center justify-center gap-2 order-1 md:order-2 text-xs uppercase tracking-wider">
                         Submit <i class="ri-checkbox-circle-fill"></i>
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

        function previewProfilePhoto(input) {
            const preview = document.getElementById('photoPreview');
            const placeholder = document.getElementById('photoPlaceholder');
            const img = document.getElementById('photoImg');
            const file = input.files[0];
            
            if (file) {
                const reader = new FileReader();
                reader.onload = (e) => {
                    img.src = e.target.result;
                    placeholder.classList.add('hidden');
                    preview.classList.remove('hidden');
                };
                reader.readAsDataURL(file);
            }
        }

        // Add Drag & Drop Support
        ['photoDrop', 'docDrop'].forEach(id => {
            const el = document.getElementById(id);
            if(!el) return;

            el.addEventListener('dragover', (e) => {
                e.preventDefault();
                el.classList.add('border-[#2F3CFF]', 'bg-blue-50');
            });

            el.addEventListener('dragleave', (e) => {
                e.preventDefault();
                el.classList.remove('border-[#2F3CFF]', 'bg-blue-50');
            });

            el.addEventListener('drop', (e) => {
                e.preventDefault();
                el.classList.remove('border-[#2F3CFF]', 'bg-blue-50');
                const file = e.dataTransfer.files[0];
                const input = el.querySelector('input');
                
                if (file) {
                    const dataTransfer = new DataTransfer();
                    dataTransfer.items.add(file);
                    input.files = dataTransfer.files;
                    
                    if(id === 'photoDrop') previewProfilePhoto(input);
                    else previewDoc(input);
                }
            });
        });

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
