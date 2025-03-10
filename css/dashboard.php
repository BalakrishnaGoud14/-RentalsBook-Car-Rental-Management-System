if ($_SERVER["REQUEST_METHOD"] === "POST") {

if (isset($_POST['email']) && isset($_POST['password'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    // Checking if user exists
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();

        if ($user['role'] == 'admin') {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['full_name'];
            $_SESSION['role'] = 'admin';  // Set role as admin
            header("Location: admin_panel.php");  // Redirect to admin panel
            exit;
        }

        else {
            $error_message = "Invalid password!";
        }
    } else {
        $error_message = "User does not exist!";
    }

    $stmt->close();
} else {
    $error_message = "User does not exist!";
}
}

