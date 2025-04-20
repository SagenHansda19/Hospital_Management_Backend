<?php
require_once __DIR__ . '/../config/db.php';

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
     header("HTTP/1.1 200 OK");
     exit();
 }

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Handle appointment creation
        $data = json_decode(file_get_contents('php://input'), true);
        
        // Validate required fields
        $required = ['patient_name', 'patient_email', 'patient_phone', 'doctor_id', 'appointment_date', 'time_slot'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                throw new Exception("Missing required field: $field");
            }
        }

        // Insert appointment
        $stmt = $conn->prepare("INSERT INTO appointments 
            (patient_name, patient_email, patient_phone, doctor_id, appointment_date, time_slot) 
            VALUES (:name, :email, :phone, :doctor_id, :date, :time_slot)");
        
        $stmt->execute([
            ':name' => $data['patient_name'],
            ':email' => $data['patient_email'],
            ':phone' => $data['patient_phone'],
            ':doctor_id' => $data['doctor_id'],
            ':date' => $data['appointment_date'],
            ':time_slot' => $data['time_slot']
        ]);

        $appointmentId = $conn->lastInsertId();
        
        echo json_encode([
            'success' => true,
            'appointment_id' => $appointmentId,
            'message' => 'Appointment booked successfully'
        ]);
    } elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
        // Handle GET requests (for fetching appointments)
        if (isset($_GET['doctor_id']) && isset($_GET['date'])) {
            // Get booked slots for a doctor on specific date
            $stmt = $conn->prepare("SELECT time_slot FROM appointments 
                                   WHERE doctor_id = :doctor_id 
                                   AND appointment_date = :date
                                   AND status != 'cancelled'");
            $stmt->execute([
                ':doctor_id' => $_GET['doctor_id'],
                ':date' => $_GET['date']
            ]);
            $bookedSlots = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            echo json_encode([
                'success' => true,
                'booked_slots' => $bookedSlots
            ]);
        }
    }
} catch(Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>