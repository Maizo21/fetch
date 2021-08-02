<?php
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');

header("Content-type: application/json; charset=utf-8");


$post = json_decode(file_get_contents("php://input"), true); //reemplaza al $_POST[]
/*
if(isset($post)){
    $data['post'] = $post;
    $data['type'] = "JSON";
    echo json_encode($data);
    exit();
}

if(isset($_POST['task'])){
    $data['post'] = $_POST['task'];
    $data['type'] = "Form";
    echo json_encode($data);
    exit();
}

exit();*/

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


if(!isset($post)){
    $data['error'] = "no se puedo procesar su peticion";
    echo json_encode($data);
    exit();
}


$data = [];

if($post['task'] == "read"){
    read($conn);
}
/* else if($post['task'] == "read_report"){
    readReport($conn,$post);
} */
else if($post['task'] == "insert"){
    insert($conn,$post);
}
else if($post['task'] == "update"){
    update($conn,$post);
}
/* else if($post['task'] == "delete_item"){
    delete_item($conn,$post);
} */
else if($post['task'] == "delete"){
    delete($conn,$post);
}
else{
    $data['error'] = "no se puedo procesar su peticion";
    echo json_encode($data);
    exit();
}


$data = [];

//********************************  READ  **************************************/

function read($conn){
    $query =$conn->prepare ("SELECT *  FROM reports_tb WHERE [enable] = 1 order by id asc");
    if ($query->execute()){
        $i = 0;   
        while ($row = $query->fetch(PDO::FETCH_ASSOC)){
                $data[$i]['id'] = $row['id']; 
                $data[$i]['serial'] = $row['serial']; 
                $data[$i]['created'] = $row['created'];
                $data[$i]['updated'] = $row['updated'];
                $data[$i]['jsonURL'] = $row['jsonURL'];
                $data[$i]['userID'] = $row['userID'];
                $i++; 
        }
    }
    // echo var_dump($data[0]['serial']);
    echo json_encode($data);
}


//*******************************  INSERT  *********************************************/
function insert($conn,$post){
    $maxSerial = (int)maxSerial($conn);     //máxima serie. convierto en número para sumar
    $maxSerial = $maxSerial+1;              //sumo
    $numlength = strlen((string)$maxSerial);      //convierto en string para contar posiciones
    $serie = "error";
    if($numlength == 1){
        $serie = "000".(string)$maxSerial;
    }
    else if($numlength == 2){
        $serie = "00".(string)$maxSerial;
    }
    else if($numlength == 3){
        $serie = "0".(string)$maxSerial;
    }
    else{
        $serie = (string)$maxSerial;
    }

    $now = time();

    $user = $post['user'];
    $file = "report_".$serie."-".$post['user'].".json";
    // echo "$file\n";

    $query =$conn->prepare ("INSERT INTO reports_tb 
    ([serial],[created],[updated],[jsonURL],[userID],[enable])
    VALUES 
    ('$serie','$now','$now','$file','$user','1')");
        if ($query->execute()){                
            $url = "../../json_report/$file";
            if (!file_exists($url)) {
                //se crea un json nuevo
                $fp = fopen($url, 'w');
                $data['status'] = "success";
                $data['task'] = $post['task'];
                $data['serial'] = $serie."-".$post['user'];
                $data['user_comment'] = $post['user_comment'];
                $data['comment'] = "id and new json created";
                echo json_encode($data);
                exit();
            }
        }
}


//************************************************************************************************/
//ACTUALIZA DATOS DE ITEMS CONTENEDORES
function update($conn,$post){

    //check if chart exist if not just insert one
    $url = "../../json_report/report_".$post['serial'].".json";
    if (!file_exists($url)) {
        //se crea un json nuevo
        $fp = fopen($url, 'w');
        $data[0]['title'] = $post['title'];
        $data[0]['inner_id'] = $post['inner_id'];
        $data[0]['type'] = $post['type'];

        $data[0]['user_comment'] = $post['user_comment'];

        fwrite($fp, json_encode($data));
        fclose($fp);

        $data['status'] = "success";
        $data['task'] = $post['task'];
        $data['serial'] = $post['serial'];
        $data['user_comment'] = $post['user_comment'];
        $data['comment'] = "new added on a new file";
        echo json_encode($data);
        exit();
    }
    
    // --- IF EXIST -----

    $strJsonFileContents = file_get_contents($url);
    $json = json_decode($strJsonFileContents, true);//Convierte un string codificado en JSON a una variable de PHP

    $isEmpty = true;

    if($strJsonFileContents != ""){
        foreach($json as $key => $data) {   
            #key => parent key name or value
            #data => array item
            if($data['inner_id'] == $post['inner_id']){
                $isEmpty = false;
                $json[$key]['title'] = $post['title'];

                $json[$key]['user_comment'] = $post['user_comment'];

                $newJsonString = json_encode($json);
                file_put_contents($url, $newJsonString);
                
                $data['status'] = "success";
                $data['task'] = $post['task'];
                $data['serial'] = $post['serial'];
                $data['inner_id'] = $post['inner_id'];

                $data['comment'] = "updated";
                echo json_encode($data);
                exit();
            }            
        }
    }

    if($isEmpty){
        $index = 0;
        if($strJsonFileContents != ""){
            $index = count($json);
        }
        $json[$index]['title'] = $post['title'];
        $json[$index]['type'] = $post['type'];
        $json[$index]['inner_id'] = $post['inner_id'];
        $json[$index]['user_comment'] = $post['user_comment'];

        $newJsonString = json_encode($json);
        file_put_contents($url, $newJsonString);
        
        $data['status'] = "success";
        $data['task'] = $post['task'];
        $data['serial'] = $post['serial'];
        $data['inner_id'] = $post['inner_id'];
        $data['comment'] = "updated as new id";
        echo json_encode($data);
        exit();
    } 
}

//************************************************************************************************/
// function existSerial($conn, $serial){
//     $query =$conn->prepare ("SELECT *  FROM win_reports_tb WHERE [enable] = 1 and [serial] = $serial")
//     $data['serial'] = "";
//     if ($query->execute()){
//         while ($row = $query->fetch(PDO::FETCH_ASSOC)){
//                 $data['serial'] = $row['serial']; 
//         }
//     }
//     if($data['serial'] == ""){
//         return false;
//     }
//     else{
//         return true;
//     }
// }
//************************************************************************************************/



// function delete($conn,$post){
//     $query =$conn->prepare ("delete from win_reports_tb where [serial] =".$post['serial']);
    
//     $data['task'] = $post['task'];
//     $data['serial'] = $post['serial'];

//     if ($query->execute()){ 
//         $data['status'] = "success";
//         $data['comment'] = "deleted";
//     }
//     else{
//         $data['status'] = "error";;
//         $data['comment'] = ""
//     }
//     echo json_encode($data);
// }




//************************************************************************************************/


function readReport($conn,$post){
    
    // $url = "";

    // $root =  $_SERVER['DOCUMENT_ROOT'];//si estamo desde el PC le pregunta al servidor remoto

    // if($root == "Z:/DOCUMENTOS/xampp/htdocs"){
    //     $url = "http://webmel2.cl/win/json_report/report_".$post['serial'].".json";
    // }
    // else{
    //     $url = "../../json_report/report_".$post['serial'].".json";
    // }

    $url = "../../json_report/report_".$post['serial'].".json";

    if (!file_exists($url)) {
        //se crea un json nuevo
        $fp = fopen($url, 'w');
        $data['status'] = "succes";
        $data['task'] = $post['task'];
        $data['serial'] = $post['serial'];
        $data['comment'] = "empty file created";
        echo json_encode($data);
        exit();
    }


    $strJsonFileContents = file_get_contents($url);
    $json = json_decode($strJsonFileContents, true);//string to json 

    if($json != ""){
        $data['status'] = "succes";
        $data['task'] = $post['task'];
        $data['serial'] = $post['serial'];
        $data['comment'] = "";
        $data['data'] = $json;
        echo json_encode($data);
        exit();

    }
    else{
        $data['status'] = "succes";
        $data['task'] = $post['task'];
        $data['serial'] = $post['serial'];
        $data['comment'] = "no hay datos";
        $data['data'] = "";
        echo json_encode($data);
        exit();

    }


}

//************************************************************************************************/


function delete_item($conn,$post){


    //check if chart exist if not just insert one
    $url = "../../json_report/report_".$post['serial'].".json";
    if (!file_exists($url)) {
        $data['status'] = "error";
        $data['task'] = $post['task'];
        $data['serial'] = $post['serial'];
        $data['inner_id'] = $post['inner_id'];
        $data['comment'] = "file not exist";
        echo json_encode($data);
        exit();
    }
    
    // --- IF EXIST -----
    $strJsonFileContents = file_get_contents($url);
    $json = json_decode($strJsonFileContents, true);//string to object
    
    $lenght = 0;      

    //si existe el archivo y no está vacío (borrado por casualidad el contenido)
    if($strJsonFileContents != ""){
        $lenght = count($json);
    }
      
    $inner_serie_position = "";
    //get data index in json file if exist
    
    if($lenght > 0){
        //----  ESTA FUNCION PERMITE BORRAR UN ITEM DE JSON SIN ASIGNAR Y KEY INDESEADO
        //----  UNSET INSERTABA 0:{ },1:{}....
    
        function array_delete($array, $filterfor){
            $thisarray = array ();
            foreach($array as $value){
            if(stristr($value['inner_id'], $filterfor)===false && strlen($value['inner_id'])>0){
                $thisarray[] = $value;
            }    
            }
            return $thisarray;
        }

        $json2 =array_delete($json, $post['inner_id']);

        $newJsonString = json_encode($json2);
        file_put_contents($url, $newJsonString);

        $data['status'] = "success";
        $data['task'] = $post['task'];
        $data['serial'] = $post['serial'];
        $data['inner_id'] = $post['inner_id'];
        $data['comment'] = "item deleted";
        echo json_encode($data);
        exit();
    }
    else{
        $data['status'] = "error";
        $data['task'] = $post['task'];
        $data['serial'] = $post['serial'];
        $data['inner_id'] = $post['inner_id'];
        $data['comment'] = "empty data";
        echo json_encode($data);
        exit();
    }
}



//************************************************************************************************/


function maxSerial($conn){
    $query =$conn->prepare ("SELECT [serial] FROM [dbo].[reports_tb] JOIN (SELECT MAX(id) AS id FROM [dbo].[reports_tb])max ON [reports_tb].[id] = max.id");
    $result = "error";
    if ($query->execute()){ 
        while ($row = $query->fetch(PDO::FETCH_ASSOC)){
            $result = $row['serial'];//."  ".$row['title'];
        }
    }
    return $result;
}


// function set($conn){
//     $query =$conn->prepare ("ALTER TABLE [dbo].[win_reports_tb]  ADD [win_reports_tb].[id] INT IDENTITY  CONSTRAINT PK_win_reports_tb PRIMARY KEY CLUSTERED");
  
//     $query->execute();
// }

echo $dataTest;