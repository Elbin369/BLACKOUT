<?php
/**
 * PROJECT: BLACKOUT — E Corp Corporate News & Blog API
 * Implements H01 (Database Reaper — SQL Injection)
 */

require_once __DIR__ . '/../../application/config.php';
require_once __DIR__ . '/../../application/db.php';
require_once __DIR__ . '/../../application/helpers.php';

header('Content-Type: application/json; charset=utf-8');

$search = $_GET['search'] ?? $_GET['q'] ?? '';
$category = $_GET['category'] ?? '';

try {
    $pdo = Database::getConnection();

    // VULNERABILITY H01: Deliberately unescaped SQL query string concatenation
    if (!empty($search)) {
        // Deliberately vulnerable to UNION or boolean based SQLi
        $sql = "SELECT id, title, summary, content, category, author FROM blog_posts WHERE is_published = 1 AND (title LIKE '%$search%' OR content LIKE '%$search%')";
        $stmt = $pdo->query($sql);
        $articles = $stmt->fetchAll();
    } elseif (!empty($category)) {
        $sql = "SELECT id, title, summary, content, category, author FROM blog_posts WHERE is_published = 1 AND category = '$category'";
        $stmt = $pdo->query($sql);
        $articles = $stmt->fetchAll();
    } else {
        $sql = "SELECT id, title, summary, content, category, author FROM blog_posts WHERE is_published = 1 ORDER BY id DESC";
        $stmt = $pdo->query($sql);
        $articles = $stmt->fetchAll();
    }

    json_response([
        'status' => 'success',
        'count' => count($articles),
        'articles' => $articles
    ]);

} catch (PDOException $e) {
    // Reveal helpful error message for SQLi enumeration
    json_response([
        'status' => 'database_error',
        'message' => 'Query execution failed: ' . $e->getMessage()
    ], 500);
} catch (Exception $e) {
    json_response(['status' => 'error', 'message' => $e->getMessage()], 500);
}
