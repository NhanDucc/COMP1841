<?php
try{
$sql = 'SELECT id, module_name FROM module';
    $result = $pdo->query($sql);
    $modules = $result->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Lỗi khi thực thi câu lệnh SQL: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Question</title>
    <link rel="stylesheet" href="css\mainn.css">
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
        <form id="question-form" method="post" action="?action=post" enctype="multipart/form-data">
          <h2>Post a Question</h2>
            <div class="form-group">
                <select id="module" name="module">
                  <?php foreach ($modules as $module): ?>
                    <option value="<?php echo $module['id']; ?>"><?php echo htmlspecialchars($module['module_name']); ?></option>
                  <?php endforeach; ?>
                </select>
            </div>
            <textarea id="question" name="question" placeholder="Type your question here.." required></textarea>
            <div class="form-group">
                <label for="images" class="custom-file-input">Choose Image</label>
                <input type="file" id="images" name="images[]" accept="image/*" multiple style="display: none;">
                <div id="preview-container"></div>
                <div id="file-list"></div>
            </div>
            <button type="submit">Post</button>
        </form>
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
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="script.js"></script>
<script>
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
  if (event.keyCode == 32 || event.keyCode == 13) {
    if ($(this).hasClass('open')) {
      focused_option.trigger('click');
    } else {
      $(this).trigger('click');
    }
    return false;
  } else if (event.keyCode == 40) {
    if (!$(this).hasClass('open')) {
      $(this).trigger('click');
    } else {
      focused_option.next().focus();
    }
    return false;
  } else if (event.keyCode == 38) {
    if (!$(this).hasClass('open')) {
      $(this).trigger('click');
    } else {
      var focused_option = $($(this).find('.list .option:focus')[0] || $(this).find('.list .option.selected')[0]);
      focused_option.prev().focus();
    }
    return false;
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