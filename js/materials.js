function fetchItemQuantity() {
	const itemDropdown = document.getElementById('ItemID');
	const itemQuantityDisplay = document.getElementById('ItemQuantity');
	const selectedItem = itemDropdown.value;
    if (selectedItem !== 'เลือกวัสดุอุปกรณ์') {
		const xhr = new XMLHttpRequest();
		xhr.open('GET', 'fetch_item_quantity.php?ItemID=' + selectedItem, true);
		xhr.onreadystatechange = function () {
            if (xhr.readyState === 4 && xhr.status === 200) {
                const quantity = xhr.responseText;
                itemQuantityDisplay.innerHTML = 'จำนวนที่เหลือ: ' + quantity;
            }
	    };
	    xhr.send();
	} else {
		itemQuantityDisplay.innerHTML = '';
	}
}
