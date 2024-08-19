<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile</title>
    <link rel="stylesheet" href="css\mainn.css">
</head>
<body onload="init()">
<header>
        <div class="header-container">
            <nav>
                <a href="index.php" class="nav-link">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-caret-left-fill" viewBox="0 0 16 16">
                        <path d="m3.86 8.753 5.482 4.796c.646.566 1.658.106 1.658-.753V3.204a1 1 0 0 0-1.659-.753l-5.48 4.796a1 1 0 0 0 0 1.506z"/>
                </svg> Back
            </a>
            </nav>
        </div>
    </header>   
    <main>
        <section class="profile-section">
            <h2>Your Profile</h2>
            <form method="post" action="profile.php" id="profile-form">
                <div class="form-group">
                    <label for="username">Username:</label>
                    <input type="text" id="username" name="username" value="" placeholder="<?php echo htmlspecialchars($_SESSION['username']); ?>" disabled>
                </div>
                <div class="form-group">
                    <label for="email">Full Name</label>
                    <input type="text" id="fullname" name="fullname" placeholder="<?php echo isset($user['fullname']) ? htmlspecialchars($user['fullname']) : ''; ?>">
                </div>
                <div class="form-group">
                    <label for="email">Phone Number</label>
                    <input type="text" id="phonenumber" name="phonenumber" placeholder="<?php echo isset($user['phonenumber']) ? htmlspecialchars($user['phonenumber']) : ''; ?>">
                </div>
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" placeholder="<?php echo isset($user['email']) ? htmlspecialchars($user['email']) : ''; ?>">
                </div>
                <div class="form-group">
                    <label for="email">Gender</label>
                    <input type="text" id="gender" name="gender" placeholder="<?php echo isset($user['gender']) ? htmlspecialchars($user['gender']) : ''; ?>">
                </div>
                <div class="form-group">
                    <label for="password">New Password:</label>
                    <input type="password" id="password" name="password">
                </div>
                <button type="submit">Update Profile</button>
                <button type="button" onclick="confirmDelete()">Delete Account</button>
                <input type="hidden" name="delete" id="delete" value="0">
            </form>
        </section>
        
        <h2>Your Questions</h2>
        <section class="questions-list">
            <div id="questions-container">
                <?php foreach ($questions as $question): ?>
                    <div class="question">
                        <h1><?php echo htmlspecialchars($question['module_name']); ?></h1><br />
                        <p class='question-show'>Question: <?php echo htmlspecialchars($question['question_text']); ?></p>
                        <?php if (!empty($question['images'])): ?>
                            <?php $images = explode(',', $question['images']); ?>
                            <?php if (count($images) === 1): ?>
                                <div class="image-single">
                                    <img src="<?php echo htmlspecialchars($images[0]); ?>" alt="Question Image">
                                </div>
                            <?php else: ?>
                                <div class="image-grid large">
                                    <?php foreach ($images as $image): ?>
                                        <div class="image-item">
                                            <img src="<?php echo htmlspecialchars($image); ?>" alt="Question Image">
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>
                        <p><em class='posted'>Posted on: <?php echo htmlspecialchars($question['time_post']); ?></em></p>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
    </main>

    <div id="myModal" class="modal">
        <span class="prev">&laquo;</span>
        <span class="next">&raquo;</span>
        <img class="modal-content" id="img01">
    </div>

    <div class="alert-box" id="alert-box">
        <p id="alert-message"></p>
        <button id="close-alert-button">OK</button>
    </div>
    <script>
document.addEventListener("DOMContentLoaded", function () {
    const modal = document.getElementById("myModal");
    const modalImg = document.getElementById("img01");
    const prevBtn = document.querySelector('.prev');
    const nextBtn = document.querySelector('.next');
    const questionImages = document.querySelectorAll('.question .image-item img, .question .image-single img');
    const filesArray = []; // Chỉnh sửa nếu có filesArray
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

function showImageInModal(src) {
    modal.style.display = "flex";
    modalImg.src = src;
    modalImg.style.maxWidth = '90%';
    modalImg.style.maxHeight = '90%';
    modalImg.style.objectFit = 'contain';
}

let currentQuestionImageCount = questionImages.length;

function showNextImage() {
    currentIndex++;
    if (currentIndex >= currentQuestionImageCount) {
        // Nếu đang ở ảnh cuối cùng của bài post, quay lại ảnh đầu tiên
        currentIndex = 0;
    }
    if (filesArray.length > 0 && currentIndex < filesArray.length) {
        showImageInModal(filesArray[currentIndex]);
    } else {
        showImageInModal(questionImages[currentIndex].src);
    }
}
prevBtn.addEventListener('click', function (event) {
        event.stopPropagation(); // Ngăn modal đóng lại
        showPrevImage();
    });

    nextBtn.addEventListener('click', function (event) {
        event.stopPropagation(); // Ngăn modal đóng lại
        showNextImage();
    });
});

        
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

// Hàm hiển thị thông báo
function showAlert(message) {
    const alertBox = document.getElementById("alert-box");
    const alertMessage = document.getElementById("alert-message");
    alertMessage.textContent = message;
    alertBox.style.display = "block";
}

document.getElementById('close-alert-button').addEventListener('click', function() {
    document.getElementById('alert-box').style.display = 'none';
    window.location.href = "profile.php";
});


// Kiểm tra và hiển thị thông báo từ PHP session
<?php
if (isset($_SESSION['message'])) {
    echo "showAlert('{$_SESSION['message']}');";
    unset($_SESSION['message']);
}
?>
</script>
<script src="script.js"></script>
</body>
</html>
