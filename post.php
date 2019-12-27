<?php require __DIR__.'/views/header.php'; ?>

<form action="app/posts/store.php" method="post" enctype="multipart/form-data">
        <div class="form-group">
            <label for="caption">Post Caption</label>
            <input class="form-control" type="text" name="caption">
            <small class="form-text text-muted">Please enter a caption to accompany your post.</small>
        </div><!-- /form-group -->
        
        <div class="form-group">
            <input type="file" accept=".jpg, .jpeg, .png" name="post" id="post" required> 
        </div>

        <button type="submit">Upload</button>
    </form>

<?php require __DIR__.'/views/footer.php'; ?>