<?php

// ****** Processa GET (Obter registro(s)) ******

// Inicializa variável com os dados JSON
$result = array();

// Obtém dados do cliente
$parts = explode('/', $_SERVER['REQUEST_URI']);

// Remove que não interessa na URI
array_shift($parts);
array_shift($parts);

// Obtém nome do campo de ordenação ou o ID do registro
if($parts[0] != '') {
  // Remove espaços extras e cpmverte o nome do campo para letras minúsculas
  $field = trim(strtolower($parts[0]));

  // Se for um ID, obtemos ele
  $id = intval($field);

  //Se não passou dados, usa valores "default"
} else {
  $field = 'date';
  $id = 0;
}


// Obtém a direção da ordenação
if(isset($parts[1])) {
  $direction =  trim(strtolower($parts[1]));
} else {
  $direction = 'ASC';
}

// Se enviou o ID, pesquisa por ele
if($id > 0) {
  // Obtém um registro pelo id
  $sql = "SELECT * FROM todo_list WHERE id = {$id} AND status = 'ativo";
  $res = $conn->query($sql);
  
  // Se encontrou
  if($res->num_rows == 1) {

    //Variável com os dados de registro
    $result = array();
    
    //Lista de dados como JSON
    extract($res->fetch_assoc());
    $result[] = array(
      "id" => $id,
      "date" => $date,
      "description" => $description,
      "priority" => $priority
    );
    // Formata JSON de saída
    $json = array('status' => "1", "data" => $result);

    // Se não encontrar o registro solicitado
  } else {
    $json = array('status' => "0", "error" => "Tarefa não encontrada");
  }
  // Se não enviou um ID, pesquisa todos os registros
} else {

  // Lista de nomes de campos válidos para ordenação
  $fieldArray = array('id', 'date', 'description', 'priority');

  // Se não informou um nome de campo invalido
  if(!in_array($field, $fieldArray)) {

    // Usa direção "default"
    $direction = 'date';
  }

  // Lista direções válidas
  $directionArray = array('ASC', 'DESC');

  // Se não informou uma direção inálida
  if(!in_array($direction, $directionArray)) {
    // Usa direção "default"
    $direction = 'ASC';
  }

  // Obtém todos os registros
  $sql = "SELECT * FROM todo_list WHERE status = 'ativo' ORDER BY {$field} {$direction}";
  $res = $conn->query($sql);

  // Conta resgistros encontrados
  $total = $res->num_rows;

  // Se achou os registros
  if($total > 0) {

    // Obtém cada registro para listar
    while($r = $res->fetch_assoc()) {

      // Lista dados como JSON
      extract($r);
      $result[] = array(
        "id" => $id,
        "date" => $date,
        "description" => $description,
        "priority" => $priority
      );
    }
    // Formata o JSON de saída
    $json = array('status' => "1", 'length' => "{$total}","data" => $result);

    // Se não encontrar registros
  } else {
    $json = array('status' => "0", 'error' => 'Não existem tarefas agendadas!');
  }
}