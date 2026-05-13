<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

require_once 'config/database.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    if (isset($_GET['id'])) {
        $id = intval($_GET['id']);
        $result = $conn->query("SELECT * FROM service_reports WHERE id = $id");
        echo json_encode(['success' => true, 'data' => $result->fetch_assoc()]);
    } else {
        $result = $conn->query('SELECT * FROM service_reports ORDER BY id DESC');
        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        echo json_encode(['success' => true, 'data' => $data]);
    }
} elseif ($method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);

    // Generate nomor report otomatis
    $year = date('Y');
    $month = date('n');
    $roman = ['', 'I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X', 'XI', 'XII'];
    $bulanRomawi = $roman[$month];

    $countResult = $conn->query("SELECT COUNT(*) as total FROM service_reports WHERE YEAR(created_at) = $year");
    $countRow = $countResult->fetch_assoc();
    $seq = $countRow['total'] + 150;
    $reportNumber = "$seq/SR/$bulanRomawi/$year";

    if (isset($input['id']) && !empty($input['id'])) {
        // Update
        $id = intval($input['id']);
        $sql = "UPDATE service_reports SET 
            report_date = '{$input['tanggal']}',
            room_name = '{$input['ruangan']}',
            facility_type = '{$input['prasarana']}',
            brand = '{$input['merk']}',
            model = '{$input['model']}',
            sn_code = '{$input['sn_code']}',
            last_calibration = '{$input['last_calibration']}',
            distributor = '{$input['distributor']}',
            year = '{$input['year']}',
            physical_test = {$input['physical_test']},
            function_test = {$input['function_test']},
            accessories_check = {$input['accessories_check']},
            parameter_setting = {$input['parameter_setting']},
            mechanical_check = {$input['mechanical_check']},
            warming_check = {$input['warming_check']},
            troubleshooting = {$input['troubleshooting']},
            problem_solution = '{$input['problem_solution']}',
            description = '{$input['description']}',
            result_status = '{$input['result_status']}'
        WHERE id = $id";
        $success = $conn->query($sql);
        echo json_encode(['success' => $success, 'message' => $success ? 'Berhasil diupdate' : 'Gagal update']);
    } else {
        // Insert
        $sql = "INSERT INTO service_reports (report_number, report_date, room_name, facility_type, brand, model, sn_code, last_calibration, distributor, year, physical_test, function_test, accessories_check, parameter_setting, mechanical_check, warming_check, troubleshooting, problem_solution, description, result_status) 
        VALUES ('$reportNumber', '{$input['tanggal']}', '{$input['ruangan']}', '{$input['prasarana']}', '{$input['merk']}', '{$input['model']}', '{$input['sn_code']}', '{$input['last_calibration']}', '{$input['distributor']}', '{$input['year']}', {$input['physical_test']}, {$input['function_test']}, {$input['accessories_check']}, {$input['parameter_setting']}, {$input['mechanical_check']}, {$input['warming_check']}, {$input['troubleshooting']}, '{$input['problem_solution']}', '{$input['description']}', '{$input['result_status']}')";
        $success = $conn->query($sql);
        echo json_encode(['success' => $success, 'message' => $success ? 'Berhasil disimpan' : 'Gagal simpan', 'report_number' => $reportNumber]);
    }
} elseif ($method === 'DELETE') {
    $id = intval($_GET['id']);
    $success = $conn->query("DELETE FROM service_reports WHERE id = $id");
    echo json_encode(['success' => $success]);
}

$conn->close();
?>