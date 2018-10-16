<?php
    require '../templates/header.php';

    $id = $_GET['id'];
    $sql = "SELECT * FROM `books` WHERE id = $id";
    $result = mysqli_query($dbc, $sql);

    //if results is successfull and affected rows return rows then save in variable
    if($result && mysqli_affected_rows($dbc) > 0){
      $singleBook = mysqli_fetch_array($result, MYSQLI_ASSOC);
    } else if ($result && mysqli_affected_rows($dbc) == 0){
      die("ERROR 404");
      // header("Location: ../error 404");
    } else {
      die("ERROR, cannot get requested data");
    }
?>
 <div class="container">
     <div class="row mb-2">
         <div class="col">
             <h1><?= $singleBook['book_name'];?></h1>
         </div>
     </div>

     <div class="row mb-2">
         <div class="col">
             <a class="btn btn-outline-primary" href="./books/update.php?id=<?= $singleBook['id']; ?>">Edit</a>
             <a class="btn btn-outline-danger" href="./books/confirm_delete.php">Delete</a>
         </div>
     </div>

     <div class="row mb-2">
        <div class="col-xs-12 col-sm-4 align-self-center">
            <img class="img-fluid" data-src="holder.js/100px225?theme=thumb&bg=55595c&fg=eceeef&text=Thumbnail" alt="Card image cap">
        </div>
        <div class="col-xs-12 col-sm-8 align-self-center">
            <h3><?= $singleBook['book_name'];?></h3>
            <h4><?= $singleBook['author']; ?></h4>
        </div>
    </div>
    <div class="row mb-2">
        <div class="col-12">
            <p>
                <?= $singleBook['description'];?>
            </p>
        </div>
    </div>


 </div>


<?php require '../templates/footer.php' ?>
