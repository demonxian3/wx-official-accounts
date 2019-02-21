<?php 

    function Upload($dir){
        return move_uploaded_file($_FILES[file][tmp_name], $dir."/".$_FILES[file][name]);
    }

    function ok(){
        echo "<script>alert('上传成功')</script>";
    }

    function no(){
        echo "<script>alert('上传成功')</script>";
    }

    function pass(){
        echo "<script>请输入密码并上传mp3音乐文件</script>";
    }

    if($_POST[pass] == "sziittiizs"){
        $_FILES[file][name] = str_ireplace("php", "attack", $_FILES[file][name]);

        if(preg_match('/(jpg$)|(png$)/i', $_FILES[file][name])){
            if(Upload("image")) ok();
            else no();
        }

    
        if(preg_match("/(mp3)|(mp4)|(avi)/i", $_FILES[file][name])){
            if(Upload("music")) ok();
            else no();
        }


    }else
        pass();
?>
<form action="" method="post" enctype="multipart/form-data">
    请输入上传密码: <input name="pass"><br>
    请选择音乐文件：<input name="file" type="file">
    <input type="submit" value="上传"> 
</form>
