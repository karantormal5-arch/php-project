<?php
$categories = mysqli_query($conn,"
SELECT * FROM categories 
WHERE parent_id=0
");
?>

<div class="container mt-4">

<h4>Main Categories</h4>

<div class="row">

<?php while($cat = mysqli_fetch_assoc($categories)){ ?>

<div class="col-md-2">

<a href="category.php?id=<?php echo $cat['id']; ?>" 
class="btn btn-dark w-100 mb-2">

<?php echo $cat['name']; ?>

</a>

</div>

<?php } ?>

</div>

</div>