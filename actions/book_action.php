<?php
require_once '../includes/config.php';
require_once '../includes/books.php';

if (!isAdmin()) {
    redirect('../login.php');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('books.php');
}

$action = $_POST['action'] ?? '';

if ($action === 'add') {
    $data = [
        'title'            => sanitize($_POST['title'] ?? ''),
        'author'           => sanitize($_POST['author'] ?? ''),
        'category'         => sanitize($_POST['category'] ?? ''),
        'isbn'             => sanitize($_POST['isbn'] ?? ''),
        'publication_year' => (int)($_POST['publication_year'] ?? 0) ?: null,
        'publisher'        => sanitize($_POST['publisher'] ?? ''),
        'description'      => sanitize($_POST['description'] ?? ''),
        'total_quantity'   => max(1, (int)($_POST['total_quantity'] ?? 1)),
    ];

    if (empty($data['title']) || empty($data['author']) || empty($data['category'])) {
        setFlash('error', 'Title, author, and category are required.');
        redirect('books.php');
    }

    $result = addBook($data);
    setFlash($result['success'] ? 'success' : 'error', $result['message']);
    redirect('books.php');
}

if ($action === 'update') {
    $id = (int)($_POST['book_id'] ?? 0);
    $data = [
        'title'            => sanitize($_POST['title'] ?? ''),
        'author'           => sanitize($_POST['author'] ?? ''),
        'category'         => sanitize($_POST['category'] ?? ''),
        'isbn'             => sanitize($_POST['isbn'] ?? ''),
        'publication_year' => (int)($_POST['publication_year'] ?? 0) ?: null,
        'publisher'        => sanitize($_POST['publisher'] ?? ''),
        'description'      => sanitize($_POST['description'] ?? ''),
        'total_quantity'   => max(1, (int)($_POST['total_quantity'] ?? 1)),
    ];

    $result = updateBook($id, $data);
    setFlash($result['success'] ? 'success' : 'error', $result['message']);
    redirect('books.php');
}

if ($action === 'delete') {
    $id = (int)($_POST['book_id'] ?? 0);
    $result = deleteBook($id);
    setFlash($result['success'] ? 'success' : 'error', $result['message']);
    redirect('books.php');
}

redirect('books.php');
?>
