<?php
require_once '../config.php';

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

$conn = getDbConnection();

$id = $_GET['id'] ?? null; // Mengambil 'id' dari URL
$method = $_SERVER['REQUEST_METHOD'];
$data = $_POST; 

switch ($method) {
    case 'GET':
        if ($id) {
            $result = getDataSensorById($conn, $id);
        } else {
            $result = getAllDataSensors($conn);
        }
        break;
    case 'POST':

        if (!$id) {
            $result = addDataSensor($conn, $data);
        } else {
            $result = updateDataSensor($conn, $id, $data);
        }
        break;
    case 'DELETE':
        if ($id) {
            $result = deleteDataSensor($conn, $id);
        } else {
            $result = "Please provide an ID to delete.";
        }
        break;
    default:
        $result = "Method not allowed.";
        break;
}

function getAllDataSensors($conn) {
    $sql = "SELECT * FROM data_sensor";
    $stmt = $conn->query($sql);
    return $stmt->fetchAll();
}

function getDataSensorById($conn, $id) {
    $sql = "SELECT * FROM data_sensor WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$id]);
    return $stmt->fetch();
}

function addDataSensor($conn, $data) {
    $required_fields = ['intensitas_cahaya', 'kelembaban_tanah', 'kualitas_udara', 'RainDrop', 'kelembaban_udara', 'suhu', 'tekanan', 'ketinggian', 'waktu_perekaman'];
    
    foreach ($required_fields as $field) {
        if (!isset($data[$field])) {
            return "Field '$field' is required.";
        }
    }

    $sql = "INSERT INTO data_sensor (id_sensor, intensitas_cahaya, kelembaban_tanah, kualitas_udara, RainDrop, kelembaban_udara, suhu, tekanan, ketinggian, waktu_perekaman, created_at, updated_at) VALUES (:id_sensor, :intensitas_cahaya, :kelembaban_tanah, :kualitas_udara, :RainDrop, :kelembaban_udara, :suhu, :tekanan, :ketinggian, :waktu_perekaman, :created_at, :updated_at)";
    $stmt = $conn->prepare($sql);
    
    $stmt->execute([
        $data['id_sensor'],
        $data['intensitas_cahaya'],
        $data['kelembaban_tanah'],
        $data['kualitas_udara'],
        $data['RainDrop'],
        $data['kelembaban_udara'],
        $data['suhu'],
        $data['tekanan'],
        $data['ketinggian'],
        $data['waktu_perekaman'],
        date('Y-m-d H:i:s'),
        date('Y-m-d H:i:s')
    ]);
    
    return $stmt->rowCount() > 0 ? "Data sensor added successfully." : "Failed to add data sensor.";
}

function updateDataSensor($conn, $id, $data) {
    if (empty($data)) {
        return "Tidak ada data yang diberikan untuk diperbarui.";
    }

    $sql = "UPDATE data_sensor SET";
    $params = [];
    $allowed_fields = ['intensitas_cahaya', 'kelembaban_tanah', 'kualitas_udara', 'RainDrop', 'kelembaban_udara', 'suhu', 'tekanan', 'ketinggian', 'waktu_perekaman'];

    foreach ($data as $key => $value) {
        if (in_array($key, $allowed_fields)) {
            $sql .= " $key = :$key,";
            $params[$key] = $value;
        }
    }

    $sql = rtrim($sql, ',');
    $sql .= " WHERE id = :id";
    $params['id'] = $id;

    try {
        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
        
        if ($stmt->rowCount() > 0) {
            return "Data sensor berhasil diperbarui.";
        } else {
            return "Tidak ada perubahan yang dilakukan pada data sensor.";
        }
    } catch (PDOException $e) {
        return "Kesalahan: " . $e->getMessage();
    }
}


function deleteDataSensor($conn, $id) {
    $sql = "DELETE FROM data_sensor WHERE id = ?";
    $stmt = $conn->prepare($sql);
    
    $stmt->execute([$id]);
    
    return $stmt->rowCount() > 0 ? "Data sensor deleted successfully." : "Failed to delete data sensor.";
}

header('Content-Type: application/json');
echo json_encode($result);
?>
