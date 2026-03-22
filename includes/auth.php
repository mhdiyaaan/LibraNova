<?php
require_once 'config.php';

// Handle Login
function handleLogin($email, $password) {
    $conn = getDBConnection();
    $email = sanitize($email);
    
    $stmt = $conn->prepare("SELECT id, name, email, password, role, status FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        
        if ($user['status'] === 'suspended') {
            return ['success' => false, 'message' => 'Your account has been suspended. Contact admin.'];
        }
        
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['role'] = $user['role'];
            
            $conn->close();
            return ['success' => true, 'role' => $user['role'], 'name' => $user['name']];
        }
    }
    
    $conn->close();
    return ['success' => false, 'message' => 'Invalid email or password.'];
}

// Handle Register
function handleRegister($name, $email, $password, $phone, $address) {
    $conn = getDBConnection();
    $name = sanitize($name);
    $email = sanitize($email);
    $phone = sanitize($phone);
    $address = sanitize($address);
    
    // Check if email exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        $conn->close();
        return ['success' => false, 'message' => 'Email already registered.'];
    }
    
    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
    
    $stmt = $conn->prepare("INSERT INTO users (name, email, password, phone, address, role) VALUES (?, ?, ?, ?, ?, 'user')");
    $stmt->bind_param("sssss", $name, $email, $hashedPassword, $phone, $address);
    
    if ($stmt->execute()) {
        $conn->close();
        return ['success' => true, 'message' => 'Registration successful! Please login.'];
    }
    
    $conn->close();
    return ['success' => false, 'message' => 'Registration failed. Please try again.'];
}

// Process POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'login') {
        $result = handleLogin($_POST['email'] ?? '', $_POST['password'] ?? '');
        if ($result['success']) {
            if ($result['role'] === 'admin') {
                redirect('../admin/dashboard.php');
            } else {
                redirect('../index.php');
            }
        } else {
            setFlash('error', $result['message']);
            redirect('../login.php');
        }
    }
    
    if ($action === 'register') {
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        
        if ($password !== $confirmPassword) {
            setFlash('error', 'Passwords do not match.');
            redirect('../register.php');
        }
        
        if (strlen($password) < 6) {
            setFlash('error', 'Password must be at least 6 characters.');
            redirect('../register.php');
        }
        
        $result = handleRegister(
            $_POST['name'] ?? '',
            $_POST['email'] ?? '',
            $password,
            $_POST['phone'] ?? '',
            $_POST['address'] ?? ''
        );
        
        if ($result['success']) {
            setFlash('success', $result['message']);
            redirect('../login.php');
        } else {
            setFlash('error', $result['message']);
            redirect('../register.php');
        }
    }
    
    if ($action === 'logout') {
        session_destroy();
        redirect('../login.php');
    }
}
?>
