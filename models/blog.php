<?php
class Blog {
    private $id;
    private $user_id;
    private $content;
    private $image;
    private $video;
    private $created_at;
    private $db;
    public function __construct($db = null) {
        $this->db = $db;
    }
    public function getId() { return $this->id; }
    public function getUserId() { return $this->user_id; }
    public function getContent() { return $this->content; }
    public function getImage() { return $this->image; }
    public function getVideo() { return $this->video; }
    public function getCreatedAt() { return $this->created_at; }
    public function getDb() { return $this->db; }
    // setter
    public function setId($id) { $this->id = $id; }
    public function setUserId($user_id) { $this->user_id = $user_id; }
    public function setContent($content) { $this->content = $content; }
    public function setImage($image) { $this->image = $image; }
    public function setVideo($video) { $this->video = $video; }
    public function setCreatedAt($created_at) { $this->created_at = $created_at; }
    public function setDb($db) { $this->db = $db; }
    
    //post
    public function createPost() { return null; }
    public function getPosts() { return []; }
    public function updatePost() { return false; }
    public function deletePost() { return false; }
    //commntr
    public function createComment() { return false; }
    public function getCommentsByPost() { return []; }
    public function updateComment() { return false; }
    public function deleteComment() { return false; }
    //react
    public function reactToPostAjax() { return false; }
    
 
}
?>