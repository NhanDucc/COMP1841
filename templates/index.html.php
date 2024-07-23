<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home Page</title>
    <link rel="stylesheet" href="css\main.css">
</head>
<body onload="init()">
<header>
    <div class="header-container">
        <div class="search-bar">
            <form id="search-form" method="get" action="index.php">
                <input type="text" name="search" placeholder="Search...">
                <button type="submit">Search</button>
            </form>
        </div>
        <nav>
            <a href="index.php" class="nav-link">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-house-fill" viewBox="0 0 16 16">
                    <path d="M8.707 1.5a1 1 0 0 0-1.414 0L.646 8.146a.5.5 0 0 0 .708.708L8 2.207l6.646 6.647a.5.5 0 0 0 .708-.708L13 5.793V2.5a.5.5 0 0 0-.5-.5h-1a.5.5 0 0 0-.5.5v1.293z"/>
                    <path d="m8 3.293 6 6V13.5a1.5 1.5 0 0 1-1.5 1.5h-9A1.5 1.5 0 0 1 2 13.5V9.293z"/>
                </svg> Home
            </a>
            <a href='addquestion.php' class="nav-link">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-plus-square-fill" viewBox="0 0 16 16">
                    <path d="M2 0a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2zm6.5 4.5v3h3a.5.5 0 0 1 0 1h-3v3a.5.5 0 0 1-1 0v-3h-3a.5.5 0 0 1 0-1h3v-3a.5.5 0 0 1 1 0"/>
                </svg> Add Question
            </a>
        <img src="image\user.png" alt="profile" class="user-pic" onclick="toggleMenu()">
                <div class="sub-menu-wrap" id="subMenu">
                    <div class="sub-menu">
                        <div class="user-info">
                            <img src="image\user.png" alt="profile" class="userpic">
                            <h1><?php echo htmlspecialchars($_SESSION['username']); ?></h1>
                        </div>
                        <hr />
                        <a href="profile.php" class="sub-menu-link">
                            <i data-feather="user" class="icon"></i>
                            <p>Edit Profile</p>
                            <span> > </span>
                        </a>
                        <a href="#" class="sub-menu-link">
                            <i data-feather="settings" class="icon"></i>
                            <p>Settings & Privacy</p>
                            <span> > </span>
                        </a>
                        <a href="contact.php" class="sub-menu-link">
                            <i data-feather="help-circle" class="icon"></i>
                            <p>Help & Support</p>
                            <span> > </span>
                        </a>
                        <a href="login.php" class="sub-menu-link">
                            <i data-feather="log-out" class="icon"></i>
                            <p>Log Out</p>
                            <span> > </span>
                        </a>
                    </div>
                </div>
        </nav>
    </div>
</header>
<script>
            feather.replace();
        </script>
        <script>
            let subMenu = document.getElementById('subMenu');
            function toggleMenu() {
                subMenu.classList.toggle('open-menu');
            }
        </script>
<main>
    <h2>List of Questions</h2>
    <section class="questions-list">
        <div id="questions-container">
            <?php foreach ($questions as $question): ?>
                <div class="question">
                    <p class='user'><?php echo htmlspecialchars($question['username']); ?></p><br />
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
                    <?php if ($_SESSION['username'] == $question['username']): ?>
                        <div class="question-actions">
                            <a href="editpost.php?id=<?php echo $question['id']; ?>">Edit</a>
                            <a href="deletepost.php?id=<?php echo $question['id']; ?>">Delete</a>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </section>
</main>
</body>
</html>
