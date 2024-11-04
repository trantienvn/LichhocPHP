<?php
session_start();
require 'vendor/autoload.php'; // Nạp autoload nếu cần thiết
require_once 'config.php';
use GuzzleHttp\Client;
use PhpOffice\PhpSpreadsheet\Reader\Xls;
use PhpOffice\PhpSpreadsheet\IOFactory;

header('Content-Type: application/json; charset=utf-8');
error_reporting(E_ALL & ~E_WARNING);
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

function tinhtoan($tiethoc)
{
    if (!is_string($tiethoc) || strpos($tiethoc, ' --> ') === false) {
        return "";
    }

    list($vao, $ra) = array_map('intval', explode(' --> ', $tiethoc));
    $gio_vao = [
        '6:45',
        '7:40',
        '8:40',
        '9:40',
        '10:35',
        '13:00',
        '13:55',
        '14:55',
        '15:55',
        '16:50',
        '18:15',
        '19:10',
        '20:05'
    ][$vao - 1];
    $gio_ra = [
        '7:35',
        '8:30',
        '9:30',
        '10:30',
        '11:25',
        '13:50',
        '14:45',
        '15:45',
        '16:45',
        '17:40',
        '19:05',
        '20:00',
        '20:55'
    ][$ra - 1];

    return "$gio_vao --> $gio_ra";
}

function lichtuan($lich)
{
    if (!is_string($lich)) {
        return ['Tu' => '01/01/1970', 'Den' => '01/01/1970'];
    }

    list($tu, $den) = explode(' đến ', $lich);
    return ['Tu' => $tu, 'Den' => $den];
}

function thutrongtuan($thu, $batdau, $ketthuc)
{
    if (!is_string($thu) || !is_string($batdau) || !is_string($ketthuc)) {
        return "Invalid input";
    }

    $start_date = DateTime::createFromFormat('d/m/Y', $batdau);
    $end_date = DateTime::createFromFormat('d/m/Y', $ketthuc);

    if ($start_date > $end_date) {
        return "Invalid date range";
    }

    $thu_index = intval($thu) - 1;
    if ($thu_index < 1 || $thu_index > 7) {
        return "Invalid weekday number";
    }

    $current_date = $start_date;
    while ($current_date <= $end_date) {
        if ($current_date->format('N') == $thu_index) {
            return to_date_string($current_date);
        }
        $current_date->modify('+1 day');
    }

    return "No such weekday found in the range";
}

function to_date_string($date)
{
    return $date->format('d/m/Y');
}

function login($client, $username, $passwordmd5)
{
    $loginUrl = "http://dangkytinchi.ictu.edu.vn/kcntt/login.aspx";
    // $response = $client->get($loginUrl, ['allow_redirects' => false]);
    $response = null;
    try {
        $response = $client->get($loginUrl, ['allow_redirects' => false]);
    } catch (\Exception $e) {
        echo json_encode(['error' => true, 'message' => 'Máy chủ đăng kí tín chỉ đang lỗi.']);
        exit;
    }
    $html = (string) $response->getBody();
    $header = $response->getHeaders();
    $session = "";
    foreach ($header as $key => $value) {
        // Kiểm tra nếu $value là một mảng và nối nó thành chuỗi
        $headerValue = is_array($value) ? implode(", ", $value) : $value;

        // Kiểm tra xem header có chứa session ID không
        if (preg_match("/\(S\((.*?)\)\)/", $headerValue, $matches)) {
            $session = $matches[1]; // Lưu session ID
            // echo $session;
            break; // Dừng lại khi đã tìm thấy
        }
    }
    $loginUrl = "https://dangkytinchi.ictu.edu.vn/kcntt/(S(${session}))/login.aspx";
    $response = $client->get($loginUrl, ['allow_redirects' => false]);
    $html = (string) $response->getBody();
    $dom = new DOMDocument;
    @$dom->loadHTML($html);
    $xpath = new DOMXPath($dom);
    $form = $xpath->query("//form[@id='Form1']")[0];
    $payload = [];

    foreach ($xpath->query(".//input|.//select", $form) as $tag) {
        $name = $tag->getAttribute('name');
        $value = $tag->getAttribute('value');
        if ($name)
            $payload[$name] = $value;
    }

    $payload['txtUserName'] = $username;
    $payload['txtPassword'] = $passwordmd5;
    $payload['PageHeader1$drpNgonNgu'] = $xpath->query(".//select[@id='PageHeader1_drpNgonNgu']/option[@selected='selected']")->item(0)->getAttribute('value');

    $response = $client->post($loginUrl, [
        'form_params' => $payload,
        'allow_redirects' => true
    ]);
    @$dom->loadHTML(mb_convert_encoding($response->getBody(), 'HTML-ENTITIES', 'UTF-8'));
    $xpath = new DOMXPath($dom);
    $errorinfo = $xpath->query("//span[@id='lblErrorInfo']")[0];
    if ($errorinfo) {
        $error = $errorinfo->nodeValue;
        if ($error == '')
            return $session;
        echo json_encode(['error' => true, 'message' => $error], JSON_UNESCAPED_UNICODE);
        exit;
    }
    return $session;
}
function getLichthi($client, $session_id){
    $examurl = "http://dangkytinchi.ictu.edu.vn/kcntt/(S($session_id))/StudentViewExamList.aspx";
    $response = $client->get($examurl, ['allow_redirects' => false]);
    $dom = new DOMDocument;
    @$dom->loadHTML(mb_convert_encoding($response->getBody(), 'HTML-ENTITIES', 'UTF-8'));
    $xpath = new DOMXPath($dom);
    $table = $xpath->query("//table[@id='tblCourseList']")[0];
    $rows = $xpath->query(".//tr", $table); // Lấy tất cả các hàng của bảng
    $data = [];
    for ($i = 1; $i < $rows->length; $i++) {
        $cols = $xpath->query(".//td", $rows[$i]); // Lấy tất cả các cột trong hàng
        $rowData = [];

        foreach ($cols as $col) {
            $rowData[] = trim($col->textContent); // Thêm giá trị cột vào hàng, loại bỏ khoảng trắng thừa
        }

        // Gán các giá trị từ $rowData vào các biến
        [
            $stt,
            $maHP,
            $tenHP,
            $soTC,
            $ngayThi,
            $caThi,
            $hinhThucThi,
            $soBaoDanh,
            $phongThi,
            $ghiChu
        ] = $rowData + array_fill(0, 10, null); // Đảm bảo có 10 giá trị mặc định là null nếu thiếu

        // Kiểm tra nếu stt không rỗng
        if ($stt !== "") {
            $data[] = [
                "stt" => $stt,
                "maHP" => $maHP,
                "tenHP" => $tenHP,
                "soTC" => $soTC,
                "ngayThi" => $ngayThi,
                "caThi" => $caThi,
                "hinhThucThi" => $hinhThucThi,
                "soBaoDanh" => $soBaoDanh,
                "phongThi" => $phongThi,
                "ghiChu" => $ghiChu
            ];
        }
    }
    echo json_encode(['error' => false, 'message' => 'Thành công', 'data' => $data], JSON_UNESCAPED_UNICODE);
    exit;
}

function fetch_timetable($client, $session_id, $username, $conn, $exist)
{
    $TimeTableURL = "http://dangkytinchi.ictu.edu.vn/kcntt/(S($session_id))/Reports/Form/StudentTimeTable.aspx";
    $response = $client->get($TimeTableURL);
    $html = mb_convert_encoding($response->getBody(), 'HTML-ENTITIES', 'UTF-8');
    $dom = new DOMDocument;
    @$dom->loadHTML($html);
    $xpath = new DOMXPath($dom);
    $form = $xpath->query("//form[@id='Form1']")[0];
    $payload = [];
    foreach ($xpath->query(".//input", $form) as $tag) {
        $name = $tag->getAttribute('name');
        $value = $tag->getAttribute('value');
        if ($name)
            $payload[$name] = $value;
    }
    $fullname = $xpath->query("//span[@id='PageHeader1_lblUserFullName']")[0]->nodeValue;
    $payload['drpSemester'] = $xpath->query(".//select[@id='drpSemester']/option[@selected='selected']")->item(0)->getAttribute('value');
    $payload['drpTerm'] = $xpath->query(".//select[@id='drpTerm']/option[@selected='selected']")->item(0)->getAttribute('value');
    $payload['drpType'] = $xpath->query(".//select[@id='drpType']/option[@selected='selected']")->item(0)->getAttribute('value');
    // headers={'Content-Type': 'application/x-www-form-urlencoded'
    $response = $client->post($TimeTableURL, [
        'form_params' => $payload,
        'headers' => [
            'Content-Type' => 'application/x-www-form-urlencoded'
        ],
        'allow_redirects' => true
    ]);
    $body = $response->getBody();
    // lưu fiel

    // Mở nội dung từ chuỗi
    $body = $response->getBody(); // Lấy nội dung từ response
    $tempFile = tempnam(sys_get_temp_dir(), 'temp_') . '.xls'; // Tạo file tạm

    // Ghi nội dung vào file tạm
    file_put_contents($tempFile, $body);

    // Sử dụng IOFactory để đọc từ file tạm
    $spreadsheet = IOFactory::load($tempFile);

    // Gọi hàm read_timetable với dữ liệu đã đọc
    $json = read_timetable($spreadsheet, $username, $conn);
    if ($exist) {
        $sql4 = "UPDATE timetabledata SET data = ? WHERE username = ?";
        $stmt4 = $conn->prepare($sql4);
        $stmt4->bind_param("ss", $json, $username);
        $stmt4->execute();
    } else {
        $sql4 = "INSERT INTO timetabledata (username, data) VALUES (?, ?)";
        $stmt4 = $conn->prepare($sql4);
        $stmt4->bind_param("ss", $username, $json);
        $stmt4->execute();
    }
    $updatenamesql = "UPDATE student SET fullname = ? WHERE username = ?";
    $updatename = $conn->prepare($updatenamesql);
    $updatename->bind_param("ss", base64_encode($fullname), $username);
    $updatename->execute();
    $updatetime_sql = "UPDATE timetabledata SET updatetime = NOW() WHERE username = ?";
    $stmt_updatetime = $conn->prepare($updatetime_sql);
    $stmt_updatetime->bind_param("s", $username);
    $stmt_updatetime->execute();
    $stmt4->close();
    $stmt_updatetime->close();
    // Xóa file tạm
    unlink($tempFile);
    // header('Content-Type: application/vnd.ms-excel'); // Đối với file .xls
    // header('Content-Disposition: attachment; filename="timetable.xls"'); // Tên file khi tải về
    // header('Cache-Control: max-age=0'); // Không lưu cache

    // // Xuất nội dung phản hồi ra output
    // echo $body;
    return $json;
}

function read_timetable($spreadsheet, $username, $conn)
{
    $worksheet = $spreadsheet->getActiveSheet();
    $timetable = [];

    foreach ($worksheet->getRowIterator(10) as $row) {
        $data = [];
        foreach ($row->getCellIterator() as $cell) {
            $data[] = $cell->getValue();
        }

        try {
            // Kiểm tra tồn tại của các chỉ số

            if (isset($data[1])) {
                if (strpos($data[1], "đến")) {
                    preg_match('/\((.*?)\)/', $data[1], $matches);
                    $weekRange = $matches[1];
                    $tuan = lichtuan($weekRange);
                } else if(intval($data[0])!=0)
                    $timetable[] = [
                        'STT' => intval($data[0]),
                        'TenHP' => $data[1],
                        // 'MaHP' => isset($data[1]) ? explode(')', explode('(', $data[1])[1])[0] : '',
                        'GiangVien' => trim(explode("\n", $data[2])[0] ?? ''),
                        'Meet' => trim(explode("\n", $data[2])[1] ?? ''),
                        'ThuNgay' => $data[3] ?? '',
                        'ThoiGian' => tinhtoan($data[4] ?? ''),
                        'MocTG' => thutrongtuan($data[3], $tuan['Tu'], $tuan['Den']),
                        'DiaDiem' => $data[5] ?? ''
                    ];
            }
        } catch (Exception $e) {
            continue; // Bỏ qua và tiếp tục với vòng lặp
        }
    }
    // Trả về mảng dưới dạng JSON
    return json_encode(['error' => false, 'message' => 'Thành công', 'data' => $timetable], JSON_UNESCAPED_UNICODE);
}


$username = $_COOKIE['username'];
$passwordmd5 = $_COOKIE['hash'];
if (isset($_GET['getname']) && $_GET['getname'] === 'true') {
    $sql = "SELECT fullname FROM student WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $data = $row['fullname'];
    $data = base64_decode($data);
    $data = trim(explode('(', $data)[0]);
    echo json_encode([
        'error' => false, 
        'message' => 'Thành công', 
        'data' => $data
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$sql = "SELECT COUNT(*) AS count FROM timetabledata WHERE username = ?";
$sql2 = "SELECT updatetime FROM timetabledata WHERE username = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
if(isset($_GET['lichthi'])){
    $client = new Client(['cookies' => true]);
    $session_id = login($client, $username, $passwordmd5);
    getLichthi($client, $session_id);
}
if ($row['count'] > 0) {
    $stmt2 = $conn->prepare($sql2);
    $stmt2->bind_param("s", $username);
    $stmt2->execute();
    $result2 = $stmt2->get_result();
    $row2 = $result2->fetch_assoc();
    $currentDate = new DateTime(); // Current date and time
    $updatetime = new DateTime($row2['updatetime']);
    $interval = $currentDate->diff($updatetime);
    $daysDifference = $interval->days;
    if ($daysDifference > 1|| isset($_GET['username'])) {
        $client = new Client(['cookies' => true]);
        $session_id = login($client, $username, $passwordmd5);
        echo fetch_timetable($client, $session_id, $username, $conn, true);
    } else {
        $sql3 = "SELECT data FROM timetabledata WHERE username = ?";
        $stmt3 = $conn->prepare($sql3);
        $stmt3->bind_param("s", $username);
        $stmt3->execute();
        $result3 = $stmt3->get_result();
        $row3 = $result3->fetch_assoc();
        $data = $row3['data'];
        echo $data;
        exit;
    }
} else {
    $client = new Client(['cookies' => true]);
    $session_id = login($client, $username, $passwordmd5);
    echo fetch_timetable($client, $session_id, $username, $conn, false);
}

$stmt->close();
$conn->close();
?>