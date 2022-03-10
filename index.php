
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <form action="index.php" method="post">

        Nome:
        <input type="text" name="nome" value="<?php echo !empty($_POST['nome']) ? $_POST['nome'] : ''; ?>"><br>
        Cpf:
        <input type="text" name="cpf" value="<?php echo !empty($_POST['cpf']) ? $_POST['cpf'] : ''; ?>"><br>
        Data inicial:
        <input type="date" name="data_inicial" value="<?php echo !empty($_POST['data_inicial']) ? $_POST['data_inicial'] : ''; ?>"><br>
        Data final:
        <input type="date" name="data_final" value="<?php echo !empty($_POST['data_final']) ? $_POST['data_final'] : ''; ?>"><br>
       
        
        Data inicial um:
        <input type="date" name="data_inicial_um" value="<?php echo !empty($_POST['data_inicial_um']) ? $_POST['data_inicial_um'] : ''; ?>"><br>
        Data final um:
        <input type="date" name="data_final_um" value="<?php echo !empty($_POST['data_final_um']) ? $_POST['data_final_um'] : ''; ?>"><br>
        <input type="submit">
    </form>
    
    <!--
    esse form serve para testar o index
    -->
    
    
</body>
</html>


<?php
echo "<a href='form.php'> Fomulario</a>";

//arrey de exemplo
$post = array(
    //html_name => db_collum
    'nome' => 'pessoa',
    'cpf' => 'identific',
    'nao existo' => 'teste',
    'data_inicial' => 'data',
    'data_final' => 'data',
    'data_inicial_um' => 'data-um',
    'data_final_um' => 'data-um'
);

/**
 * Função verifica se o formato da data é padrao para sql
 * 
 * @version 1.00
 * @package Funcoes
 * @author João Augusto
 * 
 * 
 ** $date = format en/USD
 * @return empty || 1
 * */
function valiDate($date, $format = 'Y-m-d')
{
    $d = DateTime::createFromFormat($format, $date);
    return $d && $d->format($format) == $date;
}

function montaArrayBusca($dados, $metodo)
{
    // construir esse arrey para usar em um metodo para montar
    $queryArray =[];
    /*
        [
            'exemplo' => [
                'html_name' => '', //<input name="cpf">;
                'valor' => ['', ''], //<input value>;
                'coluna' => '', //db_colum
                'operador' => '', // =, LIKE, BETWEEN, IN
                'formato' => '', // 'data, integer, string'
                'simbolo' => '', // %,__,||
                'posisao' => '', // (D - direira, E - esquerda, int - posicao entre a palavra)       //posição do simbolo entre lentras
                'funcao' => '', // executa simbolo e (ou) posicao
            ]
        ];
    */
    foreach ($metodo as $url => $result) {
        foreach ($dados as $key_dado => $val_dado) {
            if (!empty($url) && $url == $key_dado && !empty($result)) {
                // se for data
                if (valiDate($result)) {
                    $queryArray['data'][$val_dado][$key_dado]['html_name'] = $key_dado;
                    $queryArray['data'][$val_dado][$key_dado]['valor'] = array(
                        'coluna' => $val_dado,
                        $url => $result
                    );
                    $queryArray['data'][$val_dado][$key_dado]['operador'] = 'BETWEEN';
                } else {
                    switch (gettype(intval($result) > 0 ? intval($result) : $result)) {
                            // se for numero
                        case "integer":
                            $queryArray['integer'][$key_dado]['html_name'] = $key_dado;
                            $queryArray['integer'][$key_dado]['valor'] = array(
                                'coluna' => $val_dado,
                                $url => $result
                            );
                            $queryArray['integer'][$key_dado]['operador'] = '=';
                            break;
                            // se for letra
                        case "string":
                            $queryArray['string'][$key_dado]['html_name'] = $key_dado;
                            $queryArray['string'][$key_dado]['valor'] = array(
                                'coluna' => $val_dado,
                                $url => $result
                            );
                            $queryArray['string'][$key_dado]['operador'] = 'like';
                            $queryArray['string'][$key_dado]['simbolo'] = '%';
                    }
                }
            }
        }
    }

    // echo "<br>";
    // echo "<pre>";
    // print_r($queryArray);
    // echo "</pre>";
    
    
    return $queryArray;
}


/**
 * Funcção insere um elemento ao meio da palavra
 * 
 * @version 1.00
 * @package Funcoes
 * @author João Augusto
 * 
 * @param string
 * @param int
 * @param string
 * 
 * @return string
 * 
 * insertInPosition('palavra',2,'_');
 * pa_lavra
 * 
 */
function insertInPosition($str, $pos, $c)
{
    return substr($str, 0, $pos) . $c . substr($str, $pos);
}

// esse insertInPosition() server para colocar um simbolo em algum lugar do dado




/**
 * @desc Funcçao gerar sequencia de consulta no sql
 * 
 * *OBS.: Tratar os dados antes de inserir
 * 
 * @version 1.00
 * @package Funcoes
 * @author João Augusto 
 * @param array dados array(
 *                      'coluna'=>'@param string coluna do banco',
 *                      'valor'=>'@param mixed valor do elemento',
 *                      'operador'=>'=',
 *                      'formato'=>'date, int, string'
 *                      );
 * @param array dados array(
 *                      'coluna'=>'@param string coluna do banco',
 *                      'valor'=>'@param mixed valor do elemento',
 *                      'operador'=>'like',
 *                      'simbolo'=>'%,__,||',
 *                      'posicao'=>'4', (D - direira, E - esquerda, int - posicao entre a palavra)       //posição do simbolo entre lentras
 *                      'formato'=>'date, int, string'
 *                      );
 * @param array dados array(
 *                    'coluna'=>'@param string coluna do banco',
 *                    'valor'=>'@param mixed valor do elemento',
 *                    'valor2'=>'valor do elemento',
 *                    'operador'=>'BETWEEN',
 *                    'formato'=>'date, int, string'
 *                 	  );
 * 
 * @return string = "sql WHERE dados";
 * */
function setSlqConsulta($dados)
{
    $clausula = ' ';
    $contador = 0;

    foreach ($dados as $key => $val)
    {
        foreach ($val as $html => $html_val)
        {
            if (!empty($html_val)) {
                if ($contador != 0) {
                    $clausula .= " AND ";
                } else {
                    $clausula = 'WHERE ';
                }
                
                $contador++;
    
                switch ($key) {
                    case "integer":
                        $clausula .= "({$html_val['valor']['coluna']} {$html_val['operador']} {$html_val['valor'][$html]} )";
                        break;
    
                    case "data":
    
                        $valor=[];
                        foreach ($html_val as $data => $mont){

                            $valor[] = $mont['valor'][$data];
                            $coluna = $mont['valor']['coluna'];

                        }
                        $data1 = !empty( $valor[0]) == 1 ? $valor[0] : null;
                        $data2 = !empty($valor[1]) == 1 ? $valor[1] : null;
    
                        $data1 = !empty($data2) && empty($data1) ? date('Y-m-d', strtotime('-20 days', strtotime($data2))) : $data1; // 1 - 0
                        $data2 = !empty($data1) && empty($data2) ? date('Y-m-d', strtotime('+20 days', strtotime($data1))) : $data2; // 
    
                        if ((strtotime($data2) < date("Y-m-d", strtotime($data1 . "+21 days")))) {
                            $data2 = date('Y-m-d', strtotime('+20 days', strtotime($data1)));
                        }
    
                        $clausula .= "($coluna {$mont['operador']} '{$data1}' AND '{$data2}')";
                        break;
    
                    case "string":
                        $clausula .= "({$html_val['valor']['coluna']} {$html_val['operador']} {$html_val['valor'][$html]} )";
                }
    
                novo:
            }
        }
    }
    return $clausula;
}


//essa é a maneira para inserir outro operador
//              esse nome 
// $recebe ['string']['nome']['operador'] = '=';



$recebe = montaArrayBusca($post, $_POST);


// echo "<br>";
// echo "<pre>";
// print_r($recebe);
// echo "</pre>";

$date = setSlqConsulta($recebe);

print_r($date);
?>
