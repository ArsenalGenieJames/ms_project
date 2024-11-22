<?php
session_start();
include('db.php');

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch user details, including profile picture and username
$query = "SELECT profile_picture, username FROM users WHERE user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("User not found!");
}

$user = $result->fetch_assoc();

// Handle new post submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $content = $_POST['content'];

    // Handle media upload (image/video)
    $media_url = null;
    $post_type = 'text';  // Default to text

    if (isset($_FILES['media']) && $_FILES['media']['error'] === UPLOAD_ERR_OK) {
        // Validate file type and move the uploaded file
        $media_type = $_FILES['media']['type'];

        // Determine post type (image or video)
        if (strpos($media_type, 'image') !== false) {
            $post_type = 'image';
        } elseif (strpos($media_type, 'video') !== false) {
            $post_type = 'video';
        }

        // Generate a unique file name to avoid overwriting
        $target_dir = "uploads/posts/";
        $media_url = $target_dir . basename($_FILES['media']['name']);

        // Move the uploaded file to the target directory
        if (!move_uploaded_file($_FILES['media']['tmp_name'], $media_url)) {
            die("Error uploading the file.");
        }
    }

    // Prepare the SQL query for insertion
    $stmt = $conn->prepare("INSERT INTO posts (user_id, content, post_type, media_url) VALUES (?, ?, ?, ?)");

    if ($stmt === false) {
        die("Error preparing SQL query: " . $conn->error);
    }

    // Bind parameters and execute the statement
    $stmt->bind_param("isss", $user_id, $content, $post_type, $media_url);

    if ($stmt->execute()) {
        header("Location: home.php");
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }
}
?>
<!doctype html>
<html lang="en">

<head>
    <title>Post Your Thoughts</title>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous" />
    <style>
        body {
            padding: 20px;
        }

        .profile-section {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
        }

        img {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            margin-right: 10px;
        }

        textarea {
            width: 100%;
            height: 100px;
            margin-bottom: 10px;
            resize: none;
        }

        input[type="submit"] {
            width: 100%;
        }
    </style>
</head>

<body>
    <div class="container">
        
        <form method="POST" enctype="multipart/form-data">
            <div class="profile-section">
                <img src="<?php echo htmlspecialchars($user['profile_picture']); ?>" alt="Profile Picture">
                <b><?php echo htmlspecialchars($user['username']); ?></b>
            </div>
            <textarea name="content" placeholder="What's on your mind?" required></textarea>
            <input type="file" name="media">
            <input type="submit" value="Post" class="btn btn-primary">
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.min.js" integrity="sha384-BBtl+eGJRgqQAUMxJ7pMwbEyER4l1g+O15P+16Ep7Q9Q+zqX6gSbd85u4mG4QzX+" crossorigin="anonymous"></script>
</body>

</html>