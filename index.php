<?php
// INCLUDE ON EVERY TOP-LEVEL PAGE!
include("includes/init.php");

function print_tag($to_print){
  ?>
  <span> #<?php echo htmlspecialchars($to_print["tag"]);?> </span>

<?php }

function add_option($option){ ?>
  <option value= <?php echo htmlspecialchars($option); ?> ><?php echo htmlspecialchars($option); ?></option>
<?php }

// show information pertaining to individual image
if (isset($_GET['id'])) {
  $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
  $id = filter_var($id, FILTER_SANITIZE_SPECIAL_CHARS);
  $sql = "SELECT * FROM images WHERE images.id = :id;";
  $params = array(
    ':id' =>
    $id
  );
  $result = exec_sql_query($db, $sql, $params);
  if ($result) {
    $show_images = $result->fetchAll();
    $show_image = $show_images[0];
  };
}


?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link rel="stylesheet" type="text/css" href="styles/all.css" media="all" />

  <title>Autoshop</title>
</head>

<body>
  <!-- head -->
  <?php include("includes/header.php"); ?>

  <main id = "content">
    <!-- sidebar which includes search, upload, and tag-->
    <?php

    //upload_form

    if (isset($_POST['upload_submit']) && is_user_logged_in()) {

      $upload_info = $_FILES["upload"];

      if($_FILES['upload']['error'] == 0){
        $file_name = $upload_info["name"];
        $upload_ext = strtolower( pathinfo($file_name, PATHINFO_EXTENSION) );
        $file_name = basename($file_name); // cleanses

        exec_sql_query(
          $db,
          "INSERT INTO images (file_ext, file_name, user_id) VALUES (:upload_ext, :file_name, :current_user_id);",
          array(':upload_ext' => $upload_ext,
          ':file_name' => $file_name,
          'current_user_id' => $current_user["id"]
        )
        )->fetchAll();

        $last_insert = $db->lastInsertId("id");

        $new_path = "uploads/images/" . $last_insert . "." . $upload_ext;
        move_uploaded_file( $_FILES["upload"]["tmp_name"], $new_path );
      }

    }


    //tag_form

    if (isset($_POST["tag_upload_submit"])) {

      $image_id = $_POST["image_id"];

      $tag = filter_input(INPUT_POST, 'tag', FILTER_SANITIZE_STRING);
      $tag = filter_var($tag, FILTER_SANITIZE_SPECIAL_CHARS);
      $tag = trim($tag);
      //prevent spaces in tag error, spaces cause tag to split
      $tag = str_replace(' ', '-', $tag);

      $image_id = filter_var($image_id, FILTER_VALIDATE_INT);
      $image_id = filter_var($image_id, FILTER_SANITIZE_SPECIAL_CHARS);
      $image_id = trim($image_id);


      if($tag != '' && $image_id != '' && is_numeric($image_id)){

        //check to make sure tag does not already exist
        //already constrained imaged id in form, so we do not need to check if it is a valid image
        $image_tag_records = exec_sql_query(
          $db,
          "SELECT * FROM image_tags LEFT OUTER JOIN tags ON image_tags.tag_id = tags.id WHERE LOWER(tags.tag) LIKE LOWER(:tag) AND image_tags.image_id LIKE :image_id;",
          array(':tag' => $tag,
          ':image_id' => $image_id
        )
        )->fetchAll();


        //check if tag exists
        $current_tag_records = exec_sql_query(
          $db,
          "SELECT * FROM tags WHERE LOWER(tags.tag) LIKE LOWER(:tag);",
          array(':tag' => $tag)
          )->fetchAll();

          $add_tag = $current_tag_records[0]['id'];

          if(sizeof($current_tag_records) == 0) {
            //if tag doesnt exist, add the tag and then reaasign tag id
            //insert into tags table
            exec_sql_query(
              $db,
              "INSERT INTO tags (tag) VALUES (:tag);",
              array(':tag' => $tag
            )
            )->fetchAll();


            $add_tag = $db->lastInsertId("id");
          }

          //insert into image_tags table
          if(sizeof($image_tag_records) == 0){
            exec_sql_query(
              $db,
              "INSERT INTO image_tags (image_id, tag_id) VALUES (:image_id, :tag_id);",
              array( ':image_id' => $image_id,
              ':tag_id' => $add_tag
            )
            )->fetchAll();

          }

        }

      }


      //delete_form image

      if (isset($_POST['delete_img_submit']) && is_user_logged_in()) {

        $delete_img = trim( $_POST['delete_img'] );

        $delete_img = filter_var($delete_img, FILTER_VALIDATE_INT);
        $delete_img = filter_var($delete_img, FILTER_SANITIZE_SPECIAL_CHARS);

        if($delete_img != '' && is_numeric($delete_img) ){

          $image_delete_table = exec_sql_query(
            $db,
            "SELECT distinct image_tags.tag_id FROM image_tags LEFT OUTER JOIN images ON image_tags.image_id = images.id WHERE image_tags.image_id LIKE :delete_img AND images.user_id LIKE :current_user_id;",
            array(':delete_img' => $delete_img,
            ':current_user_id' => $current_user["id"]
          )
          )->fetchAll();


          if($image_delete_table[0] != NULL){
            //delete all instances of image in image_tags table
            exec_sql_query(
              $db,
              "DELETE FROM image_tags WHERE image_tags.image_id LIKE :to_delete_image;",
              array(':to_delete_image' => $delete_img
            )
            )->fetchAll();

            //delete all instances of tag not in in image_tags table
            $delete_tags = exec_sql_query(
              $db,
              "SELECT tags.id FROM tags LEFT OUTER JOIN image_tags ON image_tags.tag_id = tags.id WHERE image_tags.tag_id IS NULL;"
              )->fetchAll();

              if($delete_tags){
                foreach($delete_tags as $dt) {
                  exec_sql_query($db,"DELETE FROM tags WHERE tags.id LIKE :dt;",
                  array(":dt" => $dt["id"]))->fetchAll();
                }
              }
            }
            //get image extension
            $extension = exec_sql_query(
              $db,
              "SELECT images.file_ext FROM images WHERE images.id LIKE :to_delete_image AND images.user_id LIKE :current_user_id;",
              array(':to_delete_image' => $delete_img,
              ':current_user_id' => $current_user["id"]
            )
            )->fetchAll();

            //delete image
            exec_sql_query(
              $db,
              "DELETE FROM images WHERE images.id LIKE :to_delete_image AND images.user_id LIKE :current_user_id;",
              array(':to_delete_image' => $delete_img,
              ':current_user_id' => $current_user["id"]
            )
            )->fetchAll();

            //delete image from uploads folder

            $fdelete = "uploads/images/". $delete_img . '.' . $extension[0][0];
            fclose($fdelete);
            unlink($fdelete);


          }
        }

        //delete form tag

        if (isset($_POST['delete_tag_submit']) && is_user_logged_in()) {

          $delete_img_tag = $_POST['delete_img_tag'];

          $delete_img_tag = filter_var($delete_img_tag, FILTER_VALIDATE_INT);
          $delete_img_tag = filter_var($delete_img_tag, FILTER_SANITIZE_SPECIAL_CHARS);

          $delete_tag = filter_input(INPUT_POST, 'delete_tag', FILTER_SANITIZE_STRING);
          $delete_tag = filter_var($delete_tag, FILTER_SANITIZE_SPECIAL_CHARS);
          $delete_tag = trim($delete_tag);


          if($delete_img_tag != '' && is_numeric($delete_img_tag) && $delete_tag != '' ){

            $delete_tag_id = exec_sql_query($db, "SELECT tags.id FROM tags WHERE tags.tag LIKE :delete_tag;", array(":delete_tag" => $delete_tag))->fetchAll(PDO::FETCH_ASSOC);

            exec_sql_query($db, "DELETE FROM image_tags WHERE image_tags.tag_id LIKE :delete_tag_id AND image_tags.image_id LIKE :delete_img_tag;", array(":delete_tag_id" => $delete_tag_id[0]['id'],
            ":delete_img_tag" => $delete_img_tag))->fetchAll(PDO::FETCH_ASSOC);

            //clean up tags table
            $to_clean_tags =  exec_sql_query($db, "SELECT tags.id FROM tags LEFT OUTER JOIN image_tags ON tags.id = image_tags.tag_id WHERE image_tags.tag_id IS NULL AND tags.tag LIKE :delete_tag;", array(":delete_tag" => $delete_tag))->fetchAll(PDO::FETCH_ASSOC);

            exec_sql_query($db, "DELETE FROM tags WHERE tags.id LIKE :to_clean_tag;", array(":to_clean_tag" => $to_clean_tags[0]['id']))->fetchAll(PDO::FETCH_ASSOC);

          }
        }

        ?>

        <div id = "sidebar">
          <!-- search -->
          <form id = "search_form" action="index.php" method="get" novalidate>
            <div>
              <label>Search by Tags:</label>
              <input type="text" name="search" required/>
              <button type="submit" name="search_submit"> -> </button>
            </div>
          </form>


          <!-- upload-->
          <form id = "upload_form" action="index.php" method="post" enctype="multipart/form-data">
            <h2>Upload</h2>
            <?php if ( is_user_logged_in()) { ?>

              <input type="hidden" name="MAX_FILE_SIZE" value="1000000" />
              <label>Image:</label>
              <input id = "upload" type="file" name="upload"/>
              <button class = "upload" type="submit" name="upload_submit"> -> </button>

            <?php }
            else { ?>
              <div class = "not_loggedin_message">
                <label> User must be logged in to upload images. </label>
              </div>
            <?php }
            ?>
          </form>

          <!-- tag -->
          <form id = "tag_form" action="index.php" method="post" novalidate>
            <h2>Tag</h2>
            <div>
              <label>Image ID:</label>
              <select name = "image_id">
                <?php
                $img_tag_list = exec_sql_query($db, "SELECT distinct images.id FROM images;", array())->fetchAll(PDO::FETCH_ASSOC);

                foreach($img_tag_list as $img_tag){
                  add_option($img_tag["id"]);
                }
                ?>
              </select>
            </div>
            <div>
              <label>Tag:</label>
              <input id ="tag" type="text" name="tag" />
              <button class = "upload" type="submit" name="tag_upload_submit"> -> </button>
            </div>
          </form>


          <div id = "delete_container">
            <?php if ( is_user_logged_in()) {
              //shows only images the user uploaded
              $img_list = exec_sql_query($db, "SELECT distinct images.id FROM images WHERE images.user_id LIKE :current_user_id;", array(":current_user_id" => $current_user["id"]))->fetchAll(PDO::FETCH_ASSOC);
              ?>
              <form id = "delete_form_img" action="index.php" method="post" novalidate>
                <label>Delete Image:</label>
                <select name = "delete_img">
                  <?php
                  foreach($img_list as $img){
                    add_option($img["id"]);
                  }
                  ?>
                </select>
                <button class = "delete" type="submit" name="delete_img_submit"> X </button>
              </form>

              <form id = "delete_form_tag" action="index.php" method="post" novalidate>
                <?php
                //only show images which the user has a tag they made on, and only show tags the user created
                $image_with_user_tags = exec_sql_query($db, "SELECT distinct image_tags.image_id FROM image_tags LEFT OUTER JOIN images ON image_tags.image_id = images.id WHERE images.user_id LIKE :current_user_id;", array(":current_user_id" => $current_user["id"]))->fetchAll(PDO::FETCH_ASSOC);

                ?>
                <label>Remove Tag:</label>
                <div>
                  <select name = "delete_img_tag">
                    <!-- default -->
                    <option value= NULL selected>Image ID</option>
                    <?php
                    foreach($image_with_user_tags as $iwut){
                      add_option($iwut["image_id"]);
                    }
                    ?>
                  </select>
                  <select name = "delete_tag">
                    <!-- default -->
                    <option value= NULL selected>Tag Name</option>
                    <?php
                    $tag_delete_list = exec_sql_query($db, "SELECT distinct tags.tag FROM tags LEFT OUTER JOIN image_tags ON tags.id = image_tags.tag_id LEFT OUTER JOIN images ON image_tags.image_id = images.id WHERE images.user_id like :current_user_id;", array(":current_user_id" => $current_user["id"]))->fetchAll(PDO::FETCH_ASSOC);

                    foreach($tag_delete_list as $tag_delete){
                      add_option($tag_delete["tag"]);
                    };
                    ?>
                  </select>
                  <button class = "delete" type="submit" name="delete_tag_submit"> X </button>
                </div>
              </form>
            <?php }
            else { ?>
              <label id = "delete_else">Delete :</label>
              <div class = "not_loggedin_message">
                <label>
                  User must be logged in to delete images or tags.
                </label>
              </div>
            <?php } ?>
          </div>

        </div>


        <div id = "image_container">

          <?php if (isset($show_image)) { ?>
            <div>
              <a id = "back_button" href = "index.php"> Back </a>
            </div>
            <div id = "single_image">
              <h2><?php echo htmlspecialchars($show_image['file_name']) ?></h2>

              <figure class="large_image">
                <img src="uploads/images/<?php echo $show_image['id'] . '.' . $show_image['file_ext']; ?>" alt="<?php echo htmlspecialchars($show_image['file_name']); ?>" />
              </figure>

              <blockquote>
                <p>
                  Image ID: <?php echo htmlspecialchars($show_image['id']); ?>
                </p>

                <?php $show_image_tags = exec_sql_query($db, "SELECT distinct tags.tag FROM image_tags LEFT OUTER JOIN tags ON image_tags.tag_id = tags.id WHERE image_tags.image_id LIKE :show_image_id;", array(':show_image_id' => $show_image["id"]))->fetchAll(PDO::FETCH_ASSOC); ?>

                <p>Image Tags:  <?php foreach($show_image_tags as $is_tag) {print_tag($is_tag);}; if(!$is_tag) {echo "No Tags";};?></p>
              </blockquote>
            </div>

          <?php } else { ?>
            <ul>
              <!-- only display images like search -->
              <?php
              $valid_search = FALSE;
              if (isset($_GET['search_submit'])) {
                $valid_search = TRUE;
                $search = trim( $_GET['search'] );

                if($search == ''){
                  $valid_search = FALSE;
                }

                $search = $_GET["search"];
                $search = filter_var($search, FILTER_SANITIZE_STRING);
                $search = filter_var($search, FILTER_SANITIZE_SPECIAL_CHARS);

              }

              if(isset($valid_search) && $valid_search){

                $files = exec_sql_query($db, "SELECT distinct images.id, images.file_ext, images.file_name FROM images LEFT OUTER JOIN image_tags ON image_tags.image_id = images.id LEFT OUTER JOIN tags ON tags.id = image_tags.tag_id WHERE tags.tag LIKE '%' || :search  || '%';", array(':search' => $search))->fetchAll(PDO::FETCH_ASSOC);
              }

              if(!$valid_search && !isset($files)){
                $files = exec_sql_query($db, "SELECT * FROM images;", array())->fetchAll(PDO::FETCH_ASSOC);
              }


              foreach($files as $file){
                $file_tags = exec_sql_query($db, "SELECT distinct tags.tag FROM image_tags LEFT OUTER JOIN tags ON image_tags.tag_id = tags.id WHERE image_tags.image_id LIKE :fileid;", array(':fileid' => $file["id"]))->fetchAll(PDO::FETCH_ASSOC);

                // SOURCE: (ORIGINAL WORK)) ALL IMAGES CREATED BY KIMBERLY BAUM
                echo "<li> <a href = 'index.php?". http_build_query(array('id' => $file["id"])) . "'>
                <img src=\"uploads/images/" . $file["id"] . "." . $file["file_ext"] . "\"" . "alt = " . htmlspecialchars($file["file_name"]) . "/>" . "</a>". PHP_EOL;


                echo "<p> Image ID:" . $file["id"] . "</p><p>" . "Tags:";

                //show tags
                if(sizeof($file_tags) > 0){
                  foreach($file_tags as $ft){
                    echo " ". htmlspecialchars($ft["tag"]) ." ";
                  };

                  echo "</p>" . "</a></li>";
                }
                else{
                  echo "<span id= 'no_tags'> No tags </span>";
                }
              }

              // SOURCE: (ORIGINAL WORK)) ALL IMAGES CREATED BY KIMBERLY BAUM
              ?>
            </ul>

            <div id = "tags">
              <!-- will dispaly all tags at the bottom -->
              <p>All Tags</p>
              <?php
              $tag_list = exec_sql_query($db, "SELECT distinct * FROM tags;", array())->fetchAll(PDO::FETCH_ASSOC);
              foreach($tag_list as $tag_item){
                print_tag($tag_item);
              };
              ?>
            </div>
          <?php } ?>
        </div>
      </main>

    </body>
    </html>
