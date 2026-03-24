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
    
    if ($hospital_id > 0) {
        $stmt = $conn->prepare("UPDATE users SET hospital_id = ? WHERE id = ?");
        $stmt->bind_param("ii", $hospital_id, $user_id);
        
        if ($stmt->execute()) {
            $_SESSION['hospital_id'] = $hospital_id;
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
        body { font-family: 'Poppins', sans-serif; background: #f8fafc; }
        .step-tile { transition: all 0.3s ease; }
        .step-tile.active { border-color: #2F3CFF; background: #eff6ff; }
        .fade-in { animation: fadeIn 0.5s ease-out; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
    </style>
</head>
<body class="min-h-screen flex flex-col items-center justify-center p-6">

    <div class="max-w-2xl w-full bg-white rounded-3xl shadow-2xl overflow-hidden fade-in">
        <!-- Header -->
        <div class="bg-[#2F3CFF] p-8 text-white text-center">
            <h1 class="text-3xl font-bold mb-2">Welcome, <?php echo explode(' ', $full_name)[0]; ?>!</h1>
            <p class="opacity-90">Let's personalize your healthcare experience.</p>
        </div>

        <div class="p-8">
            <!-- Progress Bar -->
            <div class="flex items-center justify-center gap-4 mb-10">
                <div id="prog-1" class="w-10 h-10 rounded-full bg-[#2F3CFF] text-white flex items-center justify-center font-bold">1</div>
                <div class="h-1 w-20 bg-gray-200 rounded-full overflow-hidden">
                    <div id="prog-bar" class="h-full bg-[#2F3CFF] w-0 transition-all duration-500"></div>
                </div>
                <div id="prog-2" class="w-10 h-10 rounded-full bg-gray-200 text-gray-500 flex items-center justify-center font-bold">2</div>
            </div>

            <form id="onboardingForm" method="POST">
                <!-- Step 1: Location -->
                <div id="step-1" class="step-container">
                    <h2 class="text-xl font-semibold mb-6 text-center"><?php echo get_setting('onboarding_step1_question', 'Where are you located?'); ?></h2>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <?php while($loc = $locations->fetch_assoc()): ?>
                            <label class="relative block cursor-pointer">
                                <input type="radio" name="location_id" value="<?php echo $loc['id']; ?>" class="peer hidden" onchange="enableNext(1)">
                                <div class="p-4 border-2 border-gray-100 rounded-2xl flex items-center gap-4 hover:border-blue-200 peer-checked:border-[#2F3CFF] peer-checked:bg-blue-50 transition-all">
                                    <div class="w-10 h-10 rounded-xl bg-gray-100 flex items-center justify-center text-[#2F3CFF]">
                                        <i class="ri-map-pin-line text-xl"></i>
                                    </div>
                                    <span class="font-medium text-gray-700"><?php echo $loc['name']; ?></span>
                                </div>
                            </label>
                        <?php endwhile; ?>
                    </div>
                </div>

                <!-- Step 2: Hospital -->
                <div id="step-2" class="step-container hidden">
                    <h2 class="text-xl font-semibold mb-6 text-center"><?php echo get_setting('onboarding_step2_question', 'Which hospital are you at?'); ?></h2>
                    <div id="hospitalList" class="grid grid-cols-1 gap-4 max-h-80 overflow-y-auto pr-2 custom-scrollbar">
                        <!-- Loaded via AJAX -->
                        <div class="text-center py-10 text-gray-400">
                            <i class="ri-loader-4-line text-3xl animate-spin block mb-2"></i>
                            Loading hospitals...
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="mt-10 flex justify-between items-center">
                    <button type="button" id="prevBtn" onclick="prevStep()" class="hidden px-6 py-3 text-gray-500 font-medium hover:text-gray-800 flex items-center gap-2">
                        <i class="ri-arrow-left-line"></i> Back
                    </button>
                    <div class="flex-1"></div>
                    <button type="button" id="nextBtn" disabled onclick="nextStep()" class="px-10 py-3 bg-[#2F3CFF] text-white rounded-xl font-bold shadow-lg shadow-blue-200 disabled:opacity-50 disabled:cursor-not-allowed transform transition active:scale-95">
                        Next Step
                    </button>
                    <button type="submit" id="submitBtn" name="complete_onboarding" class="hidden px-10 py-3 bg-[#2F3CFF] text-white rounded-xl font-bold shadow-lg shadow-blue-200 transform transition active:scale-95">
                        Complete Setup
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
                document.getElementById('nextBtn').classList.add('hidden');
                document.getElementById('submitBtn').classList.remove('hidden');
                
                document.getElementById('prog-bar').style.width = '100%';
                document.getElementById('prog-2').classList.remove('bg-gray-200', 'text-gray-500');
                document.getElementById('prog-2').classList.add('bg-[#2F3CFF]', 'text-white');
                
                currentStep = 2;
            }
        }

        function prevStep() {
            if (currentStep === 2) {
                document.getElementById('step-2').classList.add('hidden');
                document.getElementById('step-1').classList.remove('hidden');
                document.getElementById('prevBtn').classList.add('hidden');
                document.getElementById('submitBtn').classList.add('hidden');
                document.getElementById('nextBtn').classList.remove('hidden');
                
                document.getElementById('prog-bar').style.width = '0%';
                document.getElementById('prog-2').classList.add('bg-gray-200', 'text-gray-500');
                document.getElementById('prog-2').classList.remove('bg-[#2F3CFF]', 'text-white');
                
                currentStep = 1;
            }
        }

        async function loadHospitals(locationId) {
            const list = document.getElementById('hospitalList');
            list.innerHTML = `
                <div class="text-center py-10 text-gray-400">
                    <i class="ri-loader-4-line text-3xl animate-spin block mb-2"></i>
                    Loading hospitals...
                </div>
            `;
            
            try {
                const response = await fetch(`handlers/get_hospitals.php?location_id=${locationId}`);
                const hospitals = await response.json();
                
                if (hospitals.length === 0) {
                    list.innerHTML = '<div class="text-center py-10 text-gray-400">No hospitals found in this location.</div>';
                    return;
                }
                
                list.innerHTML = hospitals.map(h => `
                    <label class="relative block cursor-pointer">
                        <input type="radio" name="hospital_id" value="${h.id}" class="peer hidden" required>
                        <div class="p-4 border-2 border-gray-100 rounded-2xl flex items-center gap-4 hover:border-blue-200 peer-checked:border-[#2F3CFF] peer-checked:bg-blue-50 transition-all">
                            <div class="w-10 h-10 rounded-xl bg-gray-100 flex items-center justify-center text-[#2F3CFF]">
                                <i class="ri-hospital-line text-xl"></i>
                            </div>
                            <span class="font-medium text-gray-700">${h.name}</span>
                        </div>
                    </label>
                `).join('');
                
            } catch (error) {
                list.innerHTML = '<div class="text-center py-10 text-red-400">Failed to load hospitals.</div>';
            }
        }
    </script>
</body>
</html>
