<?php
session_start();
require 'vendor/autoload.php'; // Nạp autoload nếu cần thiết
// require_once 'config.php';
use GuzzleHttp\Client;
header('Content-Type: application/json; charset=utf-8');
function login($client, $username, $passwordmd5)
{
    $loginUrl = "http://dangkytinchi.ictu.edu.vn/kcntt/login.aspx";
    $response = null;
    try {
        $response = $client->get($loginUrl, ['allow_redirects' => false]);
        if ($response->getStatusCode() !== 200) {
            echo json_encode(['error' => true, 'message' => 'Lỗi hệ thống. Vui lòng thử lại sau.']);
            exit;
        }
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
    $fullname = $xpath->query("//span[@id='PageHeader1_lblUserFullName']")[0];
    $errorinfo = $xpath->query("//span[@id='lblErrorInfo']")[0];
    if ($errorinfo) {
        $error = $errorinfo->nodeValue;
        if ($error == '')
            return $fullname;
        echo json_encode(['error' => true, 'message' => $error], JSON_UNESCAPED_UNICODE);
        exit;
    }
    return $fullname;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $passwordmd5 = $_POST['password'];
    $client = new Client(['cookies' => true]);
    $fullname = login($client, $username, $passwordmd5);

    // // Kiểm tra sự tồn tại của username và lấy mật khẩu hiện tại
    // $sql = "SELECT passwordmd5 FROM student WHERE username = ?";
    // $stmt = $conn->prepare($sql);
    // $stmt->bind_param("s", $username);
    // $stmt->execute();
    // $result = $stmt->get_result();
    // $row = $result->fetch_assoc();

    // // Chuẩn bị câu truy vấn chèn hoặc cập nhật
    // if ($row) {
    //     // Người dùng đã tồn tại, kiểm tra mật khẩu
    //     if ($row['passwordmd5'] !== $passwordmd5) {
    //         // Nếu mật khẩu khác, tiến hành cập nhật
    //         $sql2 = "UPDATE student SET passwordmd5 = ? WHERE username = ?";
    //         $stmt2 = $conn->prepare($sql2);
    //         $stmt2->bind_param("ss", $passwordmd5, $username);
    //         if ($stmt2->execute()) {
    //         }
    //     }
    // } else {
    //     // Người dùng chưa tồn tại, chèn bản ghi mới
    //     $sql2 = "INSERT INTO student (username, passwordmd5) VALUES (?, ?)";
    //     $stmt2 = $conn->prepare($sql2);
    //     $stmt2->bind_param("ss", $username, $passwordmd5);
    //     $stmt2->execute();
    // }

    // // Giải phóng tài nguyên
    // $stmt->close();
    // if (isset($stmt2))
    //     $stmt2->close();
    // $conn->close();

    // Tạo cookie
    setcookie("username", $username, time() + (86400 * 30), "/", "", true, true);
    setcookie('hash', $passwordmd5, time() + (86400 * 30), '/', "", true, true);
    echo json_encode(['error' => false, 'message' => 'Thành công', 'data' => $fullname], JSON_UNESCAPED_UNICODE);
    exit;
} else {
    echo json_encode(['error' => true, 'message' => 'Đầu vào không hợp lệ'], JSON_UNESCAPED_UNICODE);
}
?>