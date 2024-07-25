<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact</title>
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
        <section class="contact-info">
            <h2>Contact Us</h2>
            <form action="send_email.php" method="post">
                <label for="name">Full Name</label>
                <input type="text" id="name" name="name" required>
            
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required>

                <label for="message">Message</label>
                <textarea id="message" name="message" placeholder='Enter your message here...' col required></textarea><br />

                <input type="submit" value="Send Message">
            </form>
        </section>
    </main>
</body>
</html>
