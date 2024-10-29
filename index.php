<?php
session_start();
if (!isset($_COOKIE['username'])) {
    header("Location: login.php"); // Chuyển hướng nếu chưa đăng nhập
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
    <link rel="stylesheet" href="style.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> <!-- Thêm jQuery -->
</head>

<body>
    <div type="button" class="refetch-btn" onClick="refetch()">
        <svg fill="#4CAF50" width="40px" height="40px" viewBox="0 0 24 24" id="update" data-name="Flat Line"
            xmlns="http://www.w3.org/2000/svg" class="icon flat-line">
            <path id="primary" d="M4,12A8,8,0,0,1,18.93,8"
                style="fill: none; stroke: #4CAF50; stroke-linecap: round; stroke-linejoin: round; stroke-width: 2;">
            </path>
            <path id="primary-2" data-name="primary" d="M20,12A8,8,0,0,1,5.07,16"
                style="fill: none; stroke: #4CAF50; stroke-linecap: round; stroke-linejoin: round; stroke-width: 2;">
            </path>
            <polyline id="primary-3" data-name="primary" points="14 8 19 8 19 3"
                style="fill: none; stroke: #4CAF50; stroke-linecap: round; stroke-linejoin: round; stroke-width: 2;">
            </polyline>
            <polyline id="primary-4" data-name="primary" points="10 16 5 16 5 21"
                style="fill: none; stroke: #4CAF50; stroke-linecap: round; stroke-linejoin: round; stroke-width: 2;">
            </polyline>
        </svg>
    </div>
    <div type="button" class="logout-btn" onClick="logout()">
        <svg fill="#4CAF50" width="40px" height="40px" viewBox="0 0 32 32" version="1.1"
            xmlns="http://www.w3.org/2000/svg">
            <title>logout</title>
            <path
                d="M0 9.875v12.219c0 1.125 0.469 2.125 1.219 2.906 0.75 0.75 1.719 1.156 2.844 1.156h6.125v-2.531h-6.125c-0.844 0-1.5-0.688-1.5-1.531v-12.219c0-0.844 0.656-1.5 1.5-1.5h6.125v-2.563h-6.125c-1.125 0-2.094 0.438-2.844 1.188-0.75 0.781-1.219 1.75-1.219 2.875zM6.719 13.563v4.875c0 0.563 0.5 1.031 1.063 1.031h5.656v3.844c0 0.344 0.188 0.625 0.5 0.781 0.125 0.031 0.25 0.031 0.313 0.031 0.219 0 0.406-0.063 0.563-0.219l7.344-7.344c0.344-0.281 0.313-0.844 0-1.156l-7.344-7.313c-0.438-0.469-1.375-0.188-1.375 0.563v3.875h-5.656c-0.563 0-1.063 0.469-1.063 1.031z">
            </path>
        </svg>
    </div>

    <h1>Lịch Học</h1>
    <div id="schedule"></div>

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
    </script>

    <script>
        const username = "<?php echo $username; ?>";
        const daysOfWeek = ['Chủ Nhật', 'Thứ 2', 'Thứ 3', 'Thứ 4', 'Thứ 5', 'Thứ 6', 'Thứ 7'];
        async function refetch() {
            $.ajax({
                url: `getdata.php`,
                type: 'GET',
                data: { username: username },
                dataType: 'json',
                success: function (response) {
                    if (response.data) {
                        // renderSchedule(response.data); // Hiển thị dữ liệu lịch học
                        window.location.reload();
                    } else {
                        document.getElementById('schedule').innerHTML = "<div class='class empty'>Không có lịch học</div>";
                    }
                },
                error: function (xhr, status, error) {
                    alert("Lỗi khi cập nhật lịch học.\nVui lòng thử lại sau.");
                }
            });
        }
        // Function to fetch the schedule data from JSON file
        async function fetchSchedule() {
            $.ajax({
                url: `getdata.php`, // Đường dẫn tới file PHP
                type: 'GET', // Sử dụng phương thức GET
                dataType: 'json',
                success: function (response) {
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
            if (scheduleData.length === 0) {
                scheduleDiv.innerHTML = "<div class='class empty'>Không có lịch học</div>";
                return;
            }

            const today = new Date();
            const startDay = scheduleData[0].MocTG;
            const endDay = scheduleData[scheduleData.length - 1].MocTG;
            const lastDate = stringToDate(endDay);
            lastDate.setDate(lastDate.getDate() + 7);

            const list = {};
            scheduleData.forEach(data => {
                if (!list[data.MocTG]) {
                    list[data.MocTG] = [];
                }
                list[data.MocTG].push(data);
            });

            let time = today;
            do {
                const day = daysOfWeek[time.getDay()];
                const date = getDayStr(time);
                const dayDiv = document.createElement('div');
                dayDiv.className = 'day';
                dayDiv.innerHTML = `<h2>${day} (${date})</h2>`;

                if (list[date]) {
                    list[date].forEach(classItem => {
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
                            <li>Mã HP: ${classItem.MaHP}</li>
                            <li>Giảng viên: ${classItem.GiangVien}</li>
                            <li>Thời gian: ${classItem.ThoiGian}</li>
                            <li>Địa điểm: ${classItem.DiaDiem}</li>
                            <li>Link Meet: <a href="${linkmeet}" target="_blank">${linkmeet}</a></li>
                        </ul>
                    `;
                        dayDiv.appendChild(classDiv);
                    });
                } else {
                    dayDiv.innerHTML += '<div class="class empty">Bạn rảnh</div>';
                }
                scheduleDiv.appendChild(dayDiv);
                time.setDate(time.getDate() + 1);
            } while (time <= lastDate);

            const endMessage = document.createElement('div');
            endMessage.className = 'class empty';
            endMessage.innerHTML = "Bạn đã hết lịch học";
            scheduleDiv.appendChild(endMessage);
        }

        // Fetch and render the schedule
        fetchSchedule();
    </script>
</body>

</html>