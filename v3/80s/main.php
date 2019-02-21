<?php
    if($_GET[movieid]){
        $id = $_GET[movieid];
        if (is_numeric($id)){
            mysql_connect("localhost", "root","root");
            mysql_select_db("80s");
            mysql_set_charset("utf8");
            $sql = "select * from movies where id = $id";

            $res = mysql_query($sql);
            $row = mysql_fetch_assoc($res);

            if($row){
                echo "<div style='border:1px dotted red;font-size:30px;text-align:center;width:100%;background:#c3c3c3' >";
                echo "名称: ". $row[name] ."<br>";
                echo "<a href='".$row[refer]."'>来源:".$row[refer]."</a><br>";
                
                $resource  = explode("|||", $row[torrent]);
                for($i=0; $i<count($resource)-1; $i++)
                    echo  "<a href='".$resource[$i]."'>资源链接</a><br>";

                echo "<img style='width:300px;height:400px' src='$row[imgurl]'>";
                echo "</div>";
            }else{
                echo "查无资源<br>";
            }
        }else{
            echo "参数不合法!!<br>";
        }
    }
?>



