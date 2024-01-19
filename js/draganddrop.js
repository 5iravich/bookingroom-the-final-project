const dragArea = document.getElementById('dragArea');
const dragText = document.getElementById('dragText');
const imageUpload = document.getElementById('imageUpload');

const dropArea = document.getElementById('beforeLoad');

const chooseFileText = document.getElementById('chooseFile');

dragArea.addEventListener('dragover', (e) => {
    e.preventDefault();
    dragText.textContent = 'ปล่อยเพื่ออัปโหลด';
    dragArea.classList.add('active');
});

dragArea.addEventListener('dragleave', () => {
    dragText.textContent = 'ลากและวางไฟล์';
    dragArea.classList.remove('active');
});

dragArea.addEventListener('drop', (e) => {
    e.preventDefault();
    dragArea.classList.remove('active');
    const file = e.dataTransfer.files[0];
    imageUpload.files = e.dataTransfer.files;

    // Display the dropped image within the drag-area
    const reader = new FileReader();
    reader.onload = () => {
        dragArea.style.backgroundImage = `url('${reader.result}')`;
        dragArea.style.backgroundSize = 'cover';
        dropArea.style.display = 'none';
    };
    reader.readAsDataURL(file);
});

imageUpload.addEventListener('change', () => {
    const file = imageUpload.files[0];

    if (file) {
        const reader = new FileReader();
        reader.onload = () => {
            dragArea.style.backgroundImage = `url('${reader.result}')`;
            dragArea.style.backgroundSize = 'cover';
            dropArea.style.display = 'none';
        };
        reader.readAsDataURL(file);
    }
});

chooseFileText.addEventListener('click', () => {
    imageUpload.click();
});