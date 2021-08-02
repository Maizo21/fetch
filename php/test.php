<?php

$dataTest = '[
    {
        "titulo":"titulo 1",
        "comentario":"comentario 1",
        "descripcion":"descripcion 1"
    },
    {
        "titulo":"titulo 1",
        "comentario":"comentario 1",
        "descripcion":"descripcion 1"
    },
    {
        "titulo":"titulo 2",
        "comentario":"comentario 2",
        "descripcion":"descripcion 2"
    },
    {
        "titulo":"titulo 3",
        "comentario":"comentario 3",
        "descripcion":"descripcion 3"
    },
   {
        "titulo":"titulo 4",
        "comentario":"comentario 4",
        "descripcion":"descripcion 4"
    },
    {
        "titulo":"titulo 5",
        "comentario":"comentario 5",
        "descripcion":"descripcion 5"
    }

]';
$data=[];

$data['name']="Nombre";
$data['data'][0]['cantidad']=10;
$data['data'][0]['item']="iphone";
$data['data'][1]['cantidad']=10;
$data['data'][1]['item']="iphone";
$data['status']="success";

//echo $data['data'][0]['cantidad']."\n";

echo json_encode($data);

