document.addEventListener("DOMContentLoaded", function() {
    const questions = document.querySelectorAll(".question");

    questions.forEach(question => {
        const imageGrid = question.querySelector(".image-grid");
        const showMoreBtn = question.querySelector(".show-more-btn");
        const hiddenCountSpan = showMoreBtn.querySelector(".hidden-count");

        if (!imageGrid || !showMoreBtn || !hiddenCountSpan) {
            return; // Skip this question if any required element is missing
        }

        // Số lượng hình ảnh bị ẩn ban đầu
        let hiddenCount = imageGrid.children.length - 12;

        // Ẩn các hình ảnh từ thứ 12 trở đi
        if (hiddenCount > 0) {
            for (let i = 12; i < imageGrid.children.length; i++) {
                imageGrid.children[i].classList.add("hidden");
            }

            // Hiển thị số lượng hình ảnh bị ẩn ban đầu
            hiddenCountSpan.textContent = hiddenCount;
            showMoreBtn.style.display = "block";
            showMoreBtn.textContent = `Show more (${hiddenCount} pic)`;
        } else {
            showMoreBtn.style.display = "none";
        }

        // Xử lý sự kiện khi nhấp vào nút "Show More"
        showMoreBtn.addEventListener("click", function() {
            imageGrid.classList.add("animated");
            imageGrid.classList.toggle("show-all");

            if (imageGrid.classList.contains("show-all")) {
                showMoreBtn.textContent = `Hide`;
                showAllImages(imageGrid);
            } else {
                showMoreBtn.textContent = `Show more (${hiddenCount} pic)`;
                hideExtraImages(imageGrid);
            }
        });
    });

    function showAllImages(container) {
        const hiddenImages = container.querySelectorAll('.hidden');
        hiddenImages.forEach(image => {
            image.classList.remove('hidden');
            setTimeout(() => {
                image.classList.add('show');
            }, 10); // Small delay to trigger CSS transition
        });
        container.style.maxHeight = `${container.scrollHeight}px`;
    }

    function hideExtraImages(container) {
        const images = container.children;
        for (let i = 12; i < images.length; i++) {
            images[i].classList.remove('show');
            setTimeout(() => {
                images[i].classList.add('hidden');
            }, 500); // Delay to match the transition duration
        }
        container.style.maxHeight = '568px'; // Adjusted to match the height of 12 images
    }
});



document.addEventListener('DOMContentLoaded', function () {

    const imagesInput = document.getElementById('images');
    const fileList = document.getElementById('file-list');
    const previewContainer = document.getElementById('preview-container');
    const form = document.getElementById('question-form');
    const modal = document.getElementById("myModal");
    const modalImg = document.getElementById("img01");
    const prevBtn = document.querySelector('.prev');
    const nextBtn = document.querySelector('.next');
    const questionImages = document.querySelectorAll('.question .image-item img, .question .image-single img');
    let filesArray = [];
    let currentIndex = 0;

    questionImages.forEach((img, index) => {
        img.addEventListener('click', function(){
            modal.style.display = "flex";
            modalImg.src = this.src;
            currentIndex = index;
        });
    });

    modal.onclick = function(event) {
        if (event.target === modal) {
            modal.style.display = "none";
        }
    };

    imagesInput.addEventListener('change', function () {
        Array.from(imagesInput.files).forEach(file => {
            filesArray.push(file);
        });
        updateFileList();
        previewImages();
    });

    function updateFileList() {
        fileList.innerHTML = '';
        filesArray.forEach(file => {
            const fileName = document.createElement('p');
            fileList.appendChild(fileName);
        });
    }

    function previewImages() {
        previewContainer.innerHTML = '';
        filesArray.forEach((file, index) => {
            const reader = new FileReader();
            reader.onload = function (event) {
                const imageContainer = document.createElement('div');
                imageContainer.className = 'image-container';

                const image = document.createElement('img');
                image.src = event.target.result;
                image.alt = 'Preview Image';
                image.style.maxWidth = '200px';

                const removeButton = document.createElement('button');
                removeButton.textContent = 'X';
                removeButton.addEventListener('click', function () {
                    removeImage(index);
                });

                imageContainer.appendChild(image);
                imageContainer.appendChild(removeButton);
                previewContainer.appendChild(imageContainer);
            };
            reader.readAsDataURL(file);
        });
    }

    function showImageInModal(src) {
        modal.style.display = "flex";
        modalImg.src = src;
        modalImg.style.maxWidth = '90%';
        modalImg.style.maxHeight = '90%';
        modalImg.style.objectFit = 'contain';   
    }

    function showNextImage() {
        if (currentIndex < filesArray.length - 1) {
            currentIndex++;
            showImageInModal(URL.createObjectURL(filesArray[currentIndex]));
        } else if (currentIndex < questionImages.length - 1) {
            currentIndex++;
            showImageInModal(questionImages[currentIndex].src);
        }
    }

    function showPrevImage() {
        if (currentIndex > 0) {
            currentIndex--;
            if (currentIndex < filesArray.length) {
                showImageInModal(URL.createObjectURL(filesArray[currentIndex]));
            } else {
                showImageInModal(questionImages[currentIndex - filesArray.length].src);
            }
        }
    }

    prevBtn.addEventListener('click', function (event) {
        event.stopPropagation(); // Prevent modal from closing
        showPrevImage();
    });

    nextBtn.addEventListener('click', function (event) {
        event.stopPropagation(); // Prevent modal from closing
        showNextImage();
    });

    function removeImage(index) {
        filesArray.splice(index, 1);
        updateFileList();
        previewImages();
    }

    form.addEventListener('submit', (event) => {
        event.preventDefault();
        const formData = new FormData(form);
        formData.delete('images[]');
        filesArray.forEach(file => {
            formData.append('images[]', file);
        });

        fetch('?action=post', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                location.reload();
            }
        })
        .catch(error => {
            location.reload();
        });
    });

    function showAlert(message) {
        const alertBox = document.getElementById('alert-box');
        const alertMessage = document.getElementById('alert-message');
        alertMessage.textContent = message;
        alertBox.classList.add('show');
    }

    function closeAlert() {
        const alertBox = document.getElementById('alert-box');
        alertBox.classList.remove('show');
    }

    document.getElementById('close-alert-button').addEventListener('click', closeAlert);

    document.querySelectorAll('.question-actions a[href^="deletepost.php"]').forEach(link => {
        link.addEventListener('click', function (event) {
            event.preventDefault();
            if (confirm('Are you sure you want to delete this question?')) {
                let url = this.getAttribute('href');
                fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        let questionContainer = this.closest('.question');
                        if (questionContainer) {
                            questionContainer.remove();
                            showAlert('Question deleted successfully.');
                        }
                    } else {
                        showAlert('Failed to delete question.');
                    }
                })
            }
        });
    });


    var imgs = document.querySelectorAll('.question .images img');

    imgs.forEach(img => {
        img.onclick = function(){
            modal.style.display = "block";
            modalImg.src = this.src;
        }
    });

    span.onclick = function() { 
        modal.style.display = "none";
    }
});

function confirmDelete() {
    if (confirm("Are you sure you want to delete your account? This action cannot be undone.")) {
        document.getElementById("delete").value = "delete";
        document.getElementById("profile-form").submit();
    } else {
        showAlert('Account deletion canceled.');
        return false
    }
}

document.getElementById('close-alert-button').addEventListener('click', function() {
    document.getElementById('alert-box').style.display = 'none';
});

function togglePasswordVisibility() {
    var passwordInput = document.getElementById("password");
    var showIcon = document.getElementById("showIcon");
    var hideIcon = document.getElementById("hideIcon");
    
    if (passwordInput.type === "password") {
        passwordInput.type = "text";
        showIcon.style.display = "none";
        hideIcon.style.display = "";
    } else {
        passwordInput.type = "password";
        showIcon.style.display = "";
        hideIcon.style.display = "none";
    }
}