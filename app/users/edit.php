<?php
declare(strict_types=1);
require __DIR__.'/../autoload.php';
// In this file we edit user data.
$oldEmail = $_SESSION['user']['email'];
$oldUsername = $_SESSION['user']['username'];
$oldBio = $_SESSION['user']['bio'];
$id = $_SESSION['user']['id'];

if(isset($_POST['email'],$_POST['username'],$_POST['bio'])) {
    if($_POST['email']!=='') {
        $newEmail = trim(filter_var($_POST['email'],FILTER_SANITIZE_EMAIL));
    } else {
        $newEmail = $oldEmail;
    }
    if($_POST['username']!=='') {
        $newUsername = trim(filter_var($_POST['username'],FILTER_SANITIZE_STRING));
    } else {
        $newUsername = $oldUsername;
    }
    if($_POST['bio']!=='') {
        $newBio = trim(filter_var($_POST['bio'], FILTER_SANITIZE_STRING));
    } else {
        $newBio = $oldBio;
    }
    $queryUpdate = 'UPDATE users SET email = :email, username = :username, bio = :bio WHERE id = :id';
    $statement = $pdo->prepare($queryUpdate);
    $statement->execute([
        ':email' => $newEmail,
        ':username' => $newUsername,
        ':bio' => $newBio,
        ':id' => $id
    ]);

    if($newUsername) {
        $queryFetchPosts = 'SELECT * FROM posts WHERE user_id = :user_id';
        $statement = $pdo->prepare($queryFetchPosts);
        $statement->bindParam(':user_id', $id, PDO::PARAM_INT);
        $statement->execute();
        $posts = $statement->fetchAll(PDO::FETCH_ASSOC);

        foreach ($posts as $post) {
            $oldImage = $post['post_image'];
            $postId = $post['post_id'];
            $oldNumExtension = str_replace($oldUsername, "", $oldImage);
            $tempArray = [$newUsername, $oldNumExtension];
            $newImage = implode("",$tempArray);
            $queryUpdatePost = 'UPDATE posts SET post_image = :new_image WHERE post_id = :post_id';
            $statement = $pdo->prepare($queryUpdatePost);
            $statement->execute([
                ':post_id' => $postId,
                ':new_image' => $newImage
            ]);
            rename('../database/posts/'.$oldImage,'../database/posts/'.$newImage);
        }
    }

}
    
if($_SESSION['avatar']) {
    
    if($newUsername) {
        $extension = explode('.',$_SESSION['avatar']);
        $image = $newUsername . '.' . $extension[1];
        
        $queryFetchAvatars = 'SELECT * FROM avatars WHERE username = :username';
        $statement = $pdo->prepare($queryFetchAvatars);
        $statement->bindParam(':username', $oldUsername, PDO::PARAM_STR);
        $statement->execute();
        $avatar = $statement->fetch(PDO::FETCH_ASSOC);
        
        if($avatar===false) {
            $queryInsertAvatar = 'INSERT INTO avatars (avatar_id, username, image) VALUES (:avatar_id, :username, :image)';
            $statement = $pdo->prepare($queryInsertAvatar);
            $statement->execute([
                ':avatar_id' => $id,
                ':username' => $newUsername,
                ':image' => $image
            ]);
            
            rename('../database/avatars/'.$_SESSION['avatar'],'../database/avatars/'.$image);
            $_SESSION['avatar'] = $image;
        } else {
            $queryUpdateAvatar = 'UPDATE avatars SET username = :username, image = :image WHERE avatar_id = :id';
            $statement = $pdo->prepare($queryUpdateAvatar);
            $statement->execute([
                ':id' => $id,
                ':username' => $newUsername,
                ':image' => $image
            ]);
            
            rename('../database/avatars/'.$_SESSION['avatar'],'../database/avatars/'.$image);
            $_SESSION['avatar'] = $image;
        }
    }
}

$_SESSION['user']['email'] = $newEmail;
$_SESSION['user']['username'] = $newUsername;
$_SESSION['user']['bio'] = $newBio;
redirect('/home.php');