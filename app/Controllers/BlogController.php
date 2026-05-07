<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Blog;
use App\Models\BlogComment;

class BlogController extends Controller
{
    private Blog $blog;
    private BlogComment $comment;

    public function __construct()
    {
        parent::__construct();
        $this->blog = new Blog();
        $this->comment = new BlogComment();
    }

    /**
     * List all blog posts
     */
    public function index()
    {
        $search = $_GET['search'] ?? '';
        
        if (!empty($search)) {
            $posts = $this->blog->search($search);
        } else {
            $posts = $this->blog->all();
        }

        $this->render('blog/index', [
            'posts' => $posts,
            'search' => $search,
            'pageTitle' => 'Blog'
        ]);
    }

    /**
     * Display single blog post with comments
     */
    public function detail()
    {
        $id = (int)($_GET['id'] ?? 0);
        if (!$id) {
            http_response_code(404);
            echo 'Post not found.';
            return;
        }

        $post = $this->blog->find($id);
        if (!$post) {
            http_response_code(404);
            echo 'Post not found.';
            return;
        }

        $comments = $this->comment->getByPost($id);
        $commentCount = count($comments);

        $this->render('blog/detail', [
            'post' => $post,
            'comments' => $comments,
            'commentCount' => $commentCount,
            'pageTitle' => 'Blog Post'
        ]);
    }

    /**
     * Create new blog post (admin)
     */
    public function create()
    {
        if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
            http_response_code(403);
            echo 'Unauthorized.';
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'user_id' => $_SESSION['user']['id'],
                'content' => $_POST['content'] ?? null,
                'image' => $_POST['image'] ?? null,
                'video' => $_POST['video'] ?? null
            ];

            if ($this->blog->create($data)) {
                $_SESSION['success'] = 'Post créé avec succès';
                header('Location: ' . $GLOBALS['baseUrl'] . '?route=blog/index');
                exit;
            } else {
                $_SESSION['error'] = 'Erreur lors de la création';
            }
        }

        $this->render('blog/create', [
            'pageTitle' => 'Créer un post'
        ]);
    }

    /**
     * Edit blog post (admin)
     */
    public function edit()
    {
        $id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
        if (!$id) {
            http_response_code(404);
            echo 'Post not found.';
            return;
        }

        $post = $this->blog->find($id);
        if (!$post) {
            http_response_code(404);
            echo 'Post not found.';
            return;
        }

        if (!isset($_SESSION['user']) || ($_SESSION['user']['role'] !== 'admin' && $_SESSION['user']['id'] != $post['user_id'])) {
            http_response_code(403);
            echo 'Unauthorized.';
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'content' => $_POST['content'] ?? $post['content'],
                'image' => $_POST['image'] ?? $post['image'],
                'video' => $_POST['video'] ?? $post['video']
            ];

            if ($this->blog->update($id, $data)) {
                $_SESSION['success'] = 'Post mis à jour avec succès';
                header('Location: ' . $GLOBALS['baseUrl'] . '?route=blog/detail&id=' . $id);
                exit;
            } else {
                $_SESSION['error'] = 'Erreur lors de la mise à jour';
            }
        }

        $this->render('blog/edit', [
            'post' => $post,
            'pageTitle' => 'Éditer un post'
        ]);
    }

    /**
     * Delete blog post (admin)
     */
    public function delete()
    {
        $id = (int)($_GET['id'] ?? 0);
        if (!$id) {
            http_response_code(400);
            echo 'Invalid ID.';
            return;
        }

        $post = $this->blog->find($id);
        if (!$post) {
            http_response_code(404);
            echo 'Post not found.';
            return;
        }

        if (!isset($_SESSION['user']) || ($_SESSION['user']['role'] !== 'admin' && $_SESSION['user']['id'] != $post['user_id'])) {
            http_response_code(403);
            echo 'Unauthorized.';
            return;
        }

        if ($this->blog->delete($id)) {
            $_SESSION['success'] = 'Post supprimé avec succès';
        } else {
            $_SESSION['error'] = 'Erreur lors de la suppression';
        }

        header('Location: ' . $GLOBALS['baseUrl'] . '?route=blog/index');
        exit;
    }

    /**
     * Add comment to post
     */
    public function addComment()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(400);
            echo 'Invalid request.';
            return;
        }

        if (!isset($_SESSION['user'])) {
            $_SESSION['error'] = 'Vous devez être connecté';
            header('Location: ' . $GLOBALS['baseUrl'] . '?route=login');
            exit;
        }

        $postId = (int)($_POST['post_id'] ?? 0);
        if (!$postId) {
            http_response_code(400);
            echo 'Invalid post ID.';
            return;
        }

        $data = [
            'post_id' => $postId,
            'user_id' => $_SESSION['user']['id'],
            'content' => $_POST['content'] ?? null
        ];

        if ($this->comment->create($data)) {
            $_SESSION['success'] = 'Commentaire ajouté';
        } else {
            $_SESSION['error'] = 'Erreur lors de l\'ajout du commentaire';
        }

        header('Location: ' . $GLOBALS['baseUrl'] . '?route=blog/detail&id=' . $postId);
        exit;
    }

    /**
     * Delete comment
     */
    public function deleteComment()
    {
        $id = (int)($_GET['id'] ?? 0);
        if (!$id) {
            http_response_code(400);
            echo 'Invalid ID.';
            return;
        }

        $comment = $this->comment->find($id);
        if (!$comment) {
            http_response_code(404);
            echo 'Comment not found.';
            return;
        }

        if (!isset($_SESSION['user']) || ($_SESSION['user']['role'] !== 'admin' && $_SESSION['user']['id'] != $comment['user_id'])) {
            http_response_code(403);
            echo 'Unauthorized.';
            return;
        }

        $postId = $comment['post_id'];
        if ($this->comment->delete($id)) {
            $_SESSION['success'] = 'Commentaire supprimé';
        } else {
            $_SESSION['error'] = 'Erreur lors de la suppression';
        }

        header('Location: ' . $GLOBALS['baseUrl'] . '?route=blog/detail&id=' . $postId);
        exit;
    }
}
?>
