var modal = document.getElementById('roomListModal');
var btns = document.querySelectorAll('.btn-dorm');
var span = document.querySelector('.close');

function openModal(dormId, dormName) {

    document.body.style.overflow = 'hidden';

    var xhr = new XMLHttpRequest();
    var url = 'fetch_rooms.php';

    var selectedDormElement = document.getElementById('selectedDormNo');
    selectedDormElement.textContent = dormId;

    var selectedDormNameElement = document.getElementById('selectedDormName');
    selectedDormNameElement.textContent = '🏙 ' + dormName;

    xhr.onreadystatechange = function () {
        if (xhr.readyState === 4 && xhr.status === 200) {
            var roomListData = JSON.parse(xhr.responseText);

            var roomList = document.getElementById('roomList');
            roomList.innerHTML = '';

            roomListData.forEach(function (room) {
                var roomItem = document.createElement('div');
                roomItem.classList.add('room-item');

                var roomComp = document.createElement('div');
                roomComp.classList.add('room-comp');

                var roomNumber = document.createElement('div');
                roomNumber.classList.add('room-number');
                roomNumber.textContent = room.room_number;
                roomComp.appendChild(roomNumber);
                roomItem.appendChild(roomComp);

                var roomInfo = document.createElement('div');
                roomInfo.classList.add('room-info');
                
                var roomPrice = document.createElement('div');
                roomPrice.classList.add('room-price');
                roomPrice.textContent = '🪙 ฿'+parseInt(room.price)+'/เทอม';
                roomInfo.appendChild(roomPrice);

                if (room.booking_count >= room.max_capacity) {
                    // Hide the room
                    roomItem.style.backgroundColor = '#FAD4D4';
                    roomItem.style.borderColor = '#F47C7C';
                    roomItem.style.pointerEvents = 'none';

                    var bookingCount = document.createElement('div');
                    bookingCount.classList.add('booking-count');
                    bookingCount.textContent = 'เต็มแล้ว';
                    roomInfo.appendChild(bookingCount);
                    
                } else {
                    // Display the booking count
                    var bookingCount = document.createElement('div');
                    bookingCount.classList.add('booking-count');
                    bookingCount.textContent = 'จำนวน ' + room.booking_count + '/' + room.max_capacity +' คน';
                    roomInfo.appendChild(bookingCount);
                }
            

                roomItem.appendChild(roomInfo);


                roomList.appendChild(roomItem);

                roomItem.addEventListener('click', function () {

                    var confirmation = confirm('คุณต้องการจองห้อง: ' + room.room_number + ' ใช่หรือไม่?');
                    if (confirmation) {
                        var confirmBookPageUrl = 'confirm-booking.php' + 
                            '?dorm_id=' + dormId +
                            '&room_id=' + room.room_id +
                            '&room_number=' + room.room_number+
                            '&&dorm_name='+ dormName+
                            '&&room_price='+parseInt(room.price);
                        // Redirect
                        window.location.href = confirmBookPageUrl;
                    }
                });
            });

            modal.style.display = 'block';
        }
    };

    xhr.open('GET', url + '?dorm_id=' + dormId+dormName, true);

    xhr.send();
}

btns.forEach(function (btn) {
    btn.addEventListener('click', function () {
        var dormId = btn.getAttribute('data-dorm-id');
        var dormName = btn.getAttribute('data-dorm-name');
        openModal(dormId, dormName);
    });
});

// (x)
span.addEventListener('click', function () {
    modal.style.display = 'none';
    document.body.style.overflow = 'auto';
});

window.addEventListener('click', function (event) {
    if (event.target == modal) {
        modal.style.display = 'none';
        document.body.style.overflow = 'auto';
    }
});
