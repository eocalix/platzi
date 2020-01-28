<?php
  header('Content-Type: application/json');  
  // Autenticacion via HTTP.
  // Ejem: http://admin:12345@localhost:8080/platzi/api-rest/server.php?resource_type=books&resource_id=1
  /* $user = array_key_exists('PHP_AUTH_USER', $_SERVER) ? $_SERVER['PHP_AUTH_USER'] : '';
  $pwd = array_key_exists('PHP_AUTH_PW', $_SERVER) ? $_SERVER['PHP_AUTH_PW'] : '';

  if ($user !== 'admin' || $pwd !== '12345') 
  {
    die;
  } */
  
  // Autenticacion via HMAC
  /* if 
  (
    !array_key_exists('HTTP_X_HASH', $_SERVER) || 
    !array_key_exists('HTTP_X_UID', $_SERVER) || 
    !array_key_exists('HTTP_X_TIMESTAMP', $_SERVER)
  )
  {
    die;
  }

  list($hash, $uid, $timestamp) = [
      $_SERVER['HTTP_X_HASH'], 
      $_SERVER['HTTP_X_UID'], 
      $_SERVER['HTTP_X_TIMESTAMP'], 
  ]; 

  $secret =  'Clave secreta! No la digas a nadie';

  $newHash = sha1($uid . $timestamp . $secret);

  if ($newHash !== $hash) 
  {
    die;
  }
  */

  // Autenticacion via Access Tokens
  if (!array_key_exists('HTTP_X_TOKEN', $_SERVER)) 
  {
    die;
  }

  $url = 'http://localhost:8080/platzi/api-rest/auth_server.php';
  $ch = curl_init($url);
  curl_setopt(
    $ch, 
    CURLOPT_HTTPHEADER,
    [
      "X-Token: {$_SERVER['HTTP_X_TOKEN']}"
    ]
  );
  
  curl_setopt(
    $ch,
    CURLOPT_RETURNTRANSFER,
    true
  );

  $ret = curl_exec($ch);

  if (curl_errno($ch) != 0) 
  {
    die(curl_error($ch));
  }

  if ($ret !== 'true') 
  {
    http_response_code(403);
    die;
  }

  // Definimos los recursos disponibles.
  $allowedResourceTypes = [
    'books',
    'authors',
    'genres',
  ];

  // Validamos que el recurso esté disponible.
  $resourceType = $_GET['resource_type'];

  if (!in_array($resourceType, $allowedResourceTypes)) 
  {
    http_response_code(400);
    echo json_encode([
      'error' => $resourceType . ' is unknown'
    ]);
    die;
  }

  // Defino los recursos.
  $books = [
    1 => [
      'titulo' => 'Lo que el viento se llevó',
      'id_autor' => 2,
      'id_genero' => 2,
    ],
    2 => [
      'titulo' => 'La Iliada',
      'id_autor' => 1,
      'id_genero' => 1,
    ],
    3 => [
      'titulo' => 'La Odisea',
      'id_autor' => 1,
      'id_genero' => 1,
    ],
  ];
  

  // Levantamos el id del recurso buscado.
  $resourceId = array_key_exists('resource_id', $_GET) ? $_GET['resource_id'] : '';
  // Generamos la respuesta asumiendo que el pedido es correcto.
  switch ( strtoupper($_SERVER['REQUEST_METHOD'])) {
    case 'GET':
      if (empty($resourceId)) 
      {
        echo json_encode($books);
      }
      else 
      {
        if (array_key_exists($resourceId, $books)) 
        {
          echo json_encode($books[$resourceId]);
        }
      }
    break;
    case 'POST':
      // Tomamos la entrada "cruda".
      $json = file_get_contents('php://input');

      // Transformamos el json recibido a un nuevo elemento del arreglo.
      $books[] = json_decode($json, true);

      // Emitimos hacia la salida la última clave del arreglo de libros.
      // echo array_keys($books)[count($books) - 1];

      // Retornamos la coleccion modificada en formato json.
      echo json_encode($books);
    break;
    case 'PUT':
      // Validamos que el recurso exista.
      if (!empty($resourceId) && array_key_exists($resourceId, $books)) 
      {
        // Tomamos la entrada cruda.
        $json = file_get_contents('php://input');

        // Transformamos el json recibido a un nuevo elemento del arreglo.
        $books[$resourceId] = json_decode($json, true);

        // Retornamos la coleccion modificada en formato json.
        echo json_encode($books);
      }
    break;
    case 'DELETE':
      // Validamos que el recurso exista.
      if (!empty($resourceId) && array_key_exists($resourceId, $books)) 
      {
        // Eliminamos el recurso.
        unset($books[$resourceId]);
      }

      echo json_encode($books);
    break;
  }

?>