<?php
session_start();
include '../public/config.php';
include '../public/header.php';

// Ensure only admin can access this page
if (!isset($_SESSION['admin'])) {
    header('Location: ../public/login.php');
    exit;
}

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Handle form submission
    $patient_name = $_POST['patient_name'];
    $mobile_no = $_POST['mobile_no'];
    $service = $_POST['service'];
    $date = $_POST['date'];
    $time = $_POST['time'];

    // Find the patient ID based on the provided name and mobile number
    $stmt = $conn->prepare("SELECT PatientID FROM Patients WHERE Name = ? AND MobileNo = ?");
    $stmt->execute([$patient_name, $mobile_no]);
    $patient = $stmt->fetch();

    if ($patient) {
        $patient_id = $patient['PatientID'];
    } else {
        // Insert new patient if not found
        try {
            $stmt = $conn->prepare("INSERT INTO Patients (Name, MobileNo) VALUES (?, ?)");
            $stmt->execute([$patient_name, $mobile_no]);
            $patient_id = $conn->lastInsertId();
        } catch (PDOException $e) {
            $message = "Error inserting new patient: " . $e->getMessage();
        }
    }

    if (isset($patient_id)) {
        // Find the SlotID based on Date and Time
        $stmt = $conn->prepare("SELECT SlotID FROM Slots WHERE Date = ? AND Time = ?");
        $stmt->execute([$date, $time]);
        $slot = $stmt->fetch();

        if ($slot) {
            $slot_id = $slot['SlotID'];

            // Insert the appointment
            try {
                $stmt = $conn->prepare("INSERT INTO Appointments (PatientID, Date, Time, Service, Status, SlotID) VALUES (?, ?, ?, ?, 'Pending', ?)");
                $stmt->execute([$patient_id, $date, $time, $service, $slot_id]);
                $message = "Appointment added successfully";
            } catch (PDOException $e) {
                $message = "Error: " . $e->getMessage();
            }
        } else {
            $message = "Slot not found. Please check the date and time.";
        }
    }
} else {
    // Retrieve date, time, and service from query parameters for GET request
    $date = isset($_GET['date']) ? $_GET['date'] : '';
    $time = isset($_GET['time']) ? $_GET['time'] : '';
    $service = isset($_GET['service']) ? $_GET['service'] : '';
}
?>

<h2 class="text-3xl font-bold text-center mb-8">Add Appointment</h2>

<div class="card card-bordered w-full max-w-md bg-base-100 shadow-xl">
    <div class="card-body">
        <form id="appointmentForm" action="add_appointment.php" method="post" class="flex flex-col gap-4">
            <label class="floating-label">
                <input type="text" id="patient_name" name="patient_name" placeholder="Patient Name" class="input input-bordered w-full" required />
                <span>Patient Name</span>
            </label>
            
            <label class="floating-label">
                <input type="text" id="mobile_no" name="mobile_no" placeholder="Mobile No" class="input input-bordered w-full" required />
                <span>Mobile No</span>
            </label>
            
            <label class="floating-label">
                <input type="text" id="service" name="service" value="<?php echo htmlspecialchars($service); ?>" placeholder="Service Type" class="input input-bordered w-full" required />
                <span>Service Type</span>
            </label>
            
            <label class="floating-label">
                <input type="text" id="date" name="date" value="<?php echo htmlspecialchars($date); ?>" placeholder="Date" class="input input-bordered w-full" readonly />
                <span>Date</span>
            </label>
            
            <label class="floating-label">
                <input type="text" id="time" name="time" value="<?php echo htmlspecialchars($time); ?>" placeholder="Time" class="input input-bordered w-full" readonly />
                <span>Time</span>
            </label>
            
            <button type="submit" class="btn btn-primary w-full mt-2">Add Appointment</button>
        </form>
        
        <div class="card-actions justify-center mt-4">
            <button class="btn btn-ghost" onclick="window.location.href='admin_slots.php';">Back</button>
        </div>
    </div>
</div>

<?php include '../public/footer.php'; ?>

<?php if (!empty($message)): ?>
<script>
    window.onload = function() {
        alert("<?php echo $message; ?>");
    };
</script>
<?php endif; ?>

<script>
document.getElementById('appointmentForm').addEventListener('submit', function(event) {
    const patientName = document.getElementById('patient_name').value.trim();
    const mobileNo = document.getElementById('mobile_no').value.trim();
    const service = document.getElementById('service').value.trim();

    // Validate patient name
    if (patientName === '') {
        alert('Please enter the patient name.');
        event.preventDefault();
        return;
    }

    // Validate mobile number
    const mobileNoPattern = /^[0-9]{10,12}$/; // Assuming a valid mobile number is 10-12 digits
    if (!mobileNoPattern.test(mobileNo)) {
        alert('Please enter a valid mobile number.');
        event.preventDefault();
        return;
    }

    // Validate service type
    if (service === '') {
        alert('Please enter the service type.');
        event.preventDefault();
        return;
    }
});
</script>
