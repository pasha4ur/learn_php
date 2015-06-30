<?php
//https://htmlweb.ru/php/example/translit.php
ini_set('upload_max_filesize', 7);
class ImageGallery{
    private $galleryName='';
    private $dirName ='';
    public $msg='';
    private $imgsGallery = null;
    private $minSize=array('imageName'=>'','width'=>0, 'height'=>100000);
    public $imgsHeight=60;
    private $allowedExt = array('jpg', 'jpeg', 'gif', 'png', 'tiff', 'ico');

    function __construct($dirName='images/', $galleryName='Image Gallery'){
        $this->dirName=$dirName;
        $this->galleryName = $galleryName;
    }
    private function __clone(){}

    private function checkFile($file){
        if(!$file) return;
        return in_array (pathinfo($file,PATHINFO_EXTENSION), $this->allowedExt);
    }

     function uploadImg($field='userImage'){
        if(self::checkFile($_FILES[$field]['name'])) {
            $imgName = str_replace(" ","",$_FILES[$field]['name']);
            if (file_exists($this->dirName . $imgName)) {
                $this->msg = 'File with such name is already uploaded';
            } else {
                $this->msg = (move_uploaded_file($_FILES[$field]['tmp_name'], $this->dirName . $imgName)) ? "File $imgName is uploaded" : "File $imgName is NOT uploaded";
            }
        }
    }

    function deleteImg($imgName){
        if(self::checkFile($imgName)) {
            $delImage = $this->dirName . $imgName;
            if (file_exists($delImage)) {
                $this->msg = (unlink($delImage)) ? "File is deleled" : "Can't delete file. Sorry";
            } else {
                $this->msg = 'There is no such file anymore.';
            }
        }
    }

    function prepareImages(){
        $imgs = scandir($this->dirName);
        if (count($imgs)>2){
            $this->imgsGallery=array();

            foreach ($imgs as $tempImg){
                if ($tempImg!=='.' && $tempImg!=='..' && self::checkFile($tempImg)){
                    array_push($this->imgsGallery, $tempImg);
                    $size=getimagesize($this->dirName . $tempImg);
                    if($size[1] < $this->minSize['height']) {
                        $this->minSize['imageName'] = $tempImg;
                        $this->minSize['width'] = $size[0];
                        $this->minSize['height'] = $size[1];

                    }
                }
            }
            if ($this->minSize['width']!==0) $this->imgsHeight =  floor($this->minSize['height'] * 300 / $this->minSize['width']);
        }
    }

    function showImages(){
        if ($this->imgsGallery){
            echo '<div class="gallery"><p class="title">'. $this->galleryName.'</p>';
            foreach($this->imgsGallery as $tImg){
                $imgUrlName = urlencode($tImg);
                echo "<div class='image_gallery'><a href='".$this->dirName . $tImg."'><img src=". $this->dirName . $tImg."></a><br><a class='action_url' href=".$_SERVER['PHP_SELF']."?action=delete&delimg=".$imgUrlName.">Delete</a></div>";
            }
            echo '</div>';
        }else{
            $this->msg='There are no images in this folder. It\'s time upload your first image!';
        }
    }

}

$gallery = new ImageGallery('images/');

if($_SERVER['REQUEST_METHOD']==='POST'){
    if($_FILES['userImage'] && !$_FILES['userImage']['error']) {
         $gallery->uploadImg('userImage');
    }
}

if($_SERVER['REQUEST_METHOD']==='GET' && $_GET['action']==='delete'){
    $gallery->deleteImg(strip_tags($_GET['delimg']));
}

$gallery->prepareImages();
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Document</title>
    <style>
        *{
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body{
            font: 14px/1.5 normal Arial, Verdana, Helvetica, sans-serif;
        }
        div.form{
            width: 40%;
            min-width: 20rem;
            margin: auto;
            padding: 2rem;
            border: 1px solid #ccc;
            clear: both;
            overflow: hidden;
        }
        div.form input{
            margin: 1rem 0;
        }
        div.gallery{
            margin: 1rem auto;
            max-width: 960px;
            text-align: center;
            clear: both;
            overflow: hidden;
        }
        div.gallery p.title{
            font-size: 2rem;
            padding: 1rem 1rem 2rem;
        }
        div.image_gallery{
            display: inline-block;
            text-align: center;
        }
        .image_gallery a{
            display: inline-block;
            max-height: <?=$gallery->imgsHeight;?>px;
            overflow: hidden;
            border: 1px solid black;
            max-width: 300px;
        }
        a.action_url{
            border: none;
            font-size: .7rem;
        }
        .image_gallery img{
            max-width: 300px;
        }
    </style>

    <script type="text/javascript">

        function checkForm(form) // Submit button clicked
        {

            form.myButton.disabled = true;
            form.myButton.value = "Please wait...";
            return true;
        }

        function resetForm(form) // Reset button clicked
        {
            form.myButton.disabled = false;
            form.myButton.value = "Submit";
        }

    </script>
</head>
<body>
<?php
$gallery->showImages();
echo '<br>';
?>
<div class="form">
    <?=$gallery->msg;?>
    <form action="<?=$_SERVER['PHP_SELF']?>" enctype="multipart/form-data" method="post" onsubmit="return checkForm(this);">
        <input type="hidden" name="MAX_FILE_SIZE" value="7000000" />
        <input type="file" name="userImage" required/><br/>
        <input type="submit" name="myButton"/>
    </form>
</div>
</body>
</html>

