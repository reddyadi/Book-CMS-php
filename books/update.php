<?php
    require '../templates/header.php';

    // import the Intervention Image Manager Class
    use Intervention\Image\ImageManager;

    $id = $_GET['id'];//$_GET used to grab id from database//
    $sql = "SELECT * FROM `books` WHERE id = $id";
    $result = mysqli_query($dbc, $sql);

    //if results is successfull and affected rows return rows then save in variable
    if($result && mysqli_affected_rows($dbc) > 0){
      $singleBook = mysqli_fetch_array($result, MYSQLI_ASSOC);
      $orginalName = $singleBook['image_name'];
    } else if ($result && mysqli_affected_rows($dbc) == 0){
      die("ERROR 404");
      // header("Location: ../error 404");
    } else {
      die("ERROR, cannot get requested data");
    }

    if($_POST){
      extract($_POST);
      $errors = array();

      //variables are created on the DOM using name tags, name tags are the variables//
      if(!$title){
        array_push($errors, "Title is required, please enter a value");
      } else if(strlen($title) < 2){
        array_push($errors, "Please enter atleast 2 characters for the Title");
      } else if(strlen($title) > 100){
        array_push($errors, "The title can't be more than 100 characters");
      }

      if(!$author){
          array_push($errors, "An author is required, please enter a value");
      } else if(strlen($author) < 2){
          array_push($errors, "Please enter at least 2 characters for the author");
      } else if(strlen($author) > 100){
          array_push($errors, "The Author Name can't be more than 100 characters");
      }

      if(!$description){
          array_push($errors, "A description is required, please enter a value");
      } else if(strlen($description) < 10){
          array_push($errors, "The description must be at least 10 characters long");
      } else if(strlen($description) > 1000){
          array_push($errors, "The description needs to be less than 1000 characters");
      }

      //image validation in update
      if(file_exists($_FILES["image"]["tmp_name"])){
        $fileSize = $_FILES["image"]["size"];
        $fileTmp = $_FILES["image"]["tmp_name"];
        $fileType = $_FILES["image"]["type"];

        if($fileSize > 5000000){
            array_push($errors, "The file is to large, must be under 5MB");
        } else {
            $validExtensions = array("jpeg", "jpg", "png");
            $fileNameArray = explode(".", $_FILES["image"]["name"]);
            $fileExt = strtolower(end($fileNameArray));
            if(in_array($fileExt, $validExtensions) === false){
                array_push($errors, "File type not allowed, can only be a jpg or png");
            }
        }
      }

      //If errors array is empty then start saving details to database
      if(empty($errors)){
        $title = mysqli_real_escape_string($dbc, $title);
        $author = mysqli_real_escape_string($dbc, $author);
        $description = mysqli_real_escape_string($dbc, $description);

        //Update query copied from myphpadmin//
        $sql = "UPDATE `books` SET `book_name`= '$title',`author`= '$author',`description`= '$description'";
        //new image filename is created for new uploaded image
        if(file_exists($_FILES['image']['tmp_name'])){
          $newFileName = uniqid() .".".  $fileExt;
          $filename = mysqli_real_escape_string($dbc, $newFileName);
          $sql .= ", `image_name`=,'$filename'"; //.= concatenate in php to add new image to the URL//
        }
        //updates image for the id of affected row//
        $sql .= " WHERE id = $id";
        // die($sql);

        $result = mysqli_query($dbc, $sql);
        if($result && mysqli_affected_rows($dbc) > 0){
          //checks if a file is uploaded to the DOM, using the name tag which creates a tmp_name, if uploaded then unlink old image from id//
          if(file_exists($_FILES['image']['tmp_name'])){
            unlink("../images/uploads/$originalName");
            unlink("../images/uploads/thumbnails/$originalName");
            unlink("../images/uploads/medium/$originalName");

            //creates new image upload for the updated image into the image folder//
            $destination = "../images/uploads";
            if(! is_dir($destination) ){
                mkdir("../images/uploads/", 0777, true);
            }
            // move_uploaded_file($fileTmp, $destination."/".$newFileName);
            $manager = new ImageManager();
            $mainImage = $manager->make($fileTmp);
            $mainImage->save($destination."/".$newFileName, 100);
            $thumbnailImage = $manager->make($fileTmp);
            $thumbDestination = "../images/uploads/thumbnails";
            if(! is_dir($thumbDestination)){
                mkdir("../images/uploads/thumbnails/", 0777, true);
            }
            $thumbnailImage->resize(300, null, function($constraint){
                $constraint->aspectRatio();
                $constraint->upsize();
            });
            $thumbnailImage->save($thumbDestination."/".$newFileName, 100);

            $thumbnailImage->save($thumbDestination."/".$newFileName, 100);

            $mediumImage = $manager->make($fileTmp);
            $mediumDestination = "../images/uploads/medium";
            if(! is_dir($mediumDestination)){
              mkdir("../images/uploads/medium", 007, true);
            }
            $mediumImage->resize(300, null, function($constraint){
              $constraint->aspectRatio();
              $constraint->upsize();
            });
            $mediumImage->save($mediumDestination."/".$newFileName, 100);
          }
          header("Location: book.php?id=$id");
        } else {
          die("ERROR: Something went wrong, can't update this entry");
        }
      }
  }
 ?>

 <div class="container">
     <div class="row mb-2">
         <div class="col">
             <h1>Edit <?= $singleBook['book_name'];?></h1>
         </div>
     </div>

     <!-- errors div, render errors array -->
     <?php if($_POST && !empty($errors)): ?>
         <div class="row mb-2">
             <div class="col">
                 <div class="alert alert-danger" role="alert">
                     <ul>
                         <?php foreach($errors as $singleError): ?>
                             <li><?= $singleError; ?></li>
                         <?php endforeach; ?>
                     </ul>
                 </div>
             </div>
         </div>
     <?php endif; ?>

     <div class="row mb-2">
         <div class="col">
             <form action="./books/update.php?id=<?= $singleBook['id']; ?>" method="post" enctype="multipart/form-data">
                 <div class="form-group">
                   <label for="title">Book Title</label>
                   <input type="text" class="form-control" name="title"  placeholder="Enter book title" value="<?php if($_POST){ echo $_POST['title']; } else { echo $singleBook['book_name'];} ?>">
                 </div>

                 <div class="form-group">
                   <label for="author">Author</label>
                   <input type="text" class="form-control" name="author"  placeholder="Enter books author" value="<?php if($_POST){ echo $_POST['author']; } else { echo $singleBook['author'];} ?>">
                 </div>

                 <div class="form-group">
                   <label for="author">Book Description</label>
                   <textarea class="form-control" name="description" rows="8" cols="80" placeholder="Description about the book"><?php if($_POST){ echo $_POST['description']; } else { echo $singleBook['description'];} ?></textarea>
                 </div>

                 <div class="form-group">
                     <img src="./images/uploads/thumbnails/<?= $singleBook['image_name']; ?>" alt="Card image cap">
                     <label for="file">Upload an Image</label>
                     <input type="file" name="image" class="form-control-file">
                 </div>

                 <button type="submit" class="btn btn-outline-info btn-block">Submit</button>
             </form>
         </div>
     </div>

 </div>


<?php require '../templates/footer.php' ?>
