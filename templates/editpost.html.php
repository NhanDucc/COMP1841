<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Post</title>
    <link rel="stylesheet" href="css\main.css">
</head>
<body>
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
        <section class="post-question">
            <?php
                    if ($question && $question['username'] == $_SESSION['username']) {
                        ?>
                        <form id="edit-question-form" method="post" action="editpost.php?id=<?php echo htmlspecialchars($question['id']); ?>" enctype="multipart/form-data">
                            <div class="form-group">
                            </div>
                            <div class="container">
                                <select id="module" name="module" class="module-select" required>   
                                    <option value="1" <?php echo ($question['module_id'] == 1) ? 'selected' : ''; ?>>Programming Foundations</option>
                                    <option value="2" <?php echo ($question['module_id'] == 2) ? 'selected' : ''; ?>>Principles of Software Engineering</option>
                                    <option value="3" <?php echo ($question['module_id'] == 3) ? 'selected' : ''; ?>>Systems Development</option>
                                    <option value="4" <?php echo ($question['module_id'] == 4) ? 'selected' : ''; ?>>Mathematics for Computer Science</option>
                                    <option value="5" <?php echo ($question['module_id'] == 5) ? 'selected' : ''; ?>>Principles of Security</option>
                                </select>
                            <ul id="moduleOptions" class="module-options">
                            </div>
                            <div class="form-group">
                                <textarea id="question" name="question" placeholder="Type your question here.." required><?php echo htmlspecialchars($question['question_text']); ?></textarea>
                            </div>
                            <div class="form-group">
                            <label for="images" class="custom-file-input">Choose File</label>
                            <input type="file" id="images" name="images[]" accept="image/*" multiple style="display: none;">
                                <div id="preview-container">
                                    <!-- Display existing images -->
                                    <?php if (!empty($question['images'])): ?>
                                        <?php $images = explode(',', $question['images']); ?>
                                        <?php foreach ($images as $image): ?>
                                            <div class="image-container">
                                                <img src="<?php echo htmlspecialchars($image); ?>" alt="Preview Image" style="max-width: 200px;">
                                                <button type="button" class="remove-image-btn" data-image="<?php echo htmlspecialchars($image); ?>">Remove</button>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>
                                <div id="file-list"></div>
                            </div>
                            <button type="submit">Update</button>
                        </form>
                    <?php } else {
                        echo '<p>You are not authorized to edit this question.</p>';
                    }
            ?>
        </section>
    </main>
    <div class="alert-box" id="alert-box">
        <p id="alert-message"></p>
        <button id="close-alert-button">OK</button>
    </div>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="script.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const imagesInput = document.getElementById('images');
            const fileList = document.getElementById('file-list');
            const previewContainer = document.getElementById('preview-container');
            const form = document.getElementById('edit-question-form');
            let filesArray = [];
            let existingImages = [];

            // Handle new image file selection
            imagesInput.addEventListener('change', function () {
                Array.from(imagesInput.files).forEach(file => {
                    filesArray.push(file);
                });
                updateFileList();
                previewImages();
            });

            // Function to update the list of selected files
            function updateFileList() {
                fileList.innerHTML = '';
                filesArray.forEach(file => {
                    const fileName = document.createElement('p');
                    fileList.appendChild(fileName);
                });
            }

            // Function to preview selected images
            function previewImages() {
                previewContainer.innerHTML = '';
                // Preview existing images
                existingImages.forEach(imageSrc => {
                    const imageContainer = document.createElement('div');
                    imageContainer.className = 'image-container';

                    const image = document.createElement('img');
                    image.src = imageSrc;
                    image.alt = 'Preview Image';
                    image.style.maxWidth = '200px';

                    const removeButton = document.createElement('button');
                    removeButton.textContent = 'X';
                    removeButton.addEventListener('click', function () {
                        removeExistingImage(imageSrc);
                    });

                    imageContainer.appendChild(image);
                    imageContainer.appendChild(removeButton);
                    previewContainer.appendChild(imageContainer);
                });
                // Preview new images
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

            // Function to remove a selected image
            function removeImage(index) {
                filesArray.splice(index, 1);
                updateFileList();
                previewImages();
            }

            // Function to remove an existing image
            function removeExistingImage(imageSrc) {
                existingImages = existingImages.filter(image => image !== imageSrc);
                previewImages();
            }

            // Handle form submission
            form.addEventListener('submit', (event) => {
                event.preventDefault();
                const formData = new FormData(form);
                formData.delete('images[]');
                filesArray.forEach(file => {
                    formData.append('images[]', file);
                });
                formData.append('existing_images', existingImages.join(','));

                fetch('editpost.php?id=<?php echo htmlspecialchars($question['id']); ?>', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showAlert(data.message);
                    } else {
                        showAlert(data.message);
                    }
                })
                .catch(error => {
                    showAlert('error');
                });
                
            });

            // Function to show an alert
            function showAlert(message) {
                const alertBox = document.getElementById('alert-box');
                const alertMessage = document.getElementById('alert-message');
                alertMessage.textContent = message;
                alertBox.classList.add('show');
            }

            // Function to close the alert
            function closeAlert() {
    const alertBox = document.getElementById('alert-box');
    alertBox.classList.remove('show');

    // Redirect to index.php after closing the alert
    window.location.href = 'index.php';
}

            // Handle close alert button click
            document.getElementById('close-alert-button').addEventListener('click', closeAlert);

            // Handle remove existing image button click
            document.querySelectorAll('.image-container .remove-image-btn').forEach(btn => {
                btn.addEventListener('click', function () {
                    const imageSrc = btn.getAttribute('data-image');
                    removeExistingImage(imageSrc);
                });
            });

            // Load existing images into array
            <?php if (!empty($question['images'])): ?>
                existingImages = <?php echo json_encode(explode(',', $question['images'])); ?>;
                previewImages();
            <?php endif; ?>
        });

        function create_custom_dropdowns() {
  $('select').each(function(i, select) {
    if (!$(this).next().hasClass('dropdown')) {
      $(this).after('<div class="dropdown ' + ($(this).attr('class') || '') + '" tabindex="0"><span class="current"></span><div class="list"><ul></ul></div></div>');
      var dropdown = $(this).next();
      var options = $(select).find('option');
      var selected = $(this).find('option:selected');
      dropdown.find('.current').html(selected.data('display-text') || selected.text());
      options.each(function(j, o) {
        var display = $(o).data('display-text') || '';
        dropdown.find('ul').append('<li class="option ' + ($(o).is(':selected') ? 'selected' : '') + '" data-value="' + $(o).val() + '" data-display-text="' + display + '">' + $(o).text() + '</li>');
      });
    }
  });
}

// Event listeners

// Open/close
$(document).on('click', '.dropdown', function(event) {
  $('.dropdown').not($(this)).removeClass('open');
  $(this).toggleClass('open');
  if ($(this).hasClass('open')) {
    $(this).find('.option').attr('tabindex', 0);
    $(this).find('.selected').focus();
  } else {
    $(this).find('.option').removeAttr('tabindex');
    $(this).focus();
  }
});
// Close when clicking outside
$(document).on('click', function(event) {
  if ($(event.target).closest('.dropdown').length === 0) {
    $('.dropdown').removeClass('open');
    $('.dropdown .option').removeAttr('tabindex');
  }
  event.stopPropagation();
});
// Option click
$(document).on('click', '.dropdown .option', function(event) {
  $(this).closest('.list').find('.selected').removeClass('selected');
  $(this).addClass('selected');
  var text = $(this).data('display-text') || $(this).text();
  $(this).closest('.dropdown').find('.current').text(text);
  $(this).closest('.dropdown').prev('select').val($(this).data('value')).trigger('change');
});

// Keyboard events
$(document).on('keydown', '.dropdown', function(event) {
  var focused_option = $($(this).find('.list .option:focus')[0] || $(this).find('.list .option.selected')[0]);
  // Space or Enter
  if (event.keyCode == 32 || event.keyCode == 13) {
    if ($(this).hasClass('open')) {
      focused_option.trigger('click');
    } else {
      $(this).trigger('click');
    }
    return false;
    // Down
  } else if (event.keyCode == 40) {
    if (!$(this).hasClass('open')) {
      $(this).trigger('click');
    } else {
      focused_option.next().focus();
    }
    return false;
    // Up
  } else if (event.keyCode == 38) {
    if (!$(this).hasClass('open')) {
      $(this).trigger('click');
    } else {
      var focused_option = $($(this).find('.list .option:focus')[0] || $(this).find('.list .option.selected')[0]);
      focused_option.prev().focus();
    }
    return false;
  // Esc
  } else if (event.keyCode == 27) {
    if ($(this).hasClass('open')) {
      $(this).trigger('click');
    }
    return false;
  }
});

$(document).ready(function() {
  create_custom_dropdowns();
});
</script>
<script type="text/javascript">

  var _gaq = _gaq || [];
  _gaq.push(['_setAccount', 'UA-36251023-1']);
  _gaq.push(['_setDomainName', 'jqueryscript.net']);
  _gaq.push(['_trackPageview']);

  (function() {
    var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
    ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
  })();
  
    </script>
</body>
</html>