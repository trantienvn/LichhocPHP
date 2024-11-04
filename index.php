<?php
session_start();
if (!isset($_COOKIE['username'])) {
    header("Location: login"); // Chuyển hướng nếu chưa đăng nhập
    exit();
}
$username = $_COOKIE['username']; // Assuming the username is stored in the session
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lịch Học</title>
    <meta name="description" content="Hệ thống xem lịch học ICTU" />
    <meta name="keywords" content="Hệ thống xem lịch học ICTU" />
    <meta property="og:description" content="Hệ thống xem lịch học ICTU" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Mulish:ital,wght@0,200..1000;1,200..1000&family=Noto+Sans:ital,wght@0,100..900;1,100..900&family=Open+Sans:ital,wght@0,300..800;1,300..800&family=Playwrite+GB+S:ital,wght@0,100..400;1,100..400&display=swap"
        rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> <!-- Thêm jQuery -->
</head>
<style>
    :root {
        --main-color: #3182f4;
    }

    body {
        font-family: Mulish, sans-serif;
        margin: 20px;
        background-color: var(--main-color);
        color: #333;
    }

    h1 {
        text-align: center;
        color: #fff;
        margin-bottom: 20px;
    }



    .empty {
        font-weight: bold;
        color: #ff5722;
    }

    .logout-btn,
    .refetch-btn {
        color: #fff;
        text-decoration: none;
        border-radius: 5px;
        cursor: pointer;
    }

    .logout-btn {
        float: left;
        rotate: 180deg;
    }

    .refetch-btn {
        float: right;
    }

    h2 {
        color: #000;
        font-weight: bold;
        font-size: 1.5rem;
        margin-bottom: 10px;
        margin-left: 10px;
    }

    h6 {
        color: #2196F3;
        font-size: 20px;
        font-weight: bold;
        margin-bottom: 10px;
        margin-top: 10px;
    }

    .day {
        margin-bottom: 30px;
        padding: 10px;
        background-color: #fff;
        border-radius: 5px;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
    }

    .class {
        margin-left: 10px;
        margin-right: 10px;
        margin-bottom: 15px;
        padding: 10px;
        background-color: #e3f2fd;
        border-left: 5px solid #2196F3;
    }

    ul {
        list-style-type: none;
        padding-left: 0;
    }

    li {
        margin-bottom: 5px;
    }

    .empty {
        margin-top: 15px;
        font-weight: bold;
        color: #ff5722;
    }

    a {
        text-decoration: none;
    }
</style>

<body>
    <div type="button" class="logout-btn" onClick="logout()">
        <svg fill="#fff" width="40px" height="40px" viewBox="0 0 32 32" version="1.1"
            xmlns="http://www.w3.org/2000/svg">
            <title>Đăng xuất</title>
            <path
                d="M0 9.875v12.219c0 1.125 0.469 2.125 1.219 2.906 0.75 0.75 1.719 1.156 2.844 1.156h6.125v-2.531h-6.125c-0.844 0-1.5-0.688-1.5-1.531v-12.219c0-0.844 0.656-1.5 1.5-1.5h6.125v-2.563h-6.125c-1.125 0-2.094 0.438-2.844 1.188-0.75 0.781-1.219 1.75-1.219 2.875zM6.719 13.563v4.875c0 0.563 0.5 1.031 1.063 1.031h5.656v3.844c0 0.344 0.188 0.625 0.5 0.781 0.125 0.031 0.25 0.031 0.313 0.031 0.219 0 0.406-0.063 0.563-0.219l7.344-7.344c0.344-0.281 0.313-0.844 0-1.156l-7.344-7.313c-0.438-0.469-1.375-0.188-1.375 0.563v3.875h-5.656c-0.563 0-1.063 0.469-1.063 1.031z">
            </path>
        </svg>
    </div>
    <div type="button" class="refetch-btn" onClick="refetch()">
        <svg fill="#fff" width="40px" height="40px" viewBox="0 0 24 24" id="update" data-name="Flat Line"
            xmlns="http://www.w3.org/2000/svg" class="icon flat-line">
            <title>Cập nhật lịch học</title>
            <path id="primary" d="M4,12A8,8,0,0,1,18.93,8"
                style="fill: none; stroke: #fff; stroke-linecap: round; stroke-linejoin: round; stroke-width: 2;">
            </path>
            <path id="primary-2" data-name="primary" d="M20,12A8,8,0,0,1,5.07,16"
                style="fill: none; stroke: #fff; stroke-linecap: round; stroke-linejoin: round; stroke-width: 2;">
            </path>
            <polyline id="primary-3" data-name="primary" points="14 8 19 8 19 3"
                style="fill: none; stroke: #fff; stroke-linecap: round; stroke-linejoin: round; stroke-width: 2;">
            </polyline>
            <polyline id="primary-4" data-name="primary" points="10 16 5 16 5 21"
                style="fill: none; stroke: #fff; stroke-linecap: round; stroke-linejoin: round; stroke-width: 2;">
            </polyline>
        </svg>
    </div>


    <h1 id="title">Lịch Học</h1>
    <div id="schedule">
        <div class="class empty" style="display: flex; justify-content: center; align-items: center; height: 100vh;">
            Đang cập nhật lịch học
            <svg width="100" height="100">
                <circle cx="50" cy="50" r="30" stroke="#e1e7e7" stroke-width="10" fill="none"></circle>
                <circle cx="50" cy="50" r="30" stroke="#07abcc" stroke-width="8" stroke-linecap="round" fill="none">
                    <animateTransform attributeName="transform" type="rotate" repeatCount="indefinite"
                        dur="1.6666666666666667s" values="0 50 50;180 50 50;720 50 50" keyTimes="0;0.5;1">
                    </animateTransform>
                    <animate attributeName="stroke-dasharray" repeatCount="indefinite" dur="1.6666666666666667s"
                        values="18.84955592153876 169.64600329384882;94.2477796076938 94.24777960769377;18.84955592153876 169.64600329384882"
                        keyTimes="0;0.5;1"></animate>
                </circle>
            </svg>
        </div>

    </div>
    <script src="./fetch.js"></script>
    <script>
        function logout() {
            var xhr = new XMLHttpRequest();
            xhr.open("POST", "logout.php", true);
            xhr.addEventListener("readystatechange", function () {
                if (xhr.readyState === 4 && xhr.status === 200) {
                    window.location = "login.php";
                }
            });
            xhr.send();
        }
        async function refetch() {
            document.getElementById('schedule').innerHTML = `
                <div class="class empty" style="display: flex; justify-content: center; align-items: center; height: 100vh;">
    Đang cập nhật lịch học
                <svg width="100" height="100">
                    <circle cx="50" cy="50" r="30" stroke="#e1e7e7" stroke-width="10" fill="none"></circle>
                    <circle cx="50" cy="50" r="30" stroke="#07abcc" stroke-width="8" stroke-linecap="round" fill="none">
                        <animateTransform attributeName="transform" type="rotate" repeatCount="indefinite" dur="1.6666666666666667s" values="0 50 50;180 50 50;720 50 50" keyTimes="0;0.5;1"></animateTransform>
                        <animate attributeName="stroke-dasharray" repeatCount="indefinite" dur="1.6666666666666667s" values="18.84955592153876 169.64600329384882;94.2477796076938 94.24777960769377;18.84955592153876 169.64600329384882" keyTimes="0;0.5;1"></animate>
                    </circle>
                </svg>
                </div>

                `;

            $.ajax({
                url: `getdata`,
                type: 'GET',
                data: { username: username },
                dataType: 'json',
                success: function (response) {
                    if (response.data) {

                        if (response.lichthi) {
                            lichthi = response.lichthi;
                        }
                        renderSchedule(response.data); // Hiển thị dữ liệu lịch học
                        // window.location.reload();
                    } else {
                        alert(response.message);
                        document.getElementById('schedule').innerHTML = "<div class='class empty'>Không có lịch học</div>";
                    }
                },
                error: function (xhr, status, error) {
                    alert("Lỗi khi cập nhật lịch học.\nVui lòng thử lại sau.");
                }
            });
        }
        function getname() {

            // $.ajax({
            //     url: `getdata`,
            //     type: 'GET',
            //     data: { getname: true },
            //     dataType: 'json',
            //     success: function (response) {
            //         if (response.data) {
            //             document.getElementById('title').innerHTML = `Lịch học của ${response.data}`;
            //         }
            //     },
            //     error: function (xhr, status, error) {
            //     }
            // });
        }

    </script>

    <script>
        const username = "<?php echo $username; ?>";
        const daysOfWeek = ['Chủ Nhật', 'Thứ 2', 'Thứ 3', 'Thứ 4', 'Thứ 5', 'Thứ 6', 'Thứ 7'];
        let lichthi = [];
        // Function to fetch the schedule data from JSON file
        async function fetchSchedule() {
            $.ajax({
                url: `getdata`, // Đường dẫn tới file PHP
                type: 'GET', // Sử dụng phương thức GET
                dataType: 'json',
                success: function (response) {
                    if (response.lichthi) {
                        lichthi = response.lichthi;
                    }
                    if (response.error === false) {
                        renderSchedule(response.data); // Hiển thị dữ liệu lịch học
                    } else {
                        document.getElementById('schedule').innerHTML = "<div class='class empty'>Không có lịch học</div>";
                    }
                },
                error: function (xhr, status, error) {
                    console.error("Error fetching schedule:", error);
                    document.getElementById('schedule').innerHTML = "<div class='class empty'>Có lỗi xảy ra khi tải lịch học.</div>";
                }
            });
        }

        function getDayStr(today) {
            const dd = String(today.getDate()).padStart(2, '0');
            const mm = String(today.getMonth() + 1).padStart(2, '0');
            const yyyy = today.getFullYear();
            return `${dd}/${mm}/${yyyy}`;
        }

        function stringToDate(str) {
            const strs = str.split('/');
            return new Date(strs[2], strs[1] - 1, strs[0]);
        }

        function renderSchedule(scheduleData) {
            const scheduleDiv = document.getElementById('schedule');
            scheduleDiv.innerHTML = "";
            if (scheduleData.length === 0) {
                scheduleDiv.innerHTML = "<div class='day'>Không có lịch học</div>";
                return;
            }

            const today = new Date();
            const startDay = scheduleData[0].MocTG;
            lichthi.sort((a, b) => {
                const dateA = new Date(a.ngayThi.split('/').reverse().join('-')); // Định dạng DD/MM/YYYY thành YYYY-MM-DD
                const dateB = new Date(b.ngayThi.split('/').reverse().join('-'));
                return dateA - dateB; // So sánh ngày
            });
            // scheduleData.sort((a, b) => {
            //     const dateA = new Date(a.MocTG.split('/').reverse().join('-')); // Định dạng DD/MM/YYYY thành YYYY-MM-DD
            //     const dateB = new Date(b.MocTG.split('/').reverse().join('-'));
            //     return dateA - dateB; // So sánh ngày
            // });
            let endDay = "01/01/2050";
            if (lichthi.length > 0) {
                endDay = lichthi[lichthi.length - 1].ngayThi;
            } else {
                endDay = scheduleData[scheduleData.length - 1].MocTG;
            }
            const lastDate = stringToDate(endDay);
            lastDate.setDate(lastDate.getDate() + 7);

            const list = {};
            scheduleData.forEach(data => {
                if (!list[data.MocTG]) {
                    list[data.MocTG] = [];
                }
                list[data.MocTG].push(data);
            });
            lichthi.forEach(data => {
                if (!list[data.ngayThi]) {
                    list[data.ngayThi] = [];
                }
                list[data.ngayThi].push(data);
            })
            let time = today;
            do {
                const day = daysOfWeek[time.getDay()];
                const date = getDayStr(time);
                const dayDiv = document.createElement('div');
                dayDiv.className = 'day';
                dayDiv.innerHTML = `<h2>${day} (${date})</h2>`;

                if (list[date]) {
                    list[date].forEach(classItem => {
                        if (!classItem.hinhThucThi) {
                            let linkmeet = classItem.Meet;
                            if (linkmeet.length === 0) {
                                linkmeet = "Không có link meet";
                            } else if (!linkmeet.startsWith("http")) {
                                linkmeet = "http://" + linkmeet;
                            }
                            const classDiv = document.createElement('div');
                            classDiv.className = 'class';
                            classDiv.innerHTML = `
                            <h6>${classItem.TenHP}</h6>
                            <ul>
                                <li>Giảng viên: ${classItem.GiangVien}</li>
                                <li>Thời gian: ${classItem.ThoiGian}</li>
                                <li>Địa điểm: ${classItem.DiaDiem}</li>
                                <li>Link Meet: <a href="${linkmeet}" target="_blank">${linkmeet}</a></li>
                            </ul>`;
                            dayDiv.appendChild(classDiv);
                        } else {
                            const classDiv = document.createElement('div');
                            classDiv.className = 'class';
                            classDiv.innerHTML = `
                            <h6>Lịch thi môn: ${classItem.tenHP}</h6>
                            <ul>
                                <li>Mã HP: ${classItem.maHP}</li>
                                <li>Số tín chỉ: ${classItem.soTC}</li>
                                <li>Ca thi: ${classItem.caThi}</li>
                                <li>Hình thức thi: ${classItem.hinhThucThi}</li>
                                <li>Số báo danh: ${classItem.soBaoDanh}</li>
                                <li>Phòng thi: ${classItem.phongThi}</li>
                                <li>Ghi chú: ${classItem.ghiChu}</li>
                            </ul>
                            `;
                            dayDiv.appendChild(classDiv);
                        }
                    });
                } else {
                    dayDiv.innerHTML += '<div class="class empty">Bạn rảnh</div>';
                }
                scheduleDiv.appendChild(dayDiv);
                time.setDate(time.getDate() + 1);
            } while (time <= lastDate);

            const endMessage = document.createElement('div');
            endMessage.className = 'day';
            endMessage.innerHTML = "<div class='class empty'>Bạn đã hoàn thành kì học.</div>";
            scheduleDiv.appendChild(endMessage);
        }

        // Fetch and render the schedule
        fetchSchedule();
    </script>
</body>

</html>