# makeSQL
criar where do sql

# monte um array assim
# o a chara é o nome do input do html (html_name), o valor é o nome da coluna do banco de dados
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

# copia tudo para o codigo
a funcao montaArrayBusca(), monta um array com os quesitos para setSlqConsulta()

# montar o sql
pode colocar acrescentar poderes de busca do retorno de montaArrayBusca():

retorno do montaArrayBusca() [tipo do dado][html_name]['operador'] = 'simbolo da operação';
$retorno ['string']['nome']['operador'] = '=';


# com o setSlqConsulta()
pode receber o array de afordo aom as espeificaç
